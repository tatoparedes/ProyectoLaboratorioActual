// ============================================================
// CONSULTA RENIEC EN VIVO Y AUTOCOMPLETADO AUTOMÁTICO
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const dniInput = document.getElementById('input-dni') || document.querySelector('input[name="dni"]');
    const nombresInput = document.getElementById('input-nombres') || document.querySelector('input[name="nombres"]');
    const apellidoPaternoInput = document.getElementById('input-paterno') || document.querySelector('input[name="apellido_paterno"]');
    const apellidoMaternoInput = document.getElementById('input-materno') || document.querySelector('input[name="apellido_materno"]');
    const statusText = document.getElementById('dni-status-msg');

    if (!dniInput) return;

    let ultimoDniConsultado = '';

    async function realizarConsultaDNI() {
        const dni = dniInput.value.trim();

        if (dni.length !== 8 || !/^\d{8}$/.test(dni)) {
            return;
        }

        if (dni === ultimoDniConsultado) {
            return;
        }

        ultimoDniConsultado = dni;

        if (statusText) {
            statusText.textContent = '🔄 Consultando datos de RENIEC...';
            statusText.style.color = '#0284c7';
        }

        try {
            const response = await fetch('registro.php?dni=' + encodeURIComponent(dni));
            const data = await response.json();

            // 1. Caso: El DNI ya está registrado en el sistema
            if (data.status === 'existe') {
                if (statusText) {
                    statusText.textContent = '⚠️ DNI ya registrado en el sistema.';
                    statusText.style.color = '#ef4444';
                }

                Swal.fire({
                    icon: 'info',
                    title: 'Usuario ya Registrado',
                    text: `El DNI ${dni} ya cuenta con una cuenta registrada a nombre de ${data.nombres || ''} ${data.apellidoPaterno || ''}.`,
                    showCancelButton: true,
                    confirmButtonText: 'Ir a Iniciar Sesión',
                    cancelButtonText: 'Permanecer aquí',
                    confirmButtonColor: '#0284c7'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
                return;
            }

            // 2. Caso: Consulta exitosa de RENIEC (Autocompletado automático)
            if (data.status === 'ok' && data.nombres) {
                if (statusText) {
                    statusText.textContent = '✓ Datos autocompletados correctamente';
                    statusText.style.color = '#16a34a';
                }

                if (nombresInput) nombresInput.value = data.nombres;
                if (apellidoPaternoInput) apellidoPaternoInput.value = data.apellidoPaterno;
                if (apellidoMaternoInput) apellidoMaternoInput.value = data.apellidoMaterno;

                Swal.mixin({ toast: true, position: 'top-end', timer: 2200, showConfirmButton: false }).fire({
                    icon: 'success',
                    title: `Identificado: ${data.nombres} ${data.apellidoPaterno}`
                });

                // Enfocar automáticamente el siguiente campo para máxima agilidad
                const correoInput = document.getElementById('input-correo');
                if (correoInput) correoInput.focus();
                return;
            }

            // 3. Caso: Modo manual (servicio no disponible o no encontrado)
            if (statusText) {
                statusText.textContent = '📝 Puedes ingresar tus nombres y apellidos manualmente.';
                statusText.style.color = '#0369a1';
            }

            if (nombresInput) nombresInput.removeAttribute('readonly');
            if (apellidoPaternoInput) apellidoPaternoInput.removeAttribute('readonly');
            if (apellidoMaternoInput) apellidoMaternoInput.removeAttribute('readonly');

        } catch (error) {
            console.error('Error en consulta DNI:', error);
            if (statusText) {
                statusText.textContent = '📝 Ingresa tus nombres y apellidos manualmente.';
                statusText.style.color = '#0369a1';
            }
        }
    }

    // Evento al escribir el DNI: dispara la consulta apenas se alcanzan los 8 dígitos
    dniInput.addEventListener('input', (e) => {
        const val = e.target.value.trim();
        if (val.length < 8) {
            if (statusText) statusText.textContent = '';
            ultimoDniConsultado = '';
        } else if (val.length === 8) {
            realizarConsultaDNI();
        }
    });

    // Soporte para pegar el DNI de 8 dígitos
    dniInput.addEventListener('paste', () => {
        setTimeout(() => {
            if (dniInput.value.trim().length === 8) {
                realizarConsultaDNI();
            }
        }, 50);
    });
});
