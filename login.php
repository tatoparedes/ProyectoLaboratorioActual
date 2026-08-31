<?php
session_start();

$mensajeAlerta = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "conexion.php";

    $dni = trim($_POST["dni"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($dni) || empty($password)) {
        $mensajeAlerta = [
            "tipo" => "warning",
            "titulo" => "Campos Requeridos",
            "texto" => "Por favor ingresa tu número de DNI y contraseña."
        ];
    } else {
        try {
            $stmt = $conn->prepare("CALL sp_login_usuario(:dni, :password)");
            $stmt->bindParam(':dni', $dni);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($usuario) {
                if (password_verify($password, $usuario["cContrasena"])) {
                    $_SESSION["usuario"] = [
                        "nUsuario" => $usuario["nUsuario"],
                        "cNombres" => $usuario["cNombres"],
                        "nRol"     => $usuario["nRol"],
                        "cDNI"     => $usuario["cDNI"],
                        "cCorreo"  => $usuario["cCorreo"]
                    ];

                    $mensajeAlerta = [
                        "tipo" => "success",
                        "titulo" => "¡Bienvenido/a!",
                        "texto" => "Accediendo al sistema de laboratorio...",
                        "redirect" => "muestras.php"
                    ];
                } else {
                    $mensajeAlerta = [
                        "tipo" => "error",
                        "titulo" => "Contraseña Incorrecta",
                        "texto" => "La contraseña ingresada no coincide. Verifica tus credenciales."
                    ];
                }
            } else {
                $mensajeAlerta = [
                    "tipo" => "error",
                    "titulo" => "Usuario no Encontrado",
                    "texto" => "No existe una cuenta registrada con el DNI ingresado."
                ];
            }
        } catch (PDOException $e) {
            $mensajeAlerta = [
                "tipo" => "error",
                "titulo" => "Error de Conexión",
                "texto" => "Error al iniciar sesión: " . $e->getMessage()
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Cepario Virtual - IESTP Trujillo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <!-- Columna Izquierda: Información Institucional -->
            <div class="login-info-side">
                <a href="index.php" class="brand-badge" title="Volver a Inicio">
                    <i class="fas fa-flask"></i>
                    <span>Cepario Virtual</span>
                </a>

                <div class="info-hero-text">
                    <h1>Cepario Virtual & Diagnóstico</h1>
                    <p>Accede a tus evaluaciones, consulta el banco de cepas bacterianas y revisa tus calificaciones en tiempo real.</p>
                </div>

                <div class="info-highlights">
                    <div class="highlight-item">
                        <div class="highlight-icon"><i class="fas fa-microscope"></i></div>
                        <div>
                            <strong>Banco de Cepas Taxonómico</strong>
                            <p>Familias, géneros y especies con pruebas bioquímicas.</p>
                        </div>
                    </div>

                    <div class="highlight-item">
                        <div class="highlight-icon"><i class="fas fa-clipboard-check"></i></div>
                        <div>
                            <strong>Evaluaciones Prácticas</strong>
                            <p>Exámenes visuales con fotografías y retroalimentación docente.</p>
                        </div>
                    </div>
                </div>

                <div class="info-footer">
                    <span>Cepario Virtual - IESTP Trujillo &copy; <?php echo date('Y'); ?></span>
                    <div class="social-icons">
                        <a href="https://es-la.facebook.com/people/IESTP-Trujillo/100057443259181/" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/iestp_trujillo/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.tiktok.com/@iestp.trujillo" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Formulario Limpio y Espacioso -->
            <div class="login-form-side">
                <div class="form-title-wrap">
                    <h2>Iniciar Sesión</h2>
                    <p>Ingresa tus datos para acceder a tu cuenta institucional</p>
                </div>

                <form method="POST" action="login.php" id="form-login">
                    <div class="form-field-group">
                        <label for="input-dni">Número de DNI</label>
                        <div class="field-input-box">
                            <i class="fas fa-id-card field-icon"></i>
                            <input type="text" id="input-dni" name="dni" placeholder="Ingresa tu DNI (8 dígitos)" pattern="\d{8}" maxlength="8" required autofocus>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <div class="field-label-row">
                            <label for="input-password">Contraseña</label>
                        </div>
                        <div class="field-input-box">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" id="input-password" name="password" placeholder="Ingresa tu contraseña" required>
                            <button type="button" class="btn-toggle-eye" id="toggle-password" title="Mostrar u ocultar contraseña">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-login">
                        <span>Ingresar al Sistema</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="form-bottom-links">
                        <div class="register-prompt">
                            ¿Aún no estás registrado? <a href="registro.php">Crea tu cuenta de estudiante</a>
                        </div>
                        <a href="index.php" class="link-return-home">
                            <i class="fas fa-arrow-left"></i> Volver a la página principal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle ver contraseña
        const toggleBtn = document.getElementById('toggle-password');
        const passInput = document.getElementById('input-password');
        const eyeIcon = document.getElementById('eye-icon');

        if (toggleBtn && passInput && eyeIcon) {
            toggleBtn.addEventListener('click', () => {
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    passInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        }

        // SweetAlert2 Feedback
        <?php if ($mensajeAlerta): ?>
            Swal.fire({
                icon: '<?php echo $mensajeAlerta["tipo"]; ?>',
                title: '<?php echo $mensajeAlerta["titulo"]; ?>',
                text: '<?php echo $mensajeAlerta["texto"]; ?>',
                <?php if (isset($mensajeAlerta["redirect"])): ?>
                timer: 1500,
                showConfirmButton: false,
                willClose: () => {
                    window.location.href = '<?php echo $mensajeAlerta["redirect"]; ?>';
                }
                <?php else: ?>
                confirmButtonColor: '#0284c7'
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>