<?php
session_start();
require_once '../conexion.php';

// Solo admins
if (!isset($_SESSION["usuario"]["nRol"]) || $_SESSION["usuario"]["nRol"] != 3) {
    echo json_encode(["status" => "error", "message" => "Acceso no autorizado"]);
    exit();
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        try {
            $stmt = $conn->query("SELECT nUsuario, cDNI, cApePaterno, cApeMaterno, cNombres, cCorreo, nRol FROM usuario ORDER BY nUsuario ASC");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["status" => "ok", "data" => $usuarios]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    case 'actualizar':
        $nUsuario = intval($_POST['nUsuario'] ?? 0);
        $campo = $_POST['campo'] ?? '';
        $valor = $_POST['valor'] ?? '';

        if (!$nUsuario || !$campo) {
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
            exit();
        }

        $allowed = ['cDNI', 'cApePaterno', 'cApeMaterno', 'cNombres', 'cCorreo', 'nRol'];
        if (!in_array($campo, $allowed)) {
            echo json_encode(["status" => "error", "message" => "Campo no permitido"]);
            exit();
        }

        try {
            $stmt = $conn->prepare("UPDATE usuario SET $campo = :valor WHERE nUsuario = :id");
            $stmt->execute(['valor' => $valor, 'id' => $nUsuario]);
            echo json_encode(["status" => "ok", "message" => "Usuario actualizado"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    case 'eliminar':
        $nUsuario = intval($_POST['nUsuario'] ?? 0);
        if (!$nUsuario) {
            echo json_encode(["status" => "error", "message" => "ID de usuario inválido"]);
            exit();
        }

        try {
            $stmt = $conn->prepare("DELETE FROM usuario WHERE nUsuario = ?");
            $stmt->execute([$nUsuario]);
            echo json_encode(["status" => "ok", "message" => "Usuario eliminado"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
        break;
}
?>