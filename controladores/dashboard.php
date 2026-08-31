<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../conexion.php';

$usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : 0;
$usuarioRol = isset($_SESSION["usuario"]["nRol"]) ? intval($_SESSION["usuario"]["nRol"]) : 0;

if (!$usuarioId || $usuarioId <= 0) {
    echo json_encode(["status" => "error", "message" => "Usuario no autenticado"]);
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    // ==========================================
    // MÉTRICAS PARA DOCENTE / ADMIN (Rol 2 o 3)
    // ==========================================
    if ($accion === 'metricasDocente') {
        if ($usuarioRol !== 2 && $usuarioRol !== 3) {
            echo json_encode(["status" => "error", "message" => "Acceso no autorizado para este rol"]);
            exit;
        }

        // 1. Contadores principales
        $totalFamilias = (int)$conn->query("SELECT COUNT(*) FROM familia")->fetchColumn();
        $totalGeneros = (int)$conn->query("SELECT COUNT(*) FROM genero")->fetchColumn();
        $totalEspecies = (int)$conn->query("SELECT COUNT(*) FROM especie")->fetchColumn();
        $totalPruebas = (int)$conn->query("SELECT COUNT(*) FROM prueba")->fetchColumn();
        $totalExamenes = (int)$conn->query("SELECT COUNT(*) FROM examen WHERE bEstado = 1")->fetchColumn();
        $totalEvaluaciones = (int)$conn->query("SELECT COUNT(*) FROM calificacion")->fetchColumn();

        // 2. Evaluaciones pendientes de calificar
        $totalPendientes = (int)$conn->query("SELECT COUNT(*) FROM calificacion WHERE cCalificacion IS NULL")->fetchColumn();

        // 3. Distribución de notas (Aprobados >= 11, Desaprobados < 11)
        $totalAprobados = (int)$conn->query("SELECT COUNT(*) FROM calificacion WHERE cCalificacion >= 11")->fetchColumn();
        $totalDesaprobados = (int)$conn->query("SELECT COUNT(*) FROM calificacion WHERE cCalificacion < 11 AND cCalificacion IS NOT NULL")->fetchColumn();

        // 4. Promedio general
        $promedioRow = $conn->query("SELECT AVG(cCalificacion) as promedio FROM calificacion WHERE cCalificacion IS NOT NULL")->fetch(PDO::FETCH_ASSOC);
        $promedioGeneral = $promedioRow && $promedioRow['promedio'] !== null ? round((float)$promedioRow['promedio'], 2) : 0;

        // 5. Últimos 5 exámenes activos con conteo de preguntas y rendidos
        $stmtExamenes = $conn->query("
            SELECT e.nExamen, e.cExamen, e.cCodigoExamen, e.fechaRegistro,
                   COUNT(DISTINCT p.nPregunta) AS totalPreguntas,
                   COUNT(DISTINCT c.nCalificacion) AS totalRendidos
            FROM examen e
            LEFT JOIN pregunta p ON e.nExamen = p.nExamen
            LEFT JOIN calificacion c ON e.nExamen = c.nExamen
            WHERE e.bEstado = 1
            GROUP BY e.nExamen, e.cExamen, e.cCodigoExamen, e.fechaRegistro
            ORDER BY e.nExamen DESC
            LIMIT 5
        ");
        $ultimosExamenes = $stmtExamenes->fetchAll(PDO::FETCH_ASSOC);

        // 6. Últimas 6 entregas de alumnos
        $stmtEntregas = $conn->query("
            SELECT c.nCalificacion, c.cCalificacion, c.fechaRegistro,
                   u.cNombres, u.cApePaterno, u.cApeMaterno,
                   e.cExamen, e.cCodigoExamen
            FROM calificacion c
            INNER JOIN usuario u ON c.nUsuario = u.nUsuario
            INNER JOIN examen e ON c.nExamen = e.nExamen
            ORDER BY c.nCalificacion DESC
            LIMIT 6
        ");
        $ultimasEntregas = $stmtEntregas->fetchAll(PDO::FETCH_ASSOC);

        // 7. Primer examen con evaluaciones pendientes (para navegación directa)
        $primerPendiente = $conn->query("
            SELECT e.cCodigoExamen, e.cExamen, c.nCalificacion
            FROM calificacion c
            INNER JOIN examen e ON c.nExamen = e.nExamen
            WHERE c.cCalificacion IS NULL
            ORDER BY c.nCalificacion ASC
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "ok",
            "data" => [
                "kpis" => [
                    "familias" => $totalFamilias,
                    "generos" => $totalGeneros,
                    "especies" => $totalEspecies,
                    "pruebas" => $totalPruebas,
                    "examenes" => $totalExamenes,
                    "evaluaciones" => $totalEvaluaciones,
                    "pendientes" => $totalPendientes,
                    "aprobados" => $totalAprobados,
                    "desaprobados" => $totalDesaprobados,
                    "promedio" => $promedioGeneral
                ],
                "primerExamenPendiente" => $primerPendiente ? $primerPendiente['cCodigoExamen'] : null,
                "nombreExamenPendiente" => $primerPendiente ? $primerPendiente['cExamen'] : null,
                "ultimosExamenes" => $ultimosExamenes,
                "ultimasEntregas" => $ultimasEntregas
            ]
        ]);
        exit;
    }

    // ==========================================
    // MÉTRICAS PARA ALUMNO (Rol 1)
    // ==========================================
    if ($accion === 'metricasAlumno') {
        if ($usuarioRol !== 1) {
            echo json_encode(["status" => "error", "message" => "Acceso no autorizado"]);
            exit;
        }

        // 1. Exámenes rendidos por el estudiante
        $stmtRendidos = $conn->prepare("SELECT COUNT(*) FROM calificacion WHERE nUsuario = :uid");
        $stmtRendidos->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmtRendidos->execute();
        $totalRendidos = (int)$stmtRendidos->fetchColumn();

        // 2. Evaluaciones pendientes de calificar
        $stmtPendientes = $conn->prepare("SELECT COUNT(*) FROM calificacion WHERE nUsuario = :uid AND cCalificacion IS NULL");
        $stmtPendientes->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmtPendientes->execute();
        $totalPendientes = (int)$stmtPendientes->fetchColumn();

        // 3. Promedio del alumno
        $stmtPromedio = $conn->prepare("SELECT AVG(cCalificacion) as promedio FROM calificacion WHERE nUsuario = :uid AND cCalificacion IS NOT NULL");
        $stmtPromedio->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmtPromedio->execute();
        $promRow = $stmtPromedio->fetch(PDO::FETCH_ASSOC);
        $promedioAlumno = $promRow && $promRow['promedio'] !== null ? round((float)$promRow['promedio'], 2) : 0;

        // 4. Historial de exámenes del alumno
        $stmtHistorial = $conn->prepare("
            SELECT c.nCalificacion, c.cCalificacion, c.fechaRegistro,
                   e.cExamen, e.cCodigoExamen
            FROM calificacion c
            INNER JOIN examen e ON c.nExamen = e.nExamen
            WHERE c.nUsuario = :uid
            ORDER BY c.nCalificacion DESC
        ");
        $stmtHistorial->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmtHistorial->execute();
        $historialExamenes = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "ok",
            "data" => [
                "kpis" => [
                    "rendidos" => $totalRendidos,
                    "calificados" => max(0, $totalRendidos - $totalPendientes),
                    "pendientes" => $totalPendientes,
                    "promedio" => $promedioAlumno
                ],
                "historial" => $historialExamenes
            ]
        ]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Acción no especificada"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error del servidor: " . $e->getMessage()]);
}
?>
