<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once "../conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no válido.']);
    exit;
}

$accion = $_POST['accion'] ?? '';

try {
    $usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;
    if (!$usuarioId) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
        exit;
    }

    if ($accion === 'verResultados') {
        $codigo = trim($_POST['codigoExamen'] ?? '');
        if ($codigo === '') {
            echo json_encode(['status' => 'error', 'message' => 'Debe ingresar un código de examen.']);
            exit;
        }

        // Obtener examen por código
        $stmtExamen = $conn->prepare("SELECT nExamen, cExamen FROM examen WHERE cCodigoExamen = :codigo AND bEstado = 1 LIMIT 1");
        $stmtExamen->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmtExamen->execute();
        $examen = $stmtExamen->fetch(PDO::FETCH_ASSOC);
        $stmtExamen->closeCursor();

        if (!$examen) {
            echo json_encode(['status' => 'error', 'message' => 'Examen no encontrado.']);
            exit;
        }

        $nExamen = $examen['nExamen'];

        // Obtener la calificación del alumno
        $stmtCal = $conn->prepare("
            SELECT nCalificacion, cCalificacion 
            FROM calificacion 
            WHERE nUsuario = :usuarioId AND nExamen = :nExamen
            ORDER BY nCalificacion DESC LIMIT 1
        ");
        $stmtCal->bindParam(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmtCal->bindParam(':nExamen', $nExamen, PDO::PARAM_INT);
        $stmtCal->execute();
        $calificacion = $stmtCal->fetch(PDO::FETCH_ASSOC);
        $stmtCal->closeCursor();

        if (!$calificacion) {
            echo json_encode(['status' => 'error', 'message' => 'No hay calificación registrada para este examen.']);
            exit;
        }

        // Usar SP para listar respuestas
        $stmtResp = $conn->prepare("CALL sp_listar_respuestas_calificacion(?)");
        $stmtResp->execute([$calificacion['nCalificacion']]);
        $respuestas = $stmtResp->fetchAll(PDO::FETCH_ASSOC);
        $stmtResp->closeCursor();

        // Obtener foto si existe para cada pregunta
        foreach ($respuestas as &$r) {
            $stmtFoto = $conn->prepare("
                SELECT pr.cFoto 
                FROM pregunta p 
                LEFT JOIN prueba pr ON p.nPrueba = pr.nPrueba 
                WHERE p.nPregunta = ?
            ");
            $stmtFoto->execute([$r['nPregunta']]);
            $r['cFoto'] = $stmtFoto->fetchColumn();
            $stmtFoto->closeCursor();
        }

        echo json_encode([
            'status' => 'ok',
            'examen' => $examen['cExamen'],
            'nota' => $calificacion['cCalificacion'] ?? 'Pendiente',
            'respuestas' => $respuestas
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
    exit;
}
?>