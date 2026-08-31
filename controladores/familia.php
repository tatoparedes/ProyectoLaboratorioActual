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

// Validar usuario y rol docente o admin
if (!$usuarioId || $usuarioId <= 0 || ($usuarioRol !== 2 && $usuarioRol !== 3)) {
    echo json_encode(["status" => "error", "message" => "Usuario no autorizado o rol no permitido"]);
    exit;
}

try {
    switch ($accion) {
        case 'listar':
            $stmt = $conn->prepare("CALL sp_listar_familias()");
            $stmt->execute();
            $familias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "data" => $familias]);
            break;

        case 'agregar':
            $nombre = limpiar($_POST['cFamilia'] ?? '');
            if ($nombre === '') {
                echo json_encode(["status" => "error", "message" => "El nombre de la familia no puede estar vacío"]);
                exit;
            }
            $stmt = $conn->prepare("CALL sp_crear_familia(?, ?)");
            $stmt->execute([$nombre, $usuarioId]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Familia agregada correctamente"]);
            break;

        case 'editar':
            $id = intval($_POST['nFamilia'] ?? 0);
            $nombre = limpiar($_POST['cFamilia'] ?? '');
            if ($id <= 0 || $nombre === '') {
                echo json_encode(["status" => "error", "message" => "Datos inválidos para editar"]);
                exit;
            }
            $stmt = $conn->prepare("CALL sp_actualizar_familia(?, ?)");
            $stmt->execute([$id, $nombre]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Familia actualizada correctamente"]);
            break;

        case 'eliminar':
            $id = intval($_POST['nFamilia'] ?? 0);
            if ($id <= 0) {
                echo json_encode(["status" => "error", "message" => "ID inválido para eliminar"]);
                exit;
            }
            // Verificar si existen géneros asociados
            $check = $conn->prepare("SELECT COUNT(*) FROM genero WHERE nFamilia = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                echo json_encode(["status" => "error", "message" => "No se puede eliminar la familia porque tiene géneros asociados"]);
                exit;
            }
            $stmt = $conn->prepare("CALL sp_eliminar_familia(?)");
            $stmt->execute([$id]);
            $stmt->closeCursor();
            echo json_encode(["status" => "ok", "message" => "Familia eliminada correctamente"]);
            break;

        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en la operación: " . $e->getMessage()]);
}
?>