<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';
if (!isset($_SESSION["usuario"]["nUsuario"]) || !isset($_SESSION["usuario"]["nRol"])) {
    die("Acceso no autorizado: usuario no identificado.");
}

$usuarioId = intval($_SESSION["usuario"]["nUsuario"]);
$usuarioRol = intval($_SESSION["usuario"]["nRol"]);
if ($usuarioId <= 0 || $usuarioRol !== 1) {
    die("Acceso no autorizado: sólo estudiantes pueden acceder a este portal.");
}

$usuarioNombre = $_SESSION["usuario"]["cNombres"] ?? "Estudiante";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Estudiante | Evaluaciones de Laboratorio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/alumno.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo" title="Laboratorio Clínico IESTP Trujillo">
                <img src="imagenes/logo.jpg" alt="Logo Laboratorio">
            </a>

            <nav class="nav-menu" id="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link">Inicio</a></li>
                    <li class="nav-item"><a href="muestras.php" class="nav-link active">Evaluaciones</a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contactanos.php" class="nav-link">Contáctanos</a></li>
                </ul>
                <div class="header-user-actions">
                    <span class="user-pill-tag"><i class="fas fa-user-graduate"></i> Alumno: <?php echo htmlspecialchars($usuarioNombre); ?></span>
                    <a href="logout.php" class="btn-logout-nav" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </nav>

            <div class="hamburger" id="hamburger" aria-label="Menú">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </header>

    <main class="container portal-container">
        <div class="management-wrapper">
            <!-- Sidebar de Navegación del Alumno -->
            <aside class="management-sidebar">
                <div class="sidebar-student-profile">
                    <div class="profile-avatar"><i class="fas fa-user-graduate"></i></div>
                    <div class="profile-info">
                        <h4><?php echo htmlspecialchars($usuarioNombre); ?></h4>
                        <span>Estudiante</span>
                    </div>
                </div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="#panel-dashboard" class="sidebar-btn active">
                            <i class="fas fa-th-large sidebar-icon"></i>
                            <span>Mi Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-examenes" class="sidebar-btn">
                            <i class="fas fa-file-signature sidebar-icon"></i>
                            <span>Rendir Evaluación</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-resultado-examen" class="sidebar-btn">
                            <i class="fas fa-chart-bar sidebar-icon"></i>
                            <span>Mis Calificaciones</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Área de Contenido Principal -->
            <section class="management-content">
                <!-- Panel 1: Dashboard del Alumno -->
                <div id="panel-dashboard" class="content-panel active">
                    <!-- Banner de Bienvenida -->
                    <div class="dashboard-welcome-box">
                        <div class="welcome-text">
                            <h2>¡Bienvenido/a, <?php echo htmlspecialchars($usuarioNombre); ?>! 🔬</h2>
                            <p>Este es tu portal de evaluaciones prácticas y seguimiento académico de microbiología.</p>
                        </div>
                    </div>

                    <!-- Cuadro de Acceso Rápido al Examen -->
                    <div class="quick-exam-card">
                        <div class="quick-exam-info">
                            <div class="quick-icon"><i class="fas fa-key"></i></div>
                            <div>
                                <h4>¿Tienes un código de examen?</h4>
                                <p>Ingresa el código de 6 dígitos que te brindó tu docente para comenzar tu prueba:</p>
                            </div>
                        </div>
                        <form id="form-quick-examen" class="quick-exam-form-row">
                            <input type="text" id="quick-codigo-examen" placeholder="Ej: 877509" maxlength="10" required>
                            <button type="submit" class="btn-primary-action"><i class="fas fa-arrow-right"></i> Rendir Examen</button>
                        </form>
                    </div>

                    <!-- Métricas del Alumno -->
                    <div class="kpi-cards-grid">
                        <div class="kpi-stat-card" onclick="navegarAPanelAlumno('panel-resultado-examen')">
                            <div class="kpi-icon-box icon-exams"><i class="fas fa-tasks"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Exámenes Rendidos</span>
                                <h3 class="kpi-num" id="alumno-kpi-rendidos">0</h3>
                                <span class="kpi-hint">Total de pruebas enviadas</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card">
                            <div class="kpi-icon-box icon-grade"><i class="fas fa-award"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Promedio Acumulado</span>
                                <h3 class="kpi-num" id="alumno-kpi-promedio">0.0</h3>
                                <span class="kpi-hint">Escala de 0 a 20</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" onclick="navegarAPanelAlumno('panel-resultado-examen')">
                            <div class="kpi-icon-box icon-calificados"><i class="fas fa-check-double"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Exámenes Calificados</span>
                                <h3 class="kpi-num" id="alumno-kpi-calificados">0</h3>
                                <span class="kpi-hint">Con nota y observaciones</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" onclick="navegarAPanelAlumno('panel-resultado-examen')">
                            <div class="kpi-icon-box icon-pending"><i class="fas fa-hourglass-half"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">En Revisión</span>
                                <h3 class="kpi-num" id="alumno-kpi-pendientes">0</h3>
                                <span class="kpi-hint">Pendientes por docente</span>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Evaluaciones -->
                    <div class="history-card-box">
                        <div class="history-card-header">
                            <h4><i class="fas fa-history"></i> Historial de Evaluaciones</h4>
                            <button type="button" class="btn-outline-small" onclick="navegarAPanelAlumno('panel-resultado-examen')">
                                <i class="fas fa-search"></i> Consultar Código
                            </button>
                        </div>
                        <div class="table-responsive-wrapper">
                            <table id="tabla-alumno-historial" class="custom-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre del Examen</th>
                                        <th>Código</th>
                                        <th>Calificación</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="5" style="text-align:center; padding:20px; color:#64748b;">Cargando tu historial...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Panel 2: Acceso Manual a Examen -->
                <div id="panel-examenes" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Rendir Evaluación</h3>
                            <p>Ingresa el código del examen asignado por tu docente para responder las preguntas.</p>
                        </div>

                        <div class="form-container-card">
                            <form id="form-acceso-examen">
                                <div class="form-group-custom">
                                    <label for="codigoExamen">Código de Evaluación (6 dígitos):</label>
                                    <input type="text" id="codigoExamen" name="codigoExamen" placeholder="Ej: 464528" required>
                                </div>
                                <button type="submit" class="btn-primary-action"><i class="fas fa-sign-in-alt"></i> Iniciar Evaluación</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Examen Activo (Respuesta en Vivo) -->
                <div id="panel-examen-activo" class="content-panel">
                    <div class="exam-active-card">
                        <div class="exam-header-banner">
                            <span class="exam-tag-live">Evaluación en Progreso</span>
                            <h3 id="examen-titulo">Título del Examen</h3>
                            <p>Lee atentamente cada pregunta e ingresa tus respuestas detalladas.</p>
                        </div>

                        <div id="contenedor-preguntas" class="preguntas-container-list"></div>

                        <div class="examen-actions-footer">
                            <button type="button" id="btn-enviar-examen" class="btn-primary-action btn-large">
                                <i class="fas fa-paper-plane"></i> Finalizar y Enviar Evaluación
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel 4: Resultado y Retroalimentación -->
                <div id="panel-resultado-examen" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Consulta de Calificaciones & Observaciones</h3>
                            <p>Ingresa el código del examen para revisar tu nota y las observaciones dejadas por tu profesor.</p>
                        </div>

                        <div id="contenedor-formulario-revision" class="form-container-card">
                            <form id="form-acceso-revision">
                                <div class="form-group-custom">
                                    <label for="codigoExamenRevision">Código de Examen:</label>
                                    <input type="text" id="codigoExamenRevision" name="codigoExamenRevision" placeholder="Ej: 877509" required>
                                </div>
                                <button type="submit" class="btn-primary-action"><i class="fas fa-search"></i> Ver Calificación</button>
                            </form>
                        </div>

                        <div id="contenedor-resultados" style="display:none; margin-top:30px;">
                            <div id="resumen-nota" class="grade-summary-box"></div>
                            <div id="contenedor-respuestas-revisadas" class="review-answers-box"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container footer-bottom-content" style="text-align:center; padding:25px 0; color:#94a3b8; font-size:0.9rem;">
            <p>&copy; <?php echo date('Y'); ?> Cepario Virtual - IESTP Trujillo. Todos los derechos reservados.</p>
            <p style="font-size:0.8rem; color:#64748b; margin-top:4px;">Desarrollado por Vasquez Miller, Cristian Sebastian & Aguilar Canchachi, Josbeth Esnayder | Contenido experimental por Docencia de Laboratorio Clínico</p>
        </div>
    </footer>

    <script src="JS/barradenavegacion.js"></script>
    <script src="JS/alumno_dashboard.js"></script>
    <script src="JS/back_estudiante.js"></script>
</body>
</html>