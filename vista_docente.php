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
if ($usuarioId <= 0 || ($usuarioRol !== 2 && $usuarioRol !== 3)) {
    die("Acceso no autorizado: sólo docentes y administradores pueden acceder.");
}

$usuarioNombre = $_SESSION["usuario"]["cNombres"] ?? "Docente";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestión Docente | Laboratorio Clínico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/docente.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li class="nav-item"><a href="muestras.php" class="nav-link active">Gestión de Muestras</a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contactanos.php" class="nav-link">Contáctanos</a></li>
                </ul>
                <div class="header-user-actions">
                    <span class="user-pill-tag"><i class="fas fa-chalkboard-teacher"></i> Docente: <?php echo htmlspecialchars($usuarioNombre); ?></span>
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
            <!-- Sidebar Docente -->
            <aside class="management-sidebar">
                <div class="sidebar-teacher-profile">
                    <div class="profile-avatar"><i class="fas fa-microscope"></i></div>
                    <div class="profile-info">
                        <h4><?php echo htmlspecialchars($usuarioNombre); ?></h4>
                        <span>Docente de Laboratorio</span>
                    </div>
                </div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="#panel-dashboard" class="sidebar-btn active">
                            <i class="fas fa-chart-line sidebar-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="hongos.php" class="sidebar-btn">
                            <i class="fas fa-mushroom sidebar-icon" style="font-family: 'Font Awesome 6 Free'; font-weight: 900; font-style: normal;">🍄</i>
                            <span>Hongos</span>
                        </a>
                    </li>
                    <li>
                        <a href="parasitos.php" class="sidebar-btn">
                            <i class="fas fa-bug sidebar-icon"></i>
                            <span>Parásitos</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-familias" class="sidebar-btn">
                            <i class="fas fa-sitemap sidebar-icon"></i>
                            <span>Familias</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-generos" class="sidebar-btn">
                            <i class="fas fa-folder-tree sidebar-icon"></i>
                            <span>Géneros</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-especies" class="sidebar-btn">
                            <i class="fas fa-dna sidebar-icon"></i>
                            <span>Especies</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-pruebas" class="sidebar-btn">
                            <i class="fas fa-vial sidebar-icon"></i>
                            <span>Pruebas y Cultivos</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-examenes" class="sidebar-btn">
                            <i class="fas fa-file-signature sidebar-icon"></i>
                            <span>Crear Exámenes</span>
                        </a>
                    </li>
                    <li>
                        <a href="#panel-revision-examenes" class="sidebar-btn">
                            <i class="fas fa-clipboard-check sidebar-icon"></i>
                            <span>Revisión de Exámenes</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Contenido Principal -->
            <section class="management-content">
                <!-- Panel 1: Dashboard -->
                <div id="panel-dashboard" class="content-panel active">
                    <div class="dashboard-welcome-box">
                        <div class="welcome-text">
                            <h2>¡Hola, Profesor/a <?php echo htmlspecialchars($usuarioNombre); ?>! 🔬</h2>
                            <p>Panel integral de control para el catálogo taxonómico, banco de pruebas y evaluaciones prácticas.</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions-bar">
                        <span class="quick-title"><i class="fas fa-bolt"></i> Acciones Rápidas:</span>
                        <div class="quick-buttons-row">
                            <button type="button" class="btn-quick-chip" onclick="navegarAPanel('panel-examenes')">
                                <i class="fas fa-plus-circle"></i> Crear Examen
                            </button>
                            <button type="button" class="btn-quick-chip" onclick="navegarAPanel('panel-pruebas'); document.getElementById('mostrarFormularioBtn')?.click();">
                                <i class="fas fa-vial"></i> Nueva Prueba
                            </button>
                            <button type="button" class="btn-quick-chip" onclick="navegarAPanel('panel-revision-examenes')">
                                <i class="fas fa-check-circle"></i> Revisar Entregas
                            </button>
                        </div>
                    </div>

                    <!-- Grid de KPIs -->
                    <div class="kpi-cards-grid">
                        <div class="kpi-stat-card" onclick="navegarAPanel('panel-familias')" title="Ver Familias">
                            <div class="kpi-icon-box icon-family"><i class="fas fa-sitemap"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Familias</span>
                                <h3 class="kpi-num" id="kpi-familias-val">0</h3>
                                <span class="kpi-hint">Familias registradas</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" onclick="navegarAPanel('panel-generos')" title="Ver Géneros">
                            <div class="kpi-icon-box icon-genero"><i class="fas fa-folder-tree"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Géneros</span>
                                <h3 class="kpi-num" id="kpi-generos-val">0</h3>
                                <span class="kpi-hint">Géneros taxonómicos</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" onclick="navegarAPanel('panel-especies')" title="Ver Especies">
                            <div class="kpi-icon-box icon-species"><i class="fas fa-dna"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Especies</span>
                                <h3 class="kpi-num" id="kpi-especies-val">0</h3>
                                <span class="kpi-hint">Especies en catálogo</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" onclick="navegarAPanel('panel-pruebas')" title="Ver Pruebas">
                            <div class="kpi-icon-box icon-samples"><i class="fas fa-vials"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Pruebas / Muestras</span>
                                <h3 class="kpi-num" id="kpi-pruebas-val">0</h3>
                                <span class="kpi-hint">Cultivos fotográficos</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" onclick="navegarAPanel('panel-examenes')" title="Ver Exámenes">
                            <div class="kpi-icon-box icon-exams"><i class="fas fa-file-alt"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Exámenes Activos</span>
                                <h3 class="kpi-num" id="kpi-examenes-val">0</h3>
                                <span class="kpi-hint">Banco de evaluaciones</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" id="kpi-card-pendientes" onclick="irAPendientesCalificar()" title="Toca para ir directamente a calificar las evaluaciones pendientes">
                            <div class="kpi-icon-box icon-pending"><i class="fas fa-hourglass-half"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Por Calificar</span>
                                <h3 class="kpi-num" id="kpi-pendientes-val">0</h3>
                                <span class="kpi-hint">Ir a calificar ➡️</span>
                            </div>
                        </div>

                        <div class="kpi-stat-card" title="Promedio de notas">
                            <div class="kpi-icon-box icon-grade"><i class="fas fa-award"></i></div>
                            <div class="kpi-data">
                                <span class="kpi-title">Promedio General</span>
                                <h3 class="kpi-num" id="kpi-promedio-val">0.0</h3>
                                <span class="kpi-hint">Escala de notas 0-20</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fila con Gráfico Estadístico de Rendimiento -->
                    <div class="dashboard-analytics-row" style="grid-template-columns: 1fr; max-width: 620px; margin: 25px auto 0 auto;">
                        <!-- Gráfico Estadístico Chart.js -->
                        <div class="analytics-card-box">
                            <div class="analytics-card-header">
                                <h4><i class="fas fa-chart-pie"></i> Rendimiento Académico de Alumnos</h4>
                                <span class="badge-sub-info">En tiempo real</span>
                            </div>
                            <div class="chart-canvas-wrapper" style="min-height: 280px;">
                                <canvas id="chart-rendimiento-docente"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Últimos Exámenes Creados -->
                    <div class="history-card-box" style="margin-top: 25px;">
                        <div class="history-card-header">
                            <h4><i class="fas fa-clipboard-list"></i> Últimos Exámenes Creados</h4>
                            <button type="button" class="btn-outline-small" onclick="navegarAPanel('panel-examenes')">
                                <i class="fas fa-plus"></i> Crear Nuevo Examen
                            </button>
                        </div>
                        <div class="table-responsive-wrapper">
                            <table id="tabla-dashboard-examenes" class="custom-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Título del Examen</th>
                                        <th>Código de Acceso</th>
                                        <th>Detalles</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="5" style="text-align:center; padding:20px; color:#64748b;">Cargando lista de exámenes...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabla de Últimas Entregas Recibidas -->
                    <div class="history-card-box" style="margin-top: 25px;">
                        <div class="history-card-header">
                            <h4><i class="fas fa-user-check"></i> Últimas Evaluaciones Recibidas</h4>
                            <button type="button" class="btn-outline-small" onclick="navegarAPanel('panel-revision-examenes')">
                                <i class="fas fa-search"></i> Buscar por Código
                            </button>
                        </div>
                        <div class="table-responsive-wrapper">
                            <table id="tabla-dashboard-entregas" class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Examen</th>
                                        <th>Nota</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="4" style="text-align:center; padding:20px; color:#64748b;">Cargando entregas recientes...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Familias -->
                <div id="panel-familias" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Gestión de Familias Taxonómicas</h3>
                            <p>Registra y administra las familias bacterianas que organizan los géneros y especies.</p>
                        </div>

                        <div class="form-container-card">
                            <form id="form-familias">
                                <input type="hidden" id="id_familia_edit" name="nFamilia" value="0">
                                <input type="hidden" id="accion_familia" name="accion" value="agregar">
                                <div class="form-group-custom">
                                    <label for="nombre_familia">Nombre de la Familia:</label>
                                    <input type="text" id="nombre_familia" name="cFamilia" placeholder="Ej: Enterobacteriaceae" required>
                                </div>
                                <div class="form-actions-row">
                                    <button type="submit" class="btn-submit btn-submit-action"><i class="fas fa-save"></i> Guardar Familia</button>
                                    <button type="button" id="btn-cancelar" class="btn-cancel-action" style="display:none;"><i class="fas fa-times"></i> Cancelar</button>
                                </div>
                            </form>
                        </div>

                        <div class="table-section-box">
                            <div class="table-search-bar-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" id="buscar-familias" placeholder="Buscar familia por nombre...">
                            </div>
                            <div class="table-responsive-wrapper">
                                <table id="table-familias" class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre de Familia</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 3: Géneros -->
                <div id="panel-generos" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Gestión de Géneros</h3>
                            <p>Asocia cada género a su respectiva familia taxonómica.</p>
                        </div>

                        <div class="form-container-card">
                            <form id="form-generos">
                                <input type="hidden" id="id_genero_edit" name="nGenero" value="0">
                                <input type="hidden" id="accion_genero" name="accion" value="agregar">
                                <div class="form-group-custom">
                                    <label for="familia_select_genero">Seleccionar Familia:</label>
                                    <select id="familia_select_genero" name="nFamilia" required>
                                        <option value="">-- Elige una familia --</option>
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <label for="nombre_genero">Nombre del Género:</label>
                                    <input type="text" id="nombre_genero" name="cGenero" placeholder="Ej: Escherichia" required>
                                </div>
                                <div class="form-actions-row">
                                    <button type="submit" class="btn-submit btn-submit-action"><i class="fas fa-save"></i> Guardar Género</button>
                                    <button type="button" id="btn-cancelar-genero" class="btn-cancel-action" style="display:none;"><i class="fas fa-times"></i> Cancelar</button>
                                </div>
                            </form>
                        </div>

                        <div class="table-section-box">
                            <div class="table-search-bar-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" id="buscar-generos" placeholder="Buscar género o familia...">
                            </div>
                            <div class="table-responsive-wrapper">
                                <table id="table-generos" class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Familia</th>
                                            <th>Género</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 4: Especies -->
                <div id="panel-especies" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Gestión de Especies</h3>
                            <p>Organiza las especies pertenecientes a cada género y familia.</p>
                        </div>

                        <div class="form-container-card">
                            <form id="form-especies">
                                <input type="hidden" id="id_especie_edit" name="nEspecie" value="0">
                                <input type="hidden" id="accion_especie" name="accion" value="agregar">
                                <div class="form-group-custom">
                                    <label for="familia_select_especie">Seleccionar Familia:</label>
                                    <select id="familia_select_especie" name="nFamilia" required>
                                        <option value="">-- Elige una familia --</option>
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <label for="genero_select_especie">Seleccionar Género:</label>
                                    <select id="genero_select_especie" name="nGenero" required disabled>
                                        <option value="">-- Elige un género --</option>
                                    </select>
                                </div>
                                <div class="form-group-custom">
                                    <label for="nombre_especie">Nombre de la Especie:</label>
                                    <input type="text" id="nombre_especie" name="cEspecie" placeholder="Ej: coli" required>
                                </div>
                                <div class="form-actions-row">
                                    <button type="submit" class="btn-submit btn-submit-action"><i class="fas fa-save"></i> Guardar Especie</button>
                                    <button type="button" id="btn-cancelar-especie" class="btn-cancel-action" style="display:none;"><i class="fas fa-times"></i> Cancelar</button>
                                </div>
                            </form>
                        </div>

                        <div class="table-section-box">
                            <div class="table-search-bar-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" id="buscar-especies" placeholder="Buscar especie, género o familia...">
                            </div>
                            <div class="table-responsive-wrapper">
                                <table id="table-especies" class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Familia</th>
                                            <th>Género</th>
                                            <th>Especie</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 5: Pruebas y Muestras Clínicas -->
                <div id="panel-pruebas" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Banco de Pruebas y Muestras de Laboratorio</h3>
                            <p>Registra fotografías de cultivos, características morfológicas y resultados de pruebas diferenciales.</p>
                        </div>

                        <div class="header-action-button-bar">
                            <button type="button" id="mostrarFormularioBtn" class="btn-add-main">
                                <i class="fas fa-plus-circle"></i> Agregar Nueva Prueba
                            </button>
                        </div>

                        <div class="form-container-card-wide" id="contenedorFormularioPrueba">
                            <form id="formularioProducto" enctype="multipart/form-data" style="display:none;">
                                <input type="hidden" name="nPrueba" id="nPrueba" value="0">
                                <input type="hidden" name="accion" id="accion" value="agregar">
                                
                                <div class="form-grid-2col">
                                    <div class="form-group-custom">
                                        <label for="familiaSelect">Familia Taxonómica:</label>
                                        <select id="familiaSelect" name="nFamilia" required>
                                            <option value="" disabled selected>-- Elige una familia --</option>
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label for="generoSelect">Género:</label>
                                        <select id="generoSelect" name="nGenero" required disabled>
                                            <option value="" disabled selected>-- Elige un género --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-grid-2col">
                                    <div class="form-group-custom">
                                        <label for="especieSelect">Especie:</label>
                                        <select id="especieSelect" name="nEspecie" required disabled>
                                            <option value="" disabled selected>-- Elige una especie --</option>
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label for="nombrePruebaInput">Nombre de la Bacteria / Prueba:</label>
                                        <input type="text" id="nombrePruebaInput" name="cBacteria" placeholder="Ej: Escherichia coli" required>
                                    </div>
                                </div>

                                <div class="form-group-custom">
                                    <label for="descripcionInput">Descripción Morfológica / Características:</label>
                                    <textarea id="descripcionInput" name="cDescripcion" rows="3" placeholder="Describe las características microscópicas y macroscópicas..." required maxlength="1000"></textarea>
                                </div>

                                <div class="form-group-custom">
                                    <label for="resultadoInput">Resultados Bioquímicos (IMViC, LIA, TSI, Citrato, etc.):</label>
                                    <textarea id="resultadoInput" name="cResultado" rows="3" placeholder="Indol (+), RM (+), VP (-), Citrato (-), LIA K/A..." required maxlength="1000"></textarea>
                                </div>

                                <div class="form-group-custom">
                                    <label for="imagenInput">Fotografía de la Muestra / Cultivo:</label>
                                    <input type="file" id="imagenInput" name="cFoto" accept="image/*" required>
                                    <img id="previewImagen" src="" style="display:none; width: 160px; border-radius: 8px; margin-top: 10px; border: 1px solid #cbd5e0;">
                                </div>

                                <div class="form-actions-row">
                                    <button type="submit" id="saveButton" class="btn-submit btn-submit-action"><i class="fas fa-save"></i> Guardar Prueba</button>
                                    <button type="button" id="cancelButton" class="btn-cancel-action" style="display:none;"><i class="fas fa-times"></i> Cancelar</button>
                                </div>
                            </form>
                        </div>

                        <!-- Catálogo de Pruebas en Grid -->
                        <div class="catalog-section-box">
                            <div class="table-search-bar-wrap">
                                <i class="fas fa-search"></i>
                                <input type="text" id="buscar-pruebas" placeholder="Buscar por bacteria, características o resultados...">
                            </div>
                            <div id="contenedorProductos" class="lista-productos"></div>
                        </div>
                    </div>
                </div>

                <!-- Panel 6: Crear Exámenes -->
                <div id="panel-examenes" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Constructor de Exámenes Prácticos</h3>
                            <p>Crea evaluaciones con preguntas personalizadas asociadas a fotografías de muestras.</p>
                        </div>

                        <div class="exam-builder-grid">
                            <!-- Columna 1: Formulario para añadir preguntas con selectores en cascada -->
                            <div class="builder-column-card">
                                <h4>1. Añadir Preguntas al Examen</h4>
                                
                                <div class="form-group-custom">
                                    <label for="familia">Familia Taxonómica:</label>
                                    <select id="familia" class="select-field">
                                        <option value="">-- Seleccione familia --</option>
                                    </select>
                                </div>

                                <div class="form-group-custom">
                                    <label for="genero">Género:</label>
                                    <select id="genero" class="select-field" disabled>
                                        <option value="">-- Seleccione género --</option>
                                    </select>
                                </div>

                                <div class="form-group-custom">
                                    <label for="especie">Especie:</label>
                                    <select id="especie" class="select-field" disabled>
                                        <option value="">-- Seleccione especie --</option>
                                    </select>
                                </div>

                                <div class="form-group-custom">
                                    <label for="pruebaSelect">Asociar Imagen de Prueba:</label>
                                    <select id="pruebaSelect" class="select-field" disabled>
                                        <option value="">-- Seleccione prueba --</option>
                                    </select>
                                    <img id="preview" class="preview-img" style="display:none; max-width: 140px; border-radius: 8px; margin-top: 10px; border: 1px solid #cbd5e0;" alt="Vista previa imagen">
                                </div>

                                <div class="form-group-custom">
                                    <label for="descripcion">Consigna / Pregunta para el Estudiante:</label>
                                    <textarea id="descripcion" rows="3" placeholder="Ej: Observa la imagen del medio LIA e indica si hubo descarboxilación de lisina..."></textarea>
                                </div>

                                <div class="form-actions-row">
                                    <button type="button" id="agregarBtn" class="btn-primary-action"><i class="fas fa-plus"></i> Agregar Pregunta</button>
                                    <button type="button" id="limpiarBtn" class="btn-cancel-action"><i class="fas fa-eraser"></i> Limpiar</button>
                                </div>
                            </div>

                            <!-- Columna 2: Lista temporal y Guardado -->
                            <div class="builder-column-card">
                                <div class="list-header-flex">
                                    <h4>2. Preguntas en este Examen (<span id="count">0</span>)</h4>
                                    <button type="button" id="clearAll" class="btn-outline-small"><i class="fas fa-trash"></i> Limpiar</button>
                                </div>

                                <div id="listaPreguntas" class="preguntas-temporal-list"></div>

                                <div class="exam-save-bar">
                                    <button type="button" id="guardarExamen" class="btn-submit-action btn-large">
                                        <i class="fas fa-cloud-upload-alt"></i> Guardar y Publicar Examen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Exámenes Creados -->
                        <div class="table-section-box" style="margin-top: 35px;">
                            <div class="panel-section-title">
                                <h4>Banco de Exámenes Registrados</h4>
                            </div>
                            <div class="table-responsive-wrapper">
                                <table id="table-examenes" class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Título del Examen</th>
                                            <th>Código de Acceso</th>
                                            <th>Preguntas</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="examenes-guardados-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 7: Revisión y Calificación -->
                <div id="panel-revision-examenes" class="content-panel">
                    <div class="panel-inner-box">
                        <div class="panel-section-title">
                            <h3>Revisión de Exámenes</h3>
                            <p>Consulta las respuestas enviadas por los estudiantes, asígnales nota de 0 a 20 y agrega observaciones personalizadas.</p>
                        </div>

                        <div class="search-exam-box-card">
                            <form id="form-buscar-examen" class="search-exam-form-row">
                                <div class="search-input-field">
                                    <label for="codigoExamen">Ingresa el Código del Examen:</label>
                                    <input type="text" id="codigoExamen" name="codigoExamen" placeholder="Ej: 877509" required>
                                </div>
                                <button type="submit" class="btn-primary-action btn-buscar"><i class="fas fa-search"></i> Buscar Examen</button>
                            </form>
                        </div>

                        <div class="results-review-section" style="margin-top: 30px;">
                            <div class="review-header-flex">
                                <h4>Resultados: <span id="nombre-examen-resultado" style="color:#0284c7;"></span></h4>
                                <div class="export-buttons-group">
                                    <button type="button" class="btn-export-csv" onclick="exportarActaNotasCSV()">
                                        <i class="fas fa-file-csv"></i> Descargar CSV / Excel
                                    </button>
                                    <button type="button" class="btn-export-pdf" onclick="imprimirActaNotas()">
                                        <i class="fas fa-print"></i> Imprimir Acta
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive-wrapper">
                                <table id="table-resultados-examen" class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>APELLIDOS Y NOMBRES</th>
                                            <th>CALIFICACIÓN</th>
                                            <th>VER RESPUESTAS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="4" style="text-align:center; padding:20px; color:#64748b;">Ingresa un código arriba para cargar las evaluaciones.</td></tr>
                                    </tbody>
                                </table>
                            </div>
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
    <script src="JS/docente.js"></script>
    <script src="JS/docente_dashboard.js"></script>
    <script src="JS/back_docente.js"></script>
    <script src="JS/back_exam.js"></script>
</body>
</html>