<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once "../conexion.php";

$usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;
$usuarioRol = isset($_SESSION["usuario"]["nRol"]) ? intval($_SESSION["usuario"]["nRol"]) : 0;

function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$accion = $_POST['accion'] ?? '';

if (!$usuarioId || $usuarioId <= 0 || ($usuarioRol !== 2 && $usuarioRol !== 3)) {
    echo json_encode(["status" => "error", "message" => "Usuario no autorizado o rol no permitido"]);
    exit;
}

try {
    switch ($accion) {
        case 'listar':
            $stmt = $conn->prepare("CALL sp_listar_especies()");
            $stmt->execute();
            $especies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "data" => $especies]);
            break;

        case 'listarGenerosPorFamilia':
            $nFamilia = intval($_POST['nFamilia'] ?? 0);
            if ($nFamilia <= 0) {
                echo json_encode(["status" => "error", "message" => "ID de familia inválido"]);
                exit;
            }
            $stmt = $conn->prepare("SELECT nGenero, cGenero FROM genero WHERE nFamilia = ? ORDER BY cGenero ASC");
            $stmt->execute([$nFamilia]);
            $generos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "data" => $generos]);
            break;

        case 'listarPorGenero':
            $nGenero = intval($_POST['nGenero'] ?? 0);
            if ($nGenero <= 0) {
                echo json_encode(["status" => "error", "message" => "ID de género inválido"]);
                exit;
            }
            $stmt = $conn->prepare("SELECT nEspecie, cEspecie FROM especie WHERE nGenero = ? ORDER BY cEspecie ASC");
            $stmt->execute([$nGenero]);
            $especies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "data" => $especies]);
            break;

        case 'agregar':
            $nombre = limpiar($_POST['cEspecie'] ?? '');
            $generoId = intval($_POST['nGenero'] ?? 0);

            if ($nombre === '' || $generoId <= 0) {
                echo json_encode(["status" => "error", "message" => "Datos incompletos para agregar la especie"]);
                exit;
            }

            $stmt = $conn->prepare("CALL sp_crear_especie(?, ?, ?)");
            $stmt->execute([$nombre, $generoId, $usuarioId]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Especie agregada correctamente"]);
            break;

        case 'editar':
            $id = intval($_POST['nEspecie'] ?? 0);
            $nombre = limpiar($_POST['cEspecie'] ?? '');
            $generoId = intval($_POST['nGenero'] ?? 0);

            if ($id <= 0 || $nombre === '' || $generoId <= 0) {
                echo json_encode(["status" => "error", "message" => "Datos inválidos para editar especie"]);
                exit;
            }

            $stmt = $conn->prepare("CALL sp_actualizar_especie(?, ?, ?)");
            $stmt->execute([$id, $nombre, $generoId]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Especie actualizada correctamente"]);
            break;

        case 'eliminar':
            $id = intval($_POST['nEspecie'] ?? 0);
            if ($id <= 0) {
                echo json_encode(["status" => "error", "message" => "ID inválido para eliminar"]);
                exit;
            }

            $check = $conn->prepare("SELECT COUNT(*) FROM prueba WHERE nEspecie = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                echo json_encode(["status" => "error", "message" => "No se puede eliminar la especie porque tiene muestras / pruebas asociadas"]);
                exit;
            }

            $stmt = $conn->prepare("CALL sp_eliminar_especie(?)");
            $stmt->execute([$id]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Especie eliminada correctamente"]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en la operación: " . $e->getMessage()]);
}
?>