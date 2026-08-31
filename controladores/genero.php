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
            $stmt = $conn->prepare("CALL sp_listar_generos()");
            $stmt->execute();
            $generos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "data" => $generos]);
            break;

        case 'listarPorFamilia':
            $nFamilia = intval($_POST['nFamilia'] ?? 0);
            if ($nFamilia <= 0) {
                echo json_encode(["status" => "error", "message" => "ID de familia inválido"]);
                exit;
            }
            $stmt = $conn->prepare("SELECT nGenero, cGenero, nFamilia FROM genero WHERE nFamilia = ? ORDER BY cGenero ASC");
            $stmt->execute([$nFamilia]);
            $generos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "data" => $generos]);
            break;

        case 'agregar':
            $nombre = limpiar($_POST['cGenero'] ?? '');
            $familiaId = intval($_POST['nFamilia'] ?? 0);

            if ($nombre === '' || $familiaId <= 0) {
                echo json_encode(["status" => "error", "message" => "Datos incompletos para agregar el género"]);
                exit;
            }

            $stmt = $conn->prepare("CALL sp_crear_genero(?, ?, ?)");
            $stmt->execute([$nombre, $familiaId, $usuarioId]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Género agregado correctamente"]);
            break;

        case 'editar':
            $id = intval($_POST['nGenero'] ?? 0);
            $nombre = limpiar($_POST['cGenero'] ?? '');
            $familiaId = intval($_POST['nFamilia'] ?? 0);

            if ($id <= 0 || $nombre === '' || $familiaId <= 0) {
                echo json_encode(["status" => "error", "message" => "Datos inválidos para editar género"]);
                exit;
            }

            $stmt = $conn->prepare("CALL sp_actualizar_genero(?, ?, ?)");
            $stmt->execute([$id, $nombre, $familiaId]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Género actualizado correctamente"]);
            break;

        case 'eliminar':
            $id = intval($_POST['nGenero'] ?? 0);
            if ($id <= 0) {
                echo json_encode(["status" => "error", "message" => "ID inválido para eliminar"]);
                exit;
            }

            $check = $conn->prepare("SELECT COUNT(*) FROM especie WHERE nGenero = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                echo json_encode(["status" => "error", "message" => "No se puede eliminar el género porque tiene especies asociadas"]);
                exit;
            }

            $stmt = $conn->prepare("CALL sp_eliminar_genero(?)");
            $stmt->execute([$id]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Género eliminado correctamente"]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en la operación: " . $e->getMessage()]);
}
?>