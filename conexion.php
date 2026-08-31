<?php

$host = "localhost";
$db = "laboratorio_db";
$user = "root";
$pass = "";


try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}

/*
$host = "localhost"; 
$db = "iestptrujilloedu_laboratorio_db";
$user = "iestptrujilloedu_usuario_laboratorio";
$pass = "usuari0_l4b0r4t0ri0";


try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>
*/