<?php
session_start();

$mensajeAlerta = null;

// Lógica de registro POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "conexion.php";

    $dni = trim($_POST["dni"] ?? "");
    $nombres = trim($_POST["nombres"] ?? "");
    $apellido_paterno = trim($_POST["apellido_paterno"] ?? "");
    $apellido_materno = trim($_POST["apellido_materno"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $raw_password = $_POST["password"] ?? "";
    $rol = 1; // Estudiante por defecto

    if (empty($dni) || empty($nombres) || empty($apellido_paterno) || empty($correo) || empty($raw_password)) {
        $mensajeAlerta = [
            "tipo" => "warning",
            "titulo" => "Campos Incompletos",
            "texto" => "Por favor completa todos los campos obligatorios."
        ];
    } elseif (!preg_match("/^[0-9]{8}$/", $dni)) {
        $mensajeAlerta = [
            "tipo" => "error",
            "titulo" => "DNI Inválido",
            "texto" => "El DNI debe contener exactamente 8 dígitos numéricos."
        ];
    } else {
        $password = password_hash($raw_password, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("CALL sp_registrar_usuario(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$dni, $nombres, $apellido_paterno, $apellido_materno, $correo, $password, $rol]);
            $stmt->closeCursor();

            $mensajeAlerta = [
                "tipo" => "success",
                "titulo" => "¡Registro Exitoso!",
                "texto" => "Tu cuenta de estudiante ha sido creada correctamente.",
                "redirect" => "login.php"
            ];
        } catch (PDOException $e) {
            $mensajeAlerta = [
                "tipo" => "error",
                "titulo" => "Error al Registrar",
                "texto" => "No se pudo registrar: " . $e->getMessage()
            ];
        }
    }
}

// ============================================================
// CONSULTA DNI EN VIVO CON DECOLECTA / RENIEC
// ============================================================
if (isset($_GET['dni'])) {
    header('Content-Type: application/json; charset=utf-8');

    // 👉 PEGA TU TOKEN DE DECOLECTA AQUÍ:
    $TOKEN_DECOLECTA = 'sk_9706.LHIbNFzmXWQZeB8XCmsX3cbxLjXJFiEO';

    $dni = trim($_GET['dni']);

    // Validar formato de 8 dígitos
    if (!preg_match('/^\d{8}$/', $dni)) {
        echo json_encode([
            'status' => 'error',
            'error' => 'El DNI debe contener exactamente 8 dígitos numéricos.'
        ]);
        exit;
    }

    // 1. Validar si el usuario ya existe en la base de datos local
    try {
        require_once "conexion.php";
        $stmtLocal = $conn->prepare("SELECT cNombres, cApePaterno, cApeMaterno FROM usuario WHERE cDNI = ? LIMIT 1");
        $stmtLocal->execute([$dni]);
        $usuarioLocal = $stmtLocal->fetch(PDO::FETCH_ASSOC);
        $stmtLocal->closeCursor();

        if ($usuarioLocal) {
            echo json_encode([
                'status' => 'existe',
                'error' => 'Este DNI ya está registrado en el sistema. Puedes iniciar sesión directamente.',
                'nombres' => $usuarioLocal['cNombres'],
                'apellidoPaterno' => $usuarioLocal['cApePaterno'],
                'apellidoMaterno' => $usuarioLocal['cApeMaterno']
            ]);
            exit;
        }
    } catch (Exception $e) {
        // Continuar si hay error de DB
    }

    // 2. Consulta a la API de Decolecta
    $token = trim($TOKEN_DECOLECTA);
    $url = 'https://api.decolecta.com/v1/reniec/dni?numero=' . $dni;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 6,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Referer: https://apis.net.pe/consulta-dni-api'
        ),
    ));

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        // Mapeo flexible de campos de Decolecta
        $nombres = $data['nombres'] ?? $data['first_name'] ?? $data['nombre'] ?? '';
        $apellidoPaterno = $data['apellidoPaterno'] ?? $data['apellido_paterno'] ?? $data['first_last_name'] ?? $data['paterno'] ?? '';
        $apellidoMaterno = $data['apellidoMaterno'] ?? $data['apellido_materno'] ?? $data['second_last_name'] ?? $data['materno'] ?? '';

        if (!empty($nombres)) {
            echo json_encode([
                'status' => 'ok',
                'nombres' => $nombres,
                'apellidoPaterno' => $apellidoPaterno,
                'apellidoMaterno' => $apellidoMaterno
            ]);
            exit;
        }
    }

    // Si el token es inválido, expiró o no devolvió datos
    $msgError = 'No se pudo autocompletar desde RENIEC. Puedes ingresar tus nombres y apellidos manualmente.';
    if ($httpCode === 401) {
        $msgError = 'Token de Decolecta requerido o expirado. Ingresa tus datos manualmente.';
    } elseif ($httpCode === 404 || $httpCode === 422) {
        $msgError = 'DNI no encontrado en RENIEC. Ingresa tus datos manualmente.';
    }

    echo json_encode([
        'status' => 'manual',
        'httpCode' => $httpCode,
        'message' => $msgError
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiante | Cepario Virtual - IESTP Trujillo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/registrarse.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-card">
            <!-- Columna Izquierda: Información -->
            <div class="register-info-side">
                <a href="index.php" class="brand-badge" title="Volver a Inicio">
                    <i class="fas fa-flask"></i>
                    <span>Cepario Virtual</span>
                </a>

                <div class="info-hero-text">
                    <h1>Crea tu Cuenta de Alumno</h1>
                    <p>Únete a la plataforma para acceder a las evaluaciones prácticas, consulta de bacterias y seguimiento de notas.</p>
                </div>

                <div class="info-highlights">
                    <div class="highlight-item">
                        <div class="highlight-icon"><i class="fas fa-id-card"></i></div>
                        <div>
                            <strong>Autocompletado RENIEC</strong>
                            <p>Ingresa tu DNI y tus nombres se llenarán automáticamente.</p>
                        </div>
                    </div>

                    <div class="highlight-item">
                        <div class="highlight-icon"><i class="fas fa-chart-line"></i></div>
                        <div>
                            <strong>Calificaciones en Tiempo Real</strong>
                            <p>Observaciones y notas directas de tu docente.</p>
                        </div>
                    </div>
                </div>

                <div class="info-footer">
                    <span>Cepario Virtual - IESTP Trujillo &copy; <?php echo date('Y'); ?></span>
                    <div class="social-icons">
                        <a href="https://es-la.facebook.com/people/IESTP-Trujillo/100057443259181/" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/iestp_trujillo/" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.tiktok.com/@iestp.trujillo" target="_blank"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Formulario -->
            <div class="register-form-side">
                <div class="form-title-wrap">
                    <h2>Registro de Estudiante</h2>
                    <p>Completa el formulario para registrarte en el sistema</p>
                </div>

                <form method="POST" action="registro.php" id="form-registro">
                    <div class="form-field-group">
                        <label for="input-dni">Número de DNI (8 dígitos)</label>
                        <div class="field-input-box">
                            <i class="fas fa-id-card field-icon"></i>
                            <input type="text" id="input-dni" name="dni" placeholder="Ej: 74589632" pattern="\d{8}" maxlength="8" required autofocus>
                        </div>
                        <span id="dni-status-msg" class="dni-status-hint"></span>
                    </div>

                    <div class="form-field-group">
                        <label for="input-nombres">Nombres Completos</label>
                        <div class="field-input-box">
                            <i class="fas fa-user field-icon"></i>
                            <input type="text" id="input-nombres" name="nombres" placeholder="Nombres" required>
                        </div>
                    </div>

                    <div class="form-row-2col">
                        <div class="form-field-group">
                            <label for="input-paterno">Apellido Paterno</label>
                            <div class="field-input-box">
                                <i class="fas fa-user field-icon"></i>
                                <input type="text" id="input-paterno" name="apellido_paterno" placeholder="Apellido paterno" required>
                            </div>
                        </div>

                        <div class="form-field-group">
                            <label for="input-materno">Apellido Materno</label>
                            <div class="field-input-box">
                                <i class="fas fa-user field-icon"></i>
                                <input type="text" id="input-materno" name="apellido_materno" placeholder="Apellido materno" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label for="input-correo">Correo Electrónico</label>
                        <div class="field-input-box">
                            <i class="fas fa-envelope field-icon"></i>
                            <input type="email" id="input-correo" name="correo" placeholder="ejemplo@correo.com" required>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label for="input-password">Contraseña (Mínimo 6 caracteres)</label>
                        <div class="field-input-box">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" id="input-password" name="password" placeholder="Tu contraseña segura" minlength="6" required>
                            <button type="button" class="btn-toggle-eye" id="toggle-password" title="Mostrar u ocultar contraseña">
                                <i class="fas fa-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-register">
                        <span>Crear mi Cuenta</span>
                        <i class="fas fa-user-plus"></i>
                    </button>

                    <div class="form-bottom-links">
                        <div class="login-prompt">
                            ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
                        </div>
                        <a href="index.php" class="link-return-home">
                            <i class="fas fa-arrow-left"></i> Volver a la página principal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="JS/registrarse.js"></script>
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
                timer: 1800,
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