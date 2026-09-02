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
    $tabla = "microorganismo";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'microorganismo'")->fetch();
    if (!$tableCheck) {
        $tableCheckHongo = $conn->query("SHOW TABLES LIKE 'hongo'")->fetch();
        if ($tableCheckHongo) {
            $tabla = "hongo";
        }
    }

    $stmt = $conn->prepare("DELETE FROM {$tabla} WHERE mCodigo = ?");
    $stmt->execute([$id]);

    echo json_encode(["status" => "ok", "message" => "Hongo eliminado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al eliminar: " . $e->getMessage()]);
}
?>
