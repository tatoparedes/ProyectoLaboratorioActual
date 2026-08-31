<?php
session_start();
header('Content-Type: application/json');
require_once "../conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no válido.']);
    exit;
}

$accion = $_POST['accion'] ?? '';
$usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;

try {
    // ================= VALIDAR CÓDIGO DEL EXAMEN =================
    if ($accion === 'verificarCodigo') {
        $codigo = trim($_POST['codigoExamen'] ?? '');
        if ($codigo === '') {
            echo json_encode(['status' => 'error', 'message' => 'Debe ingresar un código de examen.']);
            exit;
        }

        if (!$usuarioId) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión expirada. Por favor inicie sesión nuevamente.']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT e.nExamen, e.cExamen, e.cCodigoExamen, COUNT(p.nPregunta) AS totalPreguntas
            FROM examen e
            LEFT JOIN pregunta p ON e.nExamen = p.nExamen
            WHERE e.cCodigoExamen = :codigo AND e.bEstado = 1
            GROUP BY e.nExamen, e.cExamen, e.cCodigoExamen
        ");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $examen = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$examen) {
            echo json_encode(['status' => 'error', 'message' => 'Código inválido o examen no disponible.']);
            exit;
        }

        // Revisar si ya rindió el examen
        $stmtCheck = $conn->prepare("
            SELECT COUNT(*) FROM calificacion 
            WHERE nExamen = :nExamen AND nUsuario = :nUsuario
        ");
        $stmtCheck->bindParam(':nExamen', $examen['nExamen'], PDO::PARAM_INT);
        $stmtCheck->bindParam(':nUsuario', $usuarioId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $yaRindio = $stmtCheck->fetchColumn();
        $stmtCheck->closeCursor();

        if ($yaRindio > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Ya has completado esta evaluación anteriormente. Consulta tus notas en la sección "Resultado Examen".']);
            exit;
        }

        echo json_encode(['status' => 'ok', 'data' => $examen]);
        exit;
    }

    // ================= OBTENER PREGUNTAS =================
    if ($accion === 'obtenerPreguntas') {
        $nExamen = intval($_POST['nExamen'] ?? 0);
        if (!$nExamen) {
            echo json_encode(['status' => 'error', 'message' => 'ID de examen inválido.']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT p.nPregunta, p.cPregunta, pr.cFoto, pr.cDescripcion AS cDescripcionPrueba
            FROM pregunta p
            LEFT JOIN prueba pr ON p.nPrueba = pr.nPrueba
            WHERE p.nExamen = :nExamen
            ORDER BY p.nPregunta ASC
        ");
        $stmt->bindParam(':nExamen', $nExamen, PDO::PARAM_INT);
        $stmt->execute();
        $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $stmt->closeCursor();

        echo json_encode(['status' => 'ok', 'data' => $preguntas]);
        exit;
    }

    // ================= GUARDAR RESPUESTAS =================
    if ($accion === 'guardarRespuestas') {
        $nExamen = intval($_POST['nExamen'] ?? 0);
        $respuestas = json_decode($_POST['respuestas'] ?? '[]', true);

        if (!$nExamen || !$usuarioId || empty($respuestas)) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para guardar examen.']);
            exit;
        }

        // Insertar calificación pendiente con SP
        $stmtCal = $conn->prepare("CALL sp_registrar_calificacion(NULL, ?, ?)");
        $stmtCal->execute([$nExamen, $usuarioId]);
        $stmtCal->closeCursor();

        // Obtener el ID de la calificación recién creada
        $stmtCalId = $conn->prepare("
            SELECT nCalificacion FROM calificacion 
            WHERE nExamen = ? AND nUsuario = ? 
            ORDER BY nCalificacion DESC LIMIT 1
        ");
        $stmtCalId->execute([$nExamen, $usuarioId]);
        $nCalificacion = $stmtCalId->fetchColumn();
        $stmtCalId->closeCursor();

        if (!$nCalificacion) {
            echo json_encode(['status' => 'error', 'message' => 'Error al registrar la evaluación.']);
            exit;
        }

        // Insertar cada respuesta con SP
        foreach ($respuestas as $r) {
            $nPregunta = intval($r['nPregunta'] ?? 0);
            $cRespuesta = trim($r['cRespuesta'] ?? '');

            if ($nPregunta > 0) {
                $stmtRes = $conn->prepare("CALL sp_guardar_respuesta(?, ?, ?, NULL)");
                $stmtRes->execute([$nPregunta, $cRespuesta, $nCalificacion]);
                $stmtRes->closeCursor();
            }
        }

        echo json_encode(['status' => 'ok', 'message' => 'Respuestas enviadas correctamente.']);
        exit;
    }

    // ================= CONSULTAR RESULTADOS DEL ESTUDIANTE =================
    if ($accion === 'verResultadosEstudiante') {
        $codigo = trim($_POST['codigoExamen'] ?? '');

        if ($codigo === '') {
            echo json_encode(['status' => 'error', 'message' => 'Ingrese el código de examen.']);
            exit;
        }

        if (!$usuarioId) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión expirada.']);
            exit;
        }

        // Buscar examen por código
        $stmtExamen = $conn->prepare("SELECT nExamen, cExamen FROM examen WHERE cCodigoExamen = :codigo AND bEstado = 1");
        $stmtExamen->bindParam(':codigo', $codigo);
        $stmtExamen->execute();
        $examen = $stmtExamen->fetch(PDO::FETCH_ASSOC);
        $stmtExamen->closeCursor();

        if (!$examen) {
            echo json_encode(['status' => 'error', 'message' => 'Examen no encontrado.']);
            exit;
        }

        // Obtener la calificación del usuario
        $stmtCal = $conn->prepare("
            SELECT nCalificacion, cCalificacion 
            FROM calificacion 
            WHERE nExamen = :nExamen AND nUsuario = :nUsuario 
            ORDER BY nCalificacion DESC LIMIT 1
        ");
        $stmtCal->bindParam(':nExamen', $examen['nExamen'], PDO::PARAM_INT);
        $stmtCal->bindParam(':nUsuario', $usuarioId, PDO::PARAM_INT);
        $stmtCal->execute();
        $cal = $stmtCal->fetch(PDO::FETCH_ASSOC);
        $stmtCal->closeCursor();

        if (!$cal) {
            echo json_encode(['status' => 'error', 'message' => 'Aún no has rendido este examen.']);
            exit;
        }

        // Obtener respuestas y comentarios con SP
        $stmtResp = $conn->prepare("
            SELECT r.nRespuesta, r.nPregunta, r.cRespuesta, r.cComentario,
                   p.cPregunta, pr.cFoto
            FROM respuesta r
            INNER JOIN pregunta p ON r.nPregunta = p.nPregunta
            LEFT JOIN prueba pr ON p.nPrueba = pr.nPrueba
            WHERE r.nCalificacion = :nCal
            ORDER BY r.nRespuesta ASC
        ");
        $stmtResp->bindParam(':nCal', $cal['nCalificacion'], PDO::PARAM_INT);
        $stmtResp->execute();
        $respuestas = $stmtResp->fetchAll(PDO::FETCH_ASSOC);
        $stmtResp->closeCursor();

        echo json_encode([
            'status' => 'ok',
            'examen' => $examen['cExamen'],
            'nota' => $cal['cCalificacion'],
            'respuestas' => $respuestas ?: []
        ]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>