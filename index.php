<?php
session_start();
$usuarioId = isset($_SESSION["usuario"]["nUsuario"]) ? intval($_SESSION["usuario"]["nUsuario"]) : null;
$usuarioNombre = isset($_SESSION["usuario"]["cNombres"]) ? $_SESSION["usuario"]["cNombres"] : null;
$usuarioRol = isset($_SESSION["usuario"]["nRol"]) ? intval($_SESSION["usuario"]["nRol"]) : null;

// Título de la pestaña según el rol
$linkPortalTexto = "Muestras";
if ($usuarioRol === 1) {
    $linkPortalTexto = "Evaluaciones";
} elseif ($usuarioRol === 2) {
    $linkPortalTexto = "Gestión de Muestras";
} elseif ($usuarioRol === 3) {
    $linkPortalTexto = "Administración";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cepario Virtual | Microbiología & Diagnóstico de Laboratorio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Modal Banner / Afiche Informativo del Proyecto (Cerrable y Reabrible) -->
    <div id="modal-banner-1" class="modal-banner" style="display:none;">
        <div class="modal-content">
            <button type="button" class="close-button" onclick="closeModal()" title="Cerrar afiche">&times;</button>
            <img src="img/productivo.jpg" alt="Afiche del Proyecto - Cepario Virtual" onerror="this.parentElement.parentElement.style.display='none'">    
        </div>
    </div>

    <!-- Encabezado Principal -->
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo" title="Cepario Virtual IESTP Trujillo">
                <img src="imagenes/logo.jpg" alt="Logo Cepario Virtual">
            </a>

            <nav class="nav-menu" id="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link active">Inicio</a></li>
                    <li class="nav-item"><a href="muestras.php" class="nav-link"><?php echo htmlspecialchars($linkPortalTexto); ?></a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contactanos.php" class="nav-link">Contáctanos</a></li>
                </ul>

                <div class="header-user-actions">
                    <?php if ($usuarioNombre): ?>
                        <div class="user-greeting">
                            <span class="user-avatar"><i class="fas fa-user-circle"></i></span>
                            <span class="user-name"><?php echo htmlspecialchars($usuarioNombre); ?></span>
                        </div>
                        <a href="logout.php" class="btn-logout" title="Cerrar Sesión">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">
                            <i class="fas fa-user"></i> Iniciar Sesión
                        </a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="hamburger" id="hamburger" aria-label="Menú">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </header>

    <!-- Hero Section: Cepario Virtual -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-microscope"></i> Cepario Virtual Microbiológico
                </div>
                <h1 class="hero-title">
                    Banco de Cepas Bacterianas y <span class="highlight">Diagnóstico de Laboratorio</span>
                </h1>
                <p class="hero-description">
                    Plataforma interactiva para la observación taxonómica, consulta de pruebas bioquímicas diferenciales y evaluación de competencias microbiológicas.
                </p>

                <div class="hero-actions">
                    <a href="muestras.php" class="btn-cta-primary">
                        <i class="fas fa-vial"></i> <?php echo $usuarioRol === 1 ? 'Ir a Mis Evaluaciones' : ($usuarioRol === 2 ? 'Administrar Cepario' : 'Explorar Cepas & Muestras'); ?>
                    </a>
                    <button type="button" class="btn-cta-secondary" onclick="openModalAfiche()">
                        <i class="fas fa-image"></i> Ver Afiche del Proyecto
                    </button>
                </div>

                <div class="hero-stats">
                    <div class="stat-box">
                        <span class="stat-number">Cepas</span>
                        <span class="stat-label">Familias & Especies</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-box">
                        <span class="stat-number">Bioquímica</span>
                        <span class="stat-label">Baterías Diferenciales</span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-box">
                        <span class="stat-number">Evaluación</span>
                        <span class="stat-label">Retroalimentación Docente</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Pilares del Cepario Virtual -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Pilares del Cepario</span>
                <h2>Estructura del Aprendizaje Microbiológico</h2>
                <p>Información clara y visual orientada a la identificación bacteriana de laboratorio.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon icon-blue">
                        <i class="fas fa-dna"></i>
                    </div>
                    <h3>Banco Taxonómico</h3>
                    <p>Clasificación jerárquica de Familias, Géneros y Especies con características morfológicas.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-emerald">
                        <i class="fas fa-flask"></i>
                    </div>
                    <h3>Pruebas Bioquímicas</h3>
                    <p>Registro fotográfico de reacciones en medios diferenciales como TSI, LIA, Citrato y SIM.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-purple">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h3>Evaluaciones Prácticas</h3>
                    <p>Resolución de casos y pruebas visuales con calificación y retroalimentación personalizada.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-amber">
                        <i class="fas fa-book-medical"></i>
                    </div>
                    <h3>Biblioteca de Consulta</h3>
                    <p>Material de estudio, guías técnicas y protocolos microbiológicos de apoyo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Muestras Destacadas del Cepario -->
    <section class="samples-preview-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Colección Microbiológica</span>
                <h2>Muestras y Pruebas Destacadas</h2>
                <p>Revisa algunas de las cepas y baterías bioquímicas presentes en el cepario.</p>
            </div>

            <div class="samples-preview-grid">
                <div class="sample-preview-card">
                    <div class="sample-img-box">
                        <img src="imagenes/1.png" alt="Enterobacterias" loading="lazy">
                        <span class="sample-tag">Bacilos Gram (-)</span>
                    </div>
                    <div class="sample-content">
                        <h4>Enterobacteriaceae</h4>
                        <p>Bacilos Gram negativos oxidasa negativos, fermentadores de glucosa de gran relevancia clínica.</p>
                        <a href="muestras.php" class="sample-link">Ver en el cepario &rarr;</a>
                    </div>
                </div>

                <div class="sample-preview-card">
                    <div class="sample-img-box">
                        <img src="imagenes/2.jpg" alt="Batería IMViC" loading="lazy">
                        <span class="sample-tag">Batería Diferencial</span>
                    </div>
                    <div class="sample-content">
                        <h4>Batería Bioquímica IMViC</h4>
                        <p>Pruebas diferenciales (Indol, Rojo de Metilo, Voges-Proskauer, Citrato) para diferenciación.</p>
                        <a href="muestras.php" class="sample-link">Ver en el cepario &rarr;</a>
                    </div>
                </div>

                <div class="sample-preview-card">
                    <div class="sample-img-box">
                        <img src="imagenes/3.png" alt="Medio LIA" loading="lazy">
                        <span class="sample-tag">Medio Diferencial</span>
                    </div>
                    <div class="sample-content">
                        <h4>Lisina Hierro Agar (LIA)</h4>
                        <p>Medio diagnóstico para detectar descarboxilación/desaminación de lisina y producción de gas H2S.</p>
                        <a href="muestras.php" class="sample-link">Ver en el cepario &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Banner CTA / Acceso al Cepario -->
    <section class="cta-banner-section">
        <div class="container">
            <div class="cta-card">
                <div class="cta-text">
                    <h2>Accede a las Evaluaciones y Catálogo</h2>
                    <p>Ingresa con tu código de evaluación o explora las cepas y fotografías de pruebas de laboratorio.</p>
                </div>
                <div class="cta-button-wrap">
                    <a href="muestras.php" class="btn-cta-white">
                        <i class="fas fa-arrow-right"></i> Ingresar al Cepario
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer con Créditos Oficiales -->
    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-col">
                <div class="footer-logo">
                    <i class="fas fa-flask"></i>
                    <span>Cepario Virtual</span>
                </div>
                <p>Plataforma académica para el estudio interactivo de cepas bacterianas y diagnóstico microbiológico.</p>
                <div class="footer-socials">
                    <a href="https://es-la.facebook.com/people/IESTP-Trujillo/100057443259181/" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/iestp_trujillo/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.tiktok.com/@iestp.trujillo" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Navegación</h4>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="muestras.php"><?php echo htmlspecialchars($linkPortalTexto); ?></a></li>
                    <li><a href="blog.php">Blog & Guías</a></li>
                    <li><a href="contactanos.php">Contacto</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Créditos del Proyecto</h4>
                <div class="credits-box">
                    <p class="credits-title"><i class="fas fa-code"></i> Desarrollo Web:</p>
                    <p class="credits-names">Vasquez Miller, Cristian Sebastian<br>Aguilar Canchachi, Josbeth Esnayder</p>
                    <p class="credits-title" style="margin-top: 10px;"><i class="fas fa-vial"></i> Muestras & Experimentos:</p>
                    <p class="credits-note">Material fotográfico de pruebas, cultivos y validación experimental proporcionados por la Docencia de Laboratorio Clínico.</p>
                </div>
            </div>

            <div class="footer-col">
                <h4>Contacto Institucional</h4>
                <p><i class="fas fa-map-marker-alt"></i> Psje. Olaya N° 180, Trujillo, Perú</p>
                <p><i class="fas fa-phone"></i> (044) 350009</p>
                <p><i class="fas fa-envelope"></i> informes@iestp-trujillo.edu.pe</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> Cepario Virtual - IESTP Trujillo. Todos los derechos reservados.</p>
                <p class="footer-authors-badge">Desarrollado por Vasquez Miller, Cristian Sebastian & Aguilar Canchachi, Josbeth Esnayder | Contenido experimental por Docencia de Laboratorio Clínico</p>
            </div>
        </div>
    </footer>

    <script src="JS/barradenavegacion.js"></script>
</body>
</html>