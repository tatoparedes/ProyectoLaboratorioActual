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
    <title>Blog & Guías Académicas | Laboratorio Clínico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/blog.css">
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
                    <li class="nav-item"><a href="blog.php" class="nav-link active">Blog</a></li>
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

    <main class="blog-content">
        <div class="container">
            <div class="blog-header">
                <span class="section-tag">Biblioteca Académica</span>
                <h1>Guías y Protocolos de Diagnóstico</h1>
                <p>Descarga material de apoyo, diapositivas y protocolos bioquímicos para complementar tu formación en el laboratorio.</p>
            </div>
    
            <div class="blog-grid">
                <article class="blog-card">
                    <div class="blog-img-box">
                        <img src="imagenes/1.png" alt="Métodos de Identificación Bioquímica" loading="lazy">
                        <span class="blog-category">Presentación PPTX</span>
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-meta">
                            <span><i class="fas fa-file-powerpoint"></i> PPTX</span>
                            <span><i class="fas fa-book-open"></i> Microbiología</span>
                        </div>
                        <h3>Métodos de Identificación Bioquímica</h3>
                        <p>Fundamentos y procedimientos de las pruebas metabólicas empleadas en la identificación de patógenos clínicos.</p>
                        <a href="powerpoint/IdentificacionBioquimica.pptx" class="btn-download" download onclick="notificarDescarga('Identificación Bioquímica')">
                            <i class="fas fa-download"></i> Descargar Presentación
                        </a>
                    </div>
                </article>
    
                <article class="blog-card">
                    <div class="blog-img-box">
                        <img src="imagenes/2.jpg" alt="Pruebas Bioquímicas IMViC" loading="lazy">
                        <span class="blog-category">Guía Práctica</span>
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-meta">
                            <span><i class="fas fa-file-powerpoint"></i> PPTX</span>
                            <span><i class="fas fa-vial"></i> Batería IMViC</span>
                        </div>
                        <h3>Batería de Pruebas Bioquímicas</h3>
                        <p>Procedimiento paso a paso para la batería IMViC: Indol, Rojo de Metilo, Voges-Proskauer y Citrato de Simmons.</p>
                        <a href="powerpoint/PruebasBioquimicas.pptx" class="btn-download" download onclick="notificarDescarga('Pruebas Bioquímicas')">
                            <i class="fas fa-download"></i> Descargar Presentación
                        </a>
                    </div>
                </article>
    
                <article class="blog-card">
                    <div class="blog-img-box">
                        <img src="imagenes/3.png" alt="Método Lisina Hierro Agar LIA" loading="lazy">
                        <span class="blog-category">Protocolo Diagnóstico</span>
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-meta">
                            <span><i class="fas fa-file-powerpoint"></i> PPTX</span>
                            <span><i class="fas fa-microscope"></i> Agar LIA</span>
                        </div>
                        <h3>Método de Lisina Hierro Agar (LIA)</h3>
                        <p>Detección de la descarboxilación y desaminación de la lisina, con interpretación de cambios cromáticos y producción de gas/H2S.</p>
                        <a href="powerpoint/LIA.pptx" class="btn-download" download onclick="notificarDescarga('Lisina Hierro Agar LIA')">
                            <i class="fas fa-download"></i> Descargar Presentación
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container footer-bottom-content" style="text-align:center; padding:25px 0; color:#94a3b8; font-size:0.9rem;">
            <p>&copy; <?php echo date('Y'); ?> Cepario Virtual - IESTP Trujillo. Todos los derechos reservados.</p>
            <p style="font-size:0.8rem; color:#64748b; margin-top:4px;">Desarrollado por Vasquez Miller, Cristian Sebastian & Aguilar Canchachi, Josbeth Esnayder | Contenido experimental por Docencia de Laboratorio Clínico</p>
        </div>
    </footer>

    <script src="JS/barradenavegacion.js"></script>
    <script>
        function notificarDescarga(nombreGuia) {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            }).fire({
                icon: 'success',
                title: `Descargando: ${nombreGuia}`
            });
        }
    </script>
</body>
</html>