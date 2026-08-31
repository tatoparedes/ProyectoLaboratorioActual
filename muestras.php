<?php
session_start();

$usuarioNombre = ""; // Valor por defecto

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

$dni = $_SESSION['usuario']['cDNI'];

try {
    $stmt = $conn->prepare("CALL sp_verificar_rol(?)");
    $stmt->execute([$dni]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $usuarioNombre = htmlspecialchars($usuario['cNombres']);
        $rol = $usuario['nRol'];

        if ($rol == 1) {
            include 'vista_alumno.php';
        } elseif ($rol == 2) {
            include 'vista_docente.php';
        } elseif ($rol == 3) {
            include 'vista_admin.php';
        } else {
            echo "Rol no reconocido.";
        }
    } else {
        session_destroy();
        header("Location: login.php");
        exit();
    }

    $stmt->closeCursor();
} catch (PDOException $e) {
    echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body><script>Swal.fire({icon:'error', title:'Error de Base de Datos', text:'" . addslashes($e->getMessage()) . "'}).then(()=>{ window.location.href='login.php'; });</script></body></html>";
}
?>