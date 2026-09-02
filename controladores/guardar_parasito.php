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

$mCodigo = isset($_POST['mCodigo']) ? intval($_POST['mCodigo']) : 0;

// Campos libres
$mNombreComun = trim($_POST['mNombreComun'] ?? '');
$mNombreCientifico = trim($_POST['mNombreCientifico'] ?? '');

// Procesar foto
$mFoto = null;
if (isset($_FILES['mFoto']) && $_FILES['mFoto']['error'] === UPLOAD_ERR_OK) {
    $directorio = __DIR__ . "/../uploads/";
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $extension = strtolower(pathinfo($_FILES['mFoto']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($extension, $allowed)) {
        $mFoto = uniqid("parasito_", true) . "." . $extension;
        $rutaDestino = $directorio . $mFoto;
        if (!move_uploaded_file($_FILES['mFoto']['tmp_name'], $rutaDestino)) {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la imagen subida.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no permitido (solo JPG, PNG, GIF, WEBP).']);
        exit;
    }
}

// Función auxiliar para resolver o crear valores taxonómicos en `constante`
function resolverTaxonomia($conn, $valorSelect, $valorNuevoText, $grupoId) {
    if ($valorSelect === '__NEW__') {
        $nuevoValor = trim($valorNuevoText);
        if ($nuevoValor === '') {
            return null;
        }
        // Buscar si ya existe para no duplicar
        $stmtSearch = $conn->prepare("SELECT tCodigo FROM constante WHERE nConstGrupo = ? AND LOWER(cConstDescripcion) = LOWER(?) LIMIT 1");
        $stmtSearch->execute([$grupoId, $nuevoValor]);
        $existente = $stmtSearch->fetch(PDO::FETCH_ASSOC);
        if ($existente) {
            return intval($existente['tCodigo']);
        }

        // Insertar nuevo valor
        $stmt = $conn->prepare("INSERT INTO constante (nConstGrupo, nConstValor, cConstValor, cConstDescripcion) VALUES (?, 0, ?, ?)");
        $stmt->execute([$grupoId, $nuevoValor, $nuevoValor]);
        return intval($conn->lastInsertId());
    }

    return (!empty($valorSelect) && is_numeric($valorSelect)) ? intval($valorSelect) : null;
}

// Función auxiliar para obtener la descripción textual de constante
function obtenerTextoConstante($conn, $tCodigo) {
    if (!$tCodigo) return null;
    $stmt = $conn->prepare("SELECT cConstDescripcion FROM constante WHERE tCodigo = ? LIMIT 1");
    $stmt->execute([$tCodigo]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res ? $res['cConstDescripcion'] : null;
}

try {
    $conn->beginTransaction();

    // Resolver cada nivel taxonómico
    $fkDominio    = resolverTaxonomia($conn, $_POST['fkDominio'] ?? '', $_POST['new_fkDominio'] ?? '', 2);
    $fkReino      = resolverTaxonomia($conn, $_POST['fkReino'] ?? '', $_POST['new_fkReino'] ?? '', 3);
    $fkFilo       = resolverTaxonomia($conn, $_POST['fkFilo'] ?? '', $_POST['new_fkFilo'] ?? '', 4);
    $fkSubfilo    = resolverTaxonomia($conn, $_POST['fkSubfilo'] ?? '', $_POST['new_fkSubfilo'] ?? '', 5);
    $fkSuperclase = resolverTaxonomia($conn, $_POST['fkSuperclase'] ?? '', $_POST['new_fkSuperclase'] ?? '', 6);
    $fkClase      = resolverTaxonomia($conn, $_POST['fkClase'] ?? '', $_POST['new_fkClase'] ?? '', 7);
    $fkSubclase   = resolverTaxonomia($conn, $_POST['fkSubclase'] ?? '', $_POST['new_fkSubclase'] ?? '', 8);
    $fkOrden      = resolverTaxonomia($conn, $_POST['fkOrden'] ?? '', $_POST['new_fkOrden'] ?? '', 9);
    $fkFamilia    = resolverTaxonomia($conn, $_POST['fkFamilia'] ?? '', $_POST['new_fkFamilia'] ?? '', 10);
    $fkGenero     = resolverTaxonomia($conn, $_POST['fkGenero'] ?? '', $_POST['new_fkGenero'] ?? '', 11);
    $fkEspecie    = resolverTaxonomia($conn, $_POST['fkEspecie'] ?? '', $_POST['new_fkEspecie'] ?? '', 12);

    $muestraId    = resolverTaxonomia($conn, $_POST['mMuestra'] ?? '', $_POST['new_mMuestra'] ?? '', 1);
    $mMuestra     = obtenerTextoConstante($conn, $muestraId);
    if (!$mMuestra && !empty($_POST['mMuestra']) && $_POST['mMuestra'] !== '__NEW__') {
        $mMuestra = $_POST['mMuestra'];
    }

    $coloracionId = resolverTaxonomia($conn, $_POST['mColoracion'] ?? '', $_POST['new_mColoracion'] ?? '', 15);
    $mColoracion  = obtenerTextoConstante($conn, $coloracionId);
    if (!$mColoracion && !empty($_POST['mColoracion']) && $_POST['mColoracion'] !== '__NEW__') {
        $mColoracion = $_POST['mColoracion'];
    }

    // Determinar la tabla destino (`parasito` o `microorganismo`)
    $tablaDestino = "parasito";
    $tableCheck = $conn->query("SHOW TABLES LIKE 'parasito'")->fetch();
    if (!$tableCheck) {
        $tableCheckMicro = $conn->query("SHOW TABLES LIKE 'microorganismo'")->fetch();
        if ($tableCheckMicro) {
            $tablaDestino = "microorganismo";
        }
    }

    if ($mCodigo > 0) {
        // Actualizar registro existente
        $sqlFoto = $mFoto ? ", mFoto = ?" : "";
        $sql = "UPDATE {$tablaDestino} SET 
                    mNombreComun = ?, mNombreCientifico = ?, mMuestra = ?, mColoracion = ?,
                    fkDominio = ?, fkReino = ?, fkFilo = ?, fkSubfilo = ?, fkSuperclase = ?,
                    fkClase = ?, fkSubclase = ?, fkOrden = ?, fkFamilia = ?, fkGenero = ?, fkEspecie = ?
                    {$sqlFoto}
                WHERE mCodigo = ?";
        
        $params = [
            $mNombreComun !== '' ? $mNombreComun : null,
            $mNombreCientifico !== '' ? $mNombreCientifico : null,
            $mMuestra !== '' ? $mMuestra : null,
            $mColoracion !== '' ? $mColoracion : null,
            $fkDominio, $fkReino, $fkFilo, $fkSubfilo, $fkSuperclase, $fkClase, $fkSubclase,
            $fkOrden, $fkFamilia, $fkGenero, $fkEspecie
        ];
        if ($mFoto) {
            $params[] = $mFoto;
        }
        $params[] = $mCodigo;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $mensaje = "Parásito actualizado correctamente.";
    } else {
        // Insertar nuevo parásito
        $sql = "INSERT INTO {$tablaDestino} (
                    mTipo, mNombreComun, mNombreCientifico, mMuestra, mColoracion, mFoto,
                    fkDominio, fkReino, fkFilo, fkSubfilo, fkSuperclase, fkClase, fkSubclase,
                    fkOrden, fkFamilia, fkGenero, fkEspecie
                ) VALUES (
                    'Parasito', ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?
                )";

        $stmtMicro = $conn->prepare($sql);
        $stmtMicro->execute([
            $mNombreComun !== '' ? $mNombreComun : null,
            $mNombreCientifico !== '' ? $mNombreCientifico : null,
            $mMuestra !== '' ? $mMuestra : null,
            $mColoracion !== '' ? $mColoracion : null,
            $mFoto,
            $fkDominio, $fkReino, $fkFilo, $fkSubfilo, $fkSuperclase, $fkClase, $fkSubclase,
            $fkOrden, $fkFamilia, $fkGenero, $fkEspecie
        ]);
        $mensaje = "Parásito guardado correctamente.";
    }

    $conn->commit();
    echo json_encode(["status" => "ok", "message" => $mensaje]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["status" => "error", "message" => "Error al guardar el parásito: " . $e->getMessage()]);
}
?>
