<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once "../conexion.php";

$usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;
$usuarioRol = isset($_SESSION["usuario"]["nRol"]) ? intval($_SESSION["usuario"]["nRol"]) : 0;

if (!$usuarioId || $usuarioId <= 0 || ($usuarioRol !== 2 && $usuarioRol !== 3)) {
    echo json_encode(["status" => "error", "message" => "Usuario no autorizado"]);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "ID inválido"]);
    exit;
}

try {
    $tabla = "parasito";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'parasito'")->fetch();
    if (!$tableCheck) {
        $tableCheckMicro = $conn->query("SHOW TABLES LIKE 'microorganismo'")->fetch();
        if ($tableCheckMicro) {
            $tabla = "microorganismo";
        }
    }

    $stmt = $conn->prepare("DELETE FROM {$tabla} WHERE mCodigo = ?");
    $stmt->execute([$id]);

    echo json_encode(["status" => "ok", "message" => "Parásito eliminado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al eliminar: " . $e->getMessage()]);
}
?>
