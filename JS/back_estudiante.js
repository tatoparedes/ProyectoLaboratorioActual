// ============================================================
// LÓGICA DE ESTUDIANTE: EXÁMENES Y RESULTADOS CON SWEETALERT2
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const sidebarBtns = document.querySelectorAll('.sidebar-btn');
    const panels = document.querySelectorAll('.content-panel');
    const formAccesoExamen = document.getElementById('form-acceso-examen');
    const panelExamenes = document.getElementById('panel-examenes');
    const panelExamenActivo = document.getElementById('panel-examen-activo');
    const contenedorPreguntas = document.getElementById("contenedor-preguntas");
    const btnEnviarExamen = document.getElementById("btn-enviar-examen");
    let examenActivo = null;

    // Panel de resultados del estudiante
    const formRevision = document.getElementById("form-acceso-revision");
    const contenedorResultados = document.getElementById("contenedor-resultados");
    const resumenNota = document.getElementById("resumen-nota");
    const contenedorRespuestas = document.getElementById("contenedor-respuestas-revisadas");

    // === Menú lateral ===
    sidebarBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            panels.forEach(panel => panel.classList.remove('active'));
            sidebarBtns.forEach(b => b.classList.remove('active'));

            const targetPanel = document.querySelector(btn.getAttribute('href'));
            if (targetPanel) targetPanel.classList.add('active');
            btn.classList.add('active');
        });
    });

    // === Acceso a examen ===
    if (formAccesoExamen) {
        formAccesoExamen.addEventListener("submit", async (e) => {
            e.preventDefault();
            const codigo = document.getElementById("codigoExamen").value.trim();

            if (!codigo) {
                Swal.fire({ icon: 'warning', title: 'Código requerido', text: 'Por favor ingresa el código del examen.' });
                return;
            }

            Swal.fire({
                title: 'Verificando examen...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const fd = new FormData();
            fd.append("accion", "verificarCodigo");
            fd.append("codigoExamen", codigo);

            try {
                const res = await fetch("controladores/examen_estudiante.php", {
                    method: "POST",
                    body: fd
                });
                const data = await res.json();
                Swal.close();

                if (data.status === "ok") {
                    examenActivo = data.data.nExamen;

                    document.getElementById("examen-titulo").innerText = data.data.cExamen;

                    panelExamenes.classList.remove("active");
                    panelExamenActivo.classList.add("active");

                    await obtenerPreguntas(examenActivo);

                    Swal.fire({
                        icon: 'info',
                        title: '¡Examen Iniciado!',
                        text: 'Lee con atención cada consigna y redacta tus respuestas.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'No se puede acceder', text: data.message });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.' });
            }
        });
    }

    // === Obtener preguntas desde la DB ===
    async function obtenerPreguntas(nExamen) {
        const fd = new FormData();
        fd.append("accion", "obtenerPreguntas");
        fd.append("nExamen", nExamen);

        try {
            const res = await fetch("controladores/examen_estudiante.php", {
                method: "POST",
                body: fd
            });
            const data = await res.json();

            if (data.status === "ok") {
                contenedorPreguntas.innerHTML = "";

                data.data.forEach((p, i) => {
                    const div = document.createElement("div");
                    div.className = "examen-pregunta-card";
                    div.innerHTML = `
                        <div class="pregunta-header">
                            <span class="num-pregunta-badge">Pregunta ${i + 1}</span>
                            <h4 class="pregunta-titulo">${p.cPregunta}</h4>
                        </div>
                        ${p.cFoto ? `<div class="pregunta-img-wrap"><img src="uploads/${p.cFoto}" alt="Imagen de prueba microbiológica" class="examen-img" loading="lazy"></div>` : ""}
                        <div class="respuesta-wrap">
                            <label class="respuesta-label">Tu Respuesta:</label>
                            <textarea data-idpregunta="${p.nPregunta}" rows="4" placeholder="Escribe aquí tu análisis o respuesta..." required></textarea>
                        </div>
                    `;
                    contenedorPreguntas.appendChild(div);
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las preguntas del examen.' });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al obtener las preguntas del examen.' });
        }
    }

    // === Enviar respuestas del examen ===
    if (btnEnviarExamen) {
        btnEnviarExamen.addEventListener("click", async () => {
            const respuestas = [];

            contenedorPreguntas.querySelectorAll("textarea").forEach(t => {
                const nPregunta = t.getAttribute("data-idpregunta");
                const cRespuesta = t.value.trim();
                respuestas.push({ nPregunta: parseInt(nPregunta), cRespuesta });
            });

            const sinRespuesta = respuestas.some(r => !r.cRespuesta);
            if (sinRespuesta) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Preguntas sin responder',
                    text: 'Debes completar todas las preguntas antes de enviar tu evaluación.'
                });
                return;
            }

            Swal.fire({
                title: '¿Confirmas el envío de tu examen?',
                text: 'Una vez enviado no podrás modificar tus respuestas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, enviar examen',
                cancelButtonText: 'Revisar respuestas'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Enviando evaluación...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const fd = new FormData();
                    fd.append("accion", "guardarRespuestas");
                    fd.append("nExamen", examenActivo);
                    fd.append("respuestas", JSON.stringify(respuestas));

                    try {
                        const res = await fetch("controladores/examen_estudiante.php", {
                            method: "POST",
                            body: fd
                        });
                        const data = await res.json();

                        if (data.status === "ok") {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Examen Enviado con Éxito!',
                                text: 'Tus respuestas han sido recibidas. Tu docente revisará y asignará la calificación.',
                                confirmButtonColor: '#0284c7'
                            });
                            panelExamenActivo.classList.remove("active");
                            panelExamenes.classList.add("active");
                            if (typeof cargarDashboardAlumno === 'function') cargarDashboardAlumno();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error al enviar', text: data.message });
                        }
                    } catch (err) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al enviar las respuestas.' });
                    }
                }
            });
        });
    }

    // === Acceder a resultados del estudiante ===
    if (formRevision) {
        formRevision.addEventListener("submit", async (e) => {
            e.preventDefault();
            const codigo = document.getElementById("codigoExamenRevision").value.trim();
            if (!codigo) {
                Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Ingrese el código de examen para consultar.' });
                return;
            }

            resumenNota.innerHTML = "";
            contenedorRespuestas.innerHTML = "";
            contenedorResultados.style.display = "none";

            Swal.fire({
                title: 'Buscando resultados...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const fd = new FormData();
                fd.append("accion", "verResultadosEstudiante");
                fd.append("codigoExamen", codigo);

                const res = await fetch("controladores/examen_estudiante.php", {
                    method: "POST",
                    body: fd
                });
                const data = await res.json();
                Swal.close();

                if (data.status === "ok") {
                    let notaDisplay = '';
                    if (data.nota === null || data.nota === '') {
                        notaDisplay = `<span class="badge-nota badge-pendiente" style="font-size:1.2rem; padding:8px 16px;">En revisión por el docente</span>`;
                    } else {
                        const numNota = parseFloat(data.nota);
                        const estiloBadge = numNota >= 14 ? 'badge-aprobado' : (numNota >= 11 ? 'badge-regular' : 'badge-desaprobado');
                        const textoNota = (numNota % 1 === 0) ? numNota.toString() : parseFloat(numNota.toFixed(2)).toString();
                        notaDisplay = `
                            <div style="font-size:3rem; font-weight:800; line-height:1; margin:10px 0; color:#1e293b;">
                                <span class="badge-nota ${estiloBadge}" style="font-size:2.2rem; padding:6px 20px;">${textoNota} / 20</span>
                            </div>
                        `;
                    }

                    resumenNota.innerHTML = `
                        <div style="text-align:center; padding:15px 0;">
                            <span style="color:#64748b; font-size:0.95rem; text-transform:uppercase; font-weight:700;">Calificación Obtenida</span>
                            ${notaDisplay}
                        </div>
                    `;

                    if (data.respuestas && data.respuestas.length > 0) {
                        let html = '<div class="lista-respuestas-revisadas">';
                        data.respuestas.forEach((r, i) => {
                            const tieneComentario = r.cComentario && r.cComentario.trim() !== '';
                            html += `
                                <div class="revision-pregunta-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:14px;">
                                    <div style="font-weight:700; color:#1e293b; margin-bottom:8px;">${i + 1}. ${r.cPregunta}</div>
                                    ${r.cFoto ? `<img src="uploads/${r.cFoto}" class="examen-img" style="max-height:120px; border-radius:8px; margin-bottom:8px; display:block;" alt="Muestra">` : ''}
                                    <div style="background:#f8fafc; border-left:3px solid #0284c7; padding:8px 12px; border-radius:4px; margin-bottom:8px;">
                                        <strong style="font-size:0.85rem; color:#0284c7;">Tu respuesta:</strong>
                                        <p style="margin:2px 0 0 0; color:#334155; font-size:0.95rem;">${r.cRespuesta}</p>
                                    </div>
                                    <div style="background:${tieneComentario ? '#f0fdf4' : '#f8fafc'}; border-left:3px solid ${tieneComentario ? '#16a34a' : '#94a3b8'}; padding:8px 12px; border-radius:4px;">
                                        <strong style="font-size:0.85rem; color:${tieneComentario ? '#16a34a' : '#64748b'};">Comentario del Docente:</strong>
                                        <p style="margin:2px 0 0 0; color:#334155; font-size:0.95rem;">${r.cComentario || '<em>Aún sin comentarios</em>'}</p>
                                    </div>
                                </div>
                            `;
                        });
                        html += "</div>";
                        contenedorRespuestas.innerHTML = html;
                    } else {
                        contenedorRespuestas.innerHTML = "<p style='color:#777; text-align:center;'>No hay respuestas registradas para este examen.</p>";
                    }

                    contenedorResultados.style.display = "block";
                    contenedorResultados.scrollIntoView({ behavior: 'smooth' });
                } else {
                    Swal.fire({ icon: 'error', title: 'No encontrado', text: data.message });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al obtener los resultados del examen.' });
            }
        });
    }
});