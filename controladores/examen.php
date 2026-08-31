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

    // ================= LISTAR EXÁMENES =================
    if ($accion === 'listar') {
        try {
            $stmt = $conn->prepare("
                SELECT e.nExamen, e.cExamen, e.cCodigoExamen, e.fechaRegistro,
                       COUNT(p.nPregunta) AS totalPreguntas
                FROM examen e
                LEFT JOIN pregunta p ON e.nExamen = p.nExamen
                WHERE e.bEstado = 1
                GROUP BY e.nExamen, e.cExamen, e.cCodigoExamen, e.fechaRegistro
                ORDER BY e.nExamen DESC
            ");
            $stmt->execute();
            $examenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            echo json_encode(['status' => 'ok', 'data' => $examenes]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al listar exámenes: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= LISTAR PRUEBAS POR ESPECIE =================
    if ($accion === 'listarPruebas') {
        $nEspecie = intval($_POST['nEspecie'] ?? 0);
        if (!$nEspecie) {
            echo json_encode(['status' => 'error', 'message' => 'Especie inválida.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                SELECT nPrueba, cBacteria, cFoto
                FROM prueba 
                WHERE nEspecie = :nEspecie
                ORDER BY nPrueba DESC
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

    // ================= AGREGAR EXAMEN COMPLETO =================
    if ($accion === 'agregar') {
        $cExamen   = trim($_POST['cExamen'] ?? '');
        $preguntas = isset($_POST['preguntas']) ? json_decode($_POST['preguntas'], true) : [];

        if ($cExamen === '' || empty($preguntas)) {
            echo json_encode(['status' => 'error', 'message' => 'Debe ingresar un título y al menos una pregunta.']);
            exit;
        }

        try {
            // Generar código único de 6 dígitos
            do {
                $codigo = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM examen WHERE cCodigoExamen = ?");
                $stmtCheck->execute([$codigo]);
                $existe = $stmtCheck->fetchColumn();
                $stmtCheck->closeCursor();
            } while ($existe > 0);

            // Insertar examen usando stored procedure
            $stmtExamen = $conn->prepare("CALL sp_crear_examen(?, ?, ?, 1)");
            $stmtExamen->execute([$cExamen, $codigo, $usuarioId]);
            $stmtExamen->closeCursor();

            // Obtener el ID del examen recién insertado
            $stmtId = $conn->prepare("SELECT nExamen FROM examen WHERE cCodigoExamen = ?");
            $stmtId->execute([$codigo]);
            $nExamen = $stmtId->fetchColumn();
            $stmtId->closeCursor();

            // Insertar cada pregunta usando stored procedure
            foreach ($preguntas as $p) {
                $cPregunta = trim($p['descripcion'] ?? '');
                $nPrueba   = !empty($p['nPrueba']) ? intval($p['nPrueba']) : null;

                $stmtPregunta = $conn->prepare("CALL sp_crear_pregunta(?, ?, ?)");
                $stmtPregunta->execute([$cPregunta, $nPrueba, $nExamen]);
                $stmtPregunta->closeCursor();
            }

            echo json_encode(['status' => 'ok', 'message' => 'Examen guardado correctamente.', 'codigo' => $codigo]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar examen: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= EDITAR EXAMEN =================
    if ($accion === 'editar') {
        $nExamen = intval($_POST['nExamen'] ?? 0);
        $cExamen = trim($_POST['cExamen'] ?? '');

        if (!$nExamen || $cExamen === '') {
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos para editar examen.']);
            exit;
        }

        try {
            // Obtener código actual
            $stmtCod = $conn->prepare("SELECT cCodigoExamen FROM examen WHERE nExamen = ?");
            $stmtCod->execute([$nExamen]);
            $cCodigoExamen = $stmtCod->fetchColumn();
            $stmtCod->closeCursor();

            $stmtUpdate = $conn->prepare("CALL sp_actualizar_examen(?, ?, ?, 1)");
            $stmtUpdate->execute([$nExamen, $cExamen, $cCodigoExamen]);
            $stmtUpdate->closeCursor();

            echo json_encode(['status' => 'ok', 'message' => 'Examen actualizado correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar examen: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= ELIMINAR EXAMEN =================
    if ($accion === 'eliminar') {
        $nExamen = intval($_POST['nExamen'] ?? 0);
        if (!$nExamen) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido para eliminar.']);
            exit;
        }

        try {
            // Soft delete desactivando estado
            $stmt = $conn->prepare("UPDATE examen SET bEstado = 0 WHERE nExamen = :nExamen");
            $stmt->bindParam(':nExamen', $nExamen, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();

            echo json_encode(['status' => 'ok', 'message' => 'Examen eliminado correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar examen: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= VER PREGUNTAS DE UN EXAMEN =================
    if ($accion === 'verPreguntas') {
        $nExamen = intval($_POST['nExamen'] ?? 0);
        if (!$nExamen) {
            echo json_encode(['status' => 'error', 'message' => 'ID de examen inválido.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                SELECT p.nPregunta, p.cPregunta, pr.cFoto, pr.cDescripcion AS cDescripcionPrueba
                FROM pregunta p
                LEFT JOIN prueba pr ON p.nPrueba = pr.nPrueba
                WHERE p.nExamen = :nExamen
                ORDER BY p.nPregunta ASC
            ");
            $stmt->bindParam(':nExamen', $nExamen, PDO::PARAM_INT);
            $stmt->execute();
            $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            echo json_encode(['status' => 'ok', 'data' => $preguntas]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al obtener preguntas: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= BUSCAR RESULTADOS POR CÓDIGO (DOCENTE) =================
    if ($accion === 'buscarResultados') {
        $codigoExamen = trim($_POST['codigoExamen'] ?? '');
        if ($codigoExamen === '') {
            echo json_encode(['status' => 'error', 'message' => 'Ingrese un código de examen.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT nExamen, cExamen, cCodigoExamen FROM examen WHERE cCodigoExamen = :codigo AND bEstado = 1");
            $stmt->bindParam(':codigo', $codigoExamen);
            $stmt->execute();
            $examen = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$examen) {
                echo json_encode(['status' => 'error', 'message' => 'Examen no encontrado con este código.']);
                exit;
            }

            $stmtCal = $conn->prepare("
                SELECT c.nCalificacion, c.nUsuario, c.nExamen, c.cCalificacion, c.fechaRegistro,
                       u.cNombres, u.cApePaterno, u.cApeMaterno, u.cDNI
                FROM calificacion c
                INNER JOIN usuario u ON c.nUsuario = u.nUsuario
                WHERE c.nExamen = ?
                ORDER BY c.nCalificacion DESC
            ");
            $stmtCal->execute([$examen['nExamen']]);
            $resultados = $stmtCal->fetchAll(PDO::FETCH_ASSOC);
            $stmtCal->closeCursor();

            echo json_encode([
                'status' => 'ok',
                'examen' => $examen,
                'resultados' => $resultados
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar resultados: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= VER RESPUESTAS DE UN ESTUDIANTE =================
    if ($accion === 'verRespuestas') {
        $nCalificacion = intval($_POST['nCalificacion'] ?? 0);
        if (!$nCalificacion) {
            echo json_encode(['status' => 'error', 'message' => 'ID de calificación inválido.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("
                SELECT r.nRespuesta, r.nPregunta, r.cRespuesta, r.cComentario,
                       p.cPregunta, pr.cFoto, pr.cBacteria
                FROM respuesta r
                INNER JOIN pregunta p ON r.nPregunta = p.nPregunta
                LEFT JOIN prueba pr ON p.nPrueba = pr.nPrueba
                WHERE r.nCalificacion = :nCal
                ORDER BY r.nRespuesta ASC
            ");
            $stmt->bindParam(':nCal', $nCalificacion, PDO::PARAM_INT);
            $stmt->execute();
            $respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Si no hay filas en respuesta, obtener preguntas del examen
            if (empty($respuestas)) {
                $stmtAlt = $conn->prepare("
                    SELECT p.nPregunta, p.cPregunta, pr.cFoto, pr.cBacteria,
                           '' AS cRespuesta, '' AS cComentario, 0 AS nRespuesta
                    FROM pregunta p
                    INNER JOIN calificacion c ON p.nExamen = c.nExamen
                    LEFT JOIN prueba pr ON p.nPrueba = pr.nPrueba
                    WHERE c.nCalificacion = :nCal
                    ORDER BY p.nPregunta ASC
                ");
                $stmtAlt->bindParam(':nCal', $nCalificacion, PDO::PARAM_INT);
                $stmtAlt->execute();
                $respuestas = $stmtAlt->fetchAll(PDO::FETCH_ASSOC);
                $stmtAlt->closeCursor();
            }

            echo json_encode(['status' => 'ok', 'respuestas' => $respuestas]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al obtener respuestas: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= GUARDAR COMENTARIOS DOCENTE =================
    if ($accion === 'guardarComentarios') {
        $comentarios = json_decode($_POST['comentarios'] ?? '[]', true);

        if (!is_array($comentarios)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de comentarios inválido.']);
            exit;
        }

        try {
            if (!empty($comentarios)) {
                foreach ($comentarios as $c) {
                    $nRespuesta = intval($c['nRespuesta'] ?? 0);
                    $comentario = trim($c['comentario'] ?? '');

                    if ($nRespuesta > 0) {
                        $stmt = $conn->prepare("CALL sp_calificar_respuesta(?, ?)");
                        $stmt->execute([$nRespuesta, $comentario]);
                        $stmt->closeCursor();
                    }
                }
            }

            echo json_encode(['status' => 'ok', 'message' => 'Comentarios guardados correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar comentarios: ' . $e->getMessage()]);
        }
        exit;
    }

    // ================= GUARDAR CALIFICACIÓN =================
    if ($accion === 'guardarCalificacion') {
        $nCalificacion = intval($_POST['nCalificacion'] ?? 0);
        $calificacion  = trim($_POST['calificacion'] ?? '');

        if (!$nCalificacion || $calificacion === '' || !is_numeric($calificacion)) {
            echo json_encode(['status' => 'error', 'message' => 'Calificación inválida.']);
            exit;
        }

        $calificacionVal = floatval($calificacion);
        if ($calificacionVal < 0 || $calificacionVal > 20) {
            echo json_encode(['status' => 'error', 'message' => 'La nota debe estar entre 0 y 20.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("CALL sp_actualizar_calificacion(?, ?)");
            $stmt->execute([$nCalificacion, $calificacionVal]);
            $stmt->closeCursor();

            echo json_encode(['status' => 'ok', 'message' => 'Calificación guardada correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar calificación: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>