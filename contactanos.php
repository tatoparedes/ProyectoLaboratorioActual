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
    <title>Contáctanos | Laboratorio Clínico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/contactanos.css">
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
                    <li class="nav-item"><a href="muestras.php" class="nav-link"><?php echo htmlspecialchars($linkPortalTexto); ?></a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contactanos.php" class="nav-link active">Contáctanos</a></li>
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
                        <a href="login.php" class="btn-login"><i class="fas fa-user"></i> Iniciar Sesión</a>
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

    <main class="contact-main">
        <div class="container">
            <div class="contact-header">
                <span class="section-tag">Canales Institucionales</span>
                <h1 class="page-title">Ponte en Contacto con Nosotros</h1>
                <p class="subtitle">¿Tienes consultas sobre el laboratorio o requieres asistencia técnica en la plataforma? Estamos para atenderte.</p>
            </div>

            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-icon icon-email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Correo Institucional</h3>
                    <p>informes@iestp-trujillo.edu.pe</p>
                    <span class="contact-hint">Atención de lunes a viernes</span>
                </div>

                <div class="contact-card">
                    <div class="contact-icon icon-phone">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Central Telefónica</h3>
                    <p>(044) 350009</p>
                    <span class="contact-hint">Mesa de partes y consultas</span>
                </div>

                <div class="contact-card">
                    <div class="contact-icon icon-location">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Ubicación</h3>
                    <p>Psje. Olaya N° 180, Trujillo, Perú</p>
                    <span class="contact-hint">IESTP Trujillo - Laboratorio Clínico</span>
                </div>

                <div class="contact-card">
                    <div class="contact-icon icon-clock">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Horario Académico</h3>
                    <p>Lunes a Viernes</p>
                    <span class="contact-hint">6:45 am - 08:35 pm</span>
                </div>
            </div>

            <!-- Redes Sociales y Ubicación -->
            <div class="contact-extra-grid">
                <div class="social-box-card">
                    <h3>Redes Sociales Institucionales</h3>
                    <p>Síguenos en nuestras redes oficiales para enterarte de novedades y comunicados.</p>
                    <div class="social-buttons-grid">
                        <a href="https://es-la.facebook.com/people/IESTP-Trujillo/100057443259181/" target="_blank" class="social-btn btn-fb">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://www.instagram.com/iestp_trujillo/" target="_blank" class="social-btn btn-ig">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                        <a href="https://www.tiktok.com/@iestp.trujillo" target="_blank" class="social-btn btn-tk">
                            <i class="fab fa-tiktok"></i> TikTok
                        </a>
                    </div>
                </div>

                <div class="map-card">
                    <div class="map-header">
                        <h4><i class="fas fa-map-pin"></i> Ubicación del Instituto en Google Maps</h4>
                    </div>
                    <div class="map-responsive">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.943226161809!2d-79.02394502587397!3d-8.107263681106112!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91ad3d87a50f62b9%3A0xe9c7097097b45f8c!2sInstituto%20Superior%20Tecnol%C3%B3gico%20P%C3%BAblico%20Trujillo!5e0!3m2!1ses!2spe!4v1755562401637!5m2!1ses!2spe" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Botón Flotante WhatsApp -->
    <div class="whatsapp-float">
        <a href="https://wa.me/51942879129" target="_blank" title="Chatea con nosotros por WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <footer class="footer">
        <div class="container footer-bottom-content" style="text-align:center; padding:25px 0; color:#94a3b8; font-size:0.9rem;">
            <p>&copy; <?php echo date('Y'); ?> Cepario Virtual - IESTP Trujillo. Todos los derechos reservados.</p>
            <p style="font-size:0.8rem; color:#64748b; margin-top:4px;">Desarrollado por Vasquez Miller, Cristian Sebastian & Aguilar Canchachi, Josbeth Esnayder | Contenido experimental por Docencia de Laboratorio Clínico</p>
        </div>
    </footer>

    <script src="JS/barradenavegacion.js"></script>
</body>
</html>