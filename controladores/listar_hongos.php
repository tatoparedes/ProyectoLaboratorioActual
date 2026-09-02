<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once "../conexion.php";

$usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;
$usuarioRol = isset($_SESSION["usuario"]["nRol"]) ? intval($_SESSION["usuario"]["nRol"]) : 0;

if (!$usuarioId || $usuarioId <= 0) {
    echo json_encode(["status" => "error", "message" => "Usuario no autenticado"]);
    exit;
}

try {
    // Verificar cuál tabla existe (`microorganismo` o `hongo`)
    $tabla = "microorganismo";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'microorganismo'")->fetch();
    $filtroTipo = "WHERE m.mTipo = 'Hongo' OR m.mTipo = 'hongo'";
    if (!$tableCheck) {
        $tableCheckHongo = $conn->query("SHOW TABLES LIKE 'hongo'")->fetch();
        if ($tableCheckHongo) {
            $tabla = "hongo";
            $filtroTipo = "";
        }
    }

    $sql = "
        SELECT 
            m.mCodigo, m.mTipo, m.mNombreComun, m.mNombreCientifico, m.mMuestra, m.mColoracion, m.mFoto,
            m.fkDominio, m.fkReino, m.fkFilo, m.fkSubfilo, m.fkSuperclase, m.fkClase, m.fkSubclase,
            m.fkOrden, m.fkFamilia, m.fkGenero, m.fkEspecie,
            cdom.cConstDescripcion AS cDominio,
            crei.cConstDescripcion AS cReino,
            cfil.cConstDescripcion AS cFilo,
            csubf.cConstDescripcion AS cSubfilo,
            csupc.cConstDescripcion AS cSuperclase,
            ccla.cConstDescripcion AS cClase,
            csubc.cConstDescripcion AS cSubclase,
            cord.cConstDescripcion AS cOrden,
            cfam.cConstDescripcion AS cFamilia,
            cgen.cConstDescripcion AS cGenero,
            cesp.cConstDescripcion AS cEspecie
        FROM {$tabla} m
        LEFT JOIN constante cdom ON m.fkDominio = cdom.tCodigo
        LEFT JOIN constante crei ON m.fkReino = crei.tCodigo
        LEFT JOIN constante cfil ON m.fkFilo = cfil.tCodigo
        LEFT JOIN constante csubf ON m.fkSubfilo = csubf.tCodigo
        LEFT JOIN constante csupc ON m.fkSuperclase = csupc.tCodigo
        LEFT JOIN constante ccla ON m.fkClase = ccla.tCodigo
        LEFT JOIN constante csubc ON m.fkSubclase = csubc.tCodigo
        LEFT JOIN constante cord ON m.fkOrden = cord.tCodigo
        LEFT JOIN constante cfam ON m.fkFamilia = cfam.tCodigo
        LEFT JOIN constante cgen ON m.fkGenero = cgen.tCodigo
        LEFT JOIN constante cesp ON m.fkEspecie = cesp.tCodigo
        {$filtroTipo}
        ORDER BY m.mCodigo DESC
    ";

    $stmt = $conn->query($sql);
    $hongos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "ok", "data" => $hongos]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al listar hongos: " . $e->getMessage()]);
}
?>
