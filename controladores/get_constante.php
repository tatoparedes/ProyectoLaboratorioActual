<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once "../conexion.php";

$usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;
$usuarioRol = isset($_SESSION["usuario"]["nRol"]) ? intval($_SESSION["usuario"]["nRol"]) : 0;

if (!$usuarioId || $usuarioId <= 0 || ($usuarioRol !== 2 && $usuarioRol !== 3)) {
    echo json_encode(["status" => "error", "message" => "Usuario no autorizado o rol no permitido"]);
    exit;
}

$grupo = isset($_GET['grupo']) ? intval($_GET['grupo']) : 0;

if ($grupo <= 0) {
    echo json_encode(["status" => "error", "message" => "Grupo inválido"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT tCodigo, cConstValor, cConstDescripcion FROM constante WHERE nConstGrupo = ? ORDER BY cConstDescripcion ASC");
    $stmt->execute([$grupo]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);   
    echo json_encode(["status" => "ok", "data" => $result]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en la operación: " . $e->getMessage()]);
}
?>
