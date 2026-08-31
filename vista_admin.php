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
if ($usuarioId <= 0 || $usuarioRol !== 3) { 
    die("Acceso no autorizado: solo administradores pueden acceder a esta sección."); 
} 

$usuarioNombre = $_SESSION["usuario"]["cNombres"] ?? "Administrador"; 
?>

<!DOCTYPE html> 
<html lang="es"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Panel de Administración de Usuarios | Laboratorio Clínico</title> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
    <link rel="stylesheet" href="css/admin.css">
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
                    <li class="nav-item"><a href="muestras.php" class="nav-link active">Administración</a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contactanos.php" class="nav-link">Contáctanos</a></li>
                </ul>
                <div class="header-user-actions">
                    <span class="user-pill-tag"><i class="fas fa-user-shield"></i> Admin: <?php echo htmlspecialchars($usuarioNombre); ?></span>
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

    <main class="container admin-container">
        <div class="admin-panel-card">
            <div class="admin-header-flex">
                <div>
                    <h2><i class="fas fa-users-cog"></i> Administración General de Usuarios</h2>
                    <p>Gestión de cuentas registradas, asignación de roles (Estudiante, Docente, Administrador) y control de accesos.</p>
                </div>
            </div>

            <div class="table-responsive-wrapper" style="margin-top:25px;"> 
                <table class="user-table" id="tabla-usuarios-admin"> 
                    <thead> 
                        <tr> 
                            <th>N°</th> 
                            <th>DNI</th> 
                            <th>Apellido Paterno</th> 
                            <th>Apellido Materno</th> 
                            <th>Nombres</th> 
                            <th>Correo Electrónico</th> 
                            <th>Rol en el Sistema</th> 
                            <th>Acciones</th> 
                        </tr> 
                    </thead> 
                    <tbody> 
                        <tr><td colspan="8" style="text-align:center; padding:20px; color:#64748b;">Cargando lista de usuarios...</td></tr>
                    </tbody> 
                </table> 
            </div> 
        </div>
    </main>

    <footer class="footer">
        <div class="container footer-bottom-content" style="text-align:center; padding:25px 0; color:#94a3b8; font-size:0.9rem;">
            <p>&copy; <?php echo date('Y'); ?> Cepario Virtual - IESTP Trujillo. Todos los derechos reservados.</p>
            <p style="font-size:0.8rem; color:#64748b; margin-top:4px;">Desarrollado por Vasquez Miller, Cristian Sebastian & Aguilar Canchachi, Josbeth Esnayder | Contenido experimental por Docencia de Laboratorio Clínico</p>
        </div>
    </footer>

    <script src="JS/back_admin.js"></script> 
    <script src="JS/barradenavegacion.js"></script>
</body> 
</html>