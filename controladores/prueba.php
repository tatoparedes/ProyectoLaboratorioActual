<?php
session_start();
header('Content-Type: application/json');
require_once "../conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    $usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;
    $usuarioRol = isset($_SESSION["usuario"]["nRol"]) ? intval($_SESSION["usuario"]["nRol"]) : 0;

    if (!$usuarioId || $usuarioId <= 0 || ($usuarioRol !== 2 && $usuarioRol !== 3)) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autorizado o rol incorrecto.']);
        exit;
    }

    if ($accion === 'listar') {
        try {
            $stmt = $conn->prepare("
                SELECT p.nPrueba, p.cFoto, p.cDescripcion, p.cResultado, p.cBacteria, p.nEspecie,
                       COALESCE(e.cEspecie, 'Sin especie') AS cEspecie, 
                       COALESCE(g.nGenero, 0) AS nGenero,
                       COALESCE(g.cGenero, 'Sin género') AS cGenero, 
                       COALESCE(f.nFamilia, 0) AS nFamilia,
                       COALESCE(f.cFamilia, 'Sin familia') AS cFamilia,
                       COALESCE(u.cNombres, '') AS cNombres, 
                       COALESCE(u.cApePaterno, '') AS cApePaterno
                FROM prueba p
                LEFT JOIN especie e ON p.nEspecie = e.nEspecie
                LEFT JOIN genero g ON e.nGenero = g.nGenero
                LEFT JOIN familia f ON g.nFamilia = f.nFamilia
                LEFT JOIN usuario u ON p.nUsuario = u.nUsuario
                ORDER BY p.nPrueba DESC
            ");
            $stmt->execute();
            $pruebas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            echo json_encode(['status' => 'ok', 'data' => $pruebas]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al listar pruebas: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($accion === 'listarPorEspecie') {
        $nEspecie = intval($_POST['nEspecie'] ?? 0);
        if (!$nEspecie) {
            echo json_encode(['status' => 'error', 'message' => 'Especie inválida.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                SELECT nPrueba, cBacteria, cFoto, cDescripcion, cResultado 
                FROM prueba 
                WHERE nEspecie = :nEspecie 
                ORDER BY cBacteria ASC
            ");
            $stmt->bindParam(':nEspecie', $nEspecie, PDO::PARAM_INT);
            $stmt->execute();
            $pruebas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            echo json_encode(['status' => 'ok', 'data' => $pruebas]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al listar pruebas: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($accion === 'agregar') {
        $nEspecie     = intval($_POST['nEspecie'] ?? 0);
        $cDescripcion = trim($_POST['cDescripcion'] ?? '');
        $cResultado   = trim($_POST['cResultado'] ?? '');
        $cBacteria    = trim($_POST['cBacteria'] ?? '');

        if (!$nEspecie || $cDescripcion === '' || $cResultado === '' || $cBacteria === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']);
            exit;
        }

        $cFoto = null;
        if (isset($_FILES['cFoto']) && $_FILES['cFoto']['error'] === UPLOAD_ERR_OK) {
            $directorio = __DIR__ . "/../uploads/";
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            $extension = strtolower(pathinfo($_FILES['cFoto']['name'], PATHINFO_EXTENSION));
            $cFoto = uniqid("prueba_", true) . "." . $extension;
            $rutaDestino = $directorio . $cFoto;

            if (!move_uploaded_file($_FILES['cFoto']['tmp_name'], $rutaDestino)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen.']);
                exit;
            }
        }

        try {
            $stmt = $conn->prepare("CALL sp_insertarPrueba(?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nEspecie, $cFoto, $cDescripcion, $cResultado, $cBacteria, $usuarioId]);
            $stmt->closeCursor();

            echo json_encode(['status' => 'ok', 'message' => 'Prueba creada correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al insertar prueba: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($accion === 'editar') {
        $nPrueba      = intval($_POST['nPrueba'] ?? 0);
        $nEspecie     = intval($_POST['nEspecie'] ?? 0);
        $cDescripcion = trim($_POST['cDescripcion'] ?? '');
        $cResultado   = trim($_POST['cResultado'] ?? '');
        $cBacteria    = trim($_POST['cBacteria'] ?? '');

        if (!$nPrueba || !$nEspecie || $cDescripcion === '' || $cResultado === '' || $cBacteria === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos para editar.']);
            exit;
        }

        try {
            // Obtener foto actual
            $stmt = $conn->prepare("SELECT cFoto FROM prueba WHERE nPrueba = :nPrueba");
            $stmt->bindParam(':nPrueba', $nPrueba, PDO::PARAM_INT);
            $stmt->execute();
            $cFotoActual = $stmt->fetchColumn();
            $stmt->closeCursor();

            $cFoto = $cFotoActual;

            if (isset($_FILES['cFoto']) && $_FILES['cFoto']['error'] === UPLOAD_ERR_OK) {
                $directorio = __DIR__ . "/../uploads/";
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $extension = strtolower(pathinfo($_FILES['cFoto']['name'], PATHINFO_EXTENSION));
                $cFoto = uniqid("prueba_", true) . "." . $extension;
                $rutaDestino = $directorio . $cFoto;

                if (move_uploaded_file($_FILES['cFoto']['tmp_name'], $rutaDestino)) {
                    if (!empty($cFotoActual) && file_exists($directorio . $cFotoActual)) {
                        @unlink($directorio . $cFotoActual);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al subir la nueva imagen.']);
                    exit;
                }
            }

            $stmtUpdate = $conn->prepare("CALL sp_actualizarPrueba(?, ?, ?, ?, ?, ?, ?)");
            $stmtUpdate->execute([$nPrueba, $nEspecie, $cFoto, $cDescripcion, $cResultado, $cBacteria, $usuarioId]);
            $stmtUpdate->closeCursor();

            echo json_encode(['status' => 'ok', 'message' => 'Prueba actualizada correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar prueba: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($accion === 'eliminar') {
        $nPrueba = intval($_POST['nPrueba'] ?? 0);
        if (!$nPrueba) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido para eliminar.']);
            exit;
        }

        try {
            // Verificar si hay preguntas asociadas
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM pregunta WHERE nPrueba = :nPrueba");
            $stmtCheck->bindParam(':nPrueba', $nPrueba, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ($stmtCheck->fetchColumn() > 0) {
                $stmtCheck->closeCursor();
                echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar la prueba porque está asignada a uno o más exámenes.']);
                exit;
            }
            $stmtCheck->closeCursor();

            // Buscar foto para borrar
            $stmt = $conn->prepare("SELECT cFoto FROM prueba WHERE nPrueba = :nPrueba");
            $stmt->bindParam(':nPrueba', $nPrueba, PDO::PARAM_INT);
            $stmt->execute();
            $cFoto = $stmt->fetchColumn();
            $stmt->closeCursor();

            if (!empty($cFoto)) {
                $rutaFoto = __DIR__ . "/../uploads/" . $cFoto;
                if (file_exists($rutaFoto)) {
                    @unlink($rutaFoto);
                }
            }

            $stmtDel = $conn->prepare("CALL sp_eliminarPrueba(?)");
            $stmtDel->execute([$nPrueba]);
            $stmtDel->closeCursor();

            echo json_encode(['status' => 'ok', 'message' => 'Prueba eliminada correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar prueba: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>