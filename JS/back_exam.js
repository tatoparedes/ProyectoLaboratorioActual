// ============================================================
// GESTIÓN DE EXÁMENES Y VENTANAS FLOTANTES DE REVISIÓN MODERNAS
// ============================================================

let preguntasTmp = []; // Array temporal de preguntas
let pruebasDisponibles = []; // Para almacenar info de pruebas con foto

// Renderizar preguntas en la lista temporal
function renderPreguntasTmp() {
    const lista = document.getElementById("listaPreguntas");
    if (!lista) return;
    lista.innerHTML = "";

    if (preguntasTmp.length === 0) {
        lista.innerHTML = `<p style="color:#64748b; text-align:center; font-size:0.9rem; padding:20px;">No hay preguntas agregadas aún.</p>`;
    }

    preguntasTmp.forEach((p, i) => {
        const div = document.createElement("div");
        div.className = "pregunta-item";
        div.innerHTML = `
            <div style="flex:1;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <span class="num-pregunta-badge">#${i + 1}</span>
                    <strong style="color:#1e293b;">${p.descripcion}</strong>
                </div>
                ${p.nombrePrueba ? `<small style="color:#0284c7; font-weight:700; display:block; margin:2px 0;">🔬 Bacteria: ${p.nombrePrueba}</small>` : ""}
                ${p.foto ? `<img src="${p.foto}" class="preview-img" style="max-width:90px; max-height:65px; border-radius:6px; margin-top:4px; border:1px solid #cbd5e0; object-fit:cover;">` : ""}
            </div>
            <button class="btn-card-delete" onclick="eliminarPreguntaTmp(${i})" title="Eliminar pregunta" style="padding:6px 10px; border-radius:6px; flex:none;">✕</button>
        `;
        lista.appendChild(div);
    });
    const countEl = document.getElementById("count");
    if (countEl) countEl.innerText = preguntasTmp.length;
}

// Eliminar pregunta del array temporal
function eliminarPreguntaTmp(index) {
    preguntasTmp.splice(index, 1);
    renderPreguntasTmp();
}

// --- Agregar pregunta temporal ---
const btnAgregarPregunta = document.getElementById("agregarBtn");
if (btnAgregarPregunta) {
    btnAgregarPregunta.addEventListener("click", () => {
        const descripcion = document.getElementById("descripcion").value.trim();
        const selectPrueba = document.getElementById("pruebaSelect");
        const nPrueba = selectPrueba.value || null;
        const nombrePrueba = selectPrueba.options[selectPrueba.selectedIndex]?.text || "";

        if (descripcion === "") {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Debe ingresar la consigna / descripción de la pregunta.' });
            return;
        }

        let foto = "";
        if (nPrueba && pruebasDisponibles.length > 0) {
            const prueba = pruebasDisponibles.find(p => p.nPrueba == nPrueba);
            if (prueba && prueba.cFoto) {
                foto = "uploads/" + prueba.cFoto;
            }
        }

        preguntasTmp.push({
            descripcion: descripcion,
            nPrueba: nPrueba,
            nombrePrueba: nombrePrueba !== "-- Seleccione prueba --" ? nombrePrueba : null,
            foto: foto
        });

        document.getElementById("descripcion").value = "";
        renderPreguntasTmp();
        Swal.mixin({ toast: true, position: 'top-end', timer: 1500, showConfirmButton: false }).fire({
            icon: 'success',
            title: `Pregunta #${preguntasTmp.length} agregada`
        });
    });
}

// --- Limpiar campos del creador ---
const btnLimpiarCampos = document.getElementById("limpiarBtn");
if (btnLimpiarCampos) {
    btnLimpiarCampos.addEventListener("click", () => {
        document.getElementById("descripcion").value = "";
        const selectPrueba = document.getElementById("pruebaSelect");
        if (selectPrueba) selectPrueba.selectedIndex = 0;
        const preview = document.getElementById("preview");
        if (preview) { preview.style.display = "none"; preview.src = ""; }
    });
}

// --- Limpiar preguntas temporales ---
const btnClearAll = document.getElementById("clearAll");
if (btnClearAll) {
    btnClearAll.addEventListener("click", () => {
        if (preguntasTmp.length === 0) return;
        Swal.fire({
            title: '¿Limpiar preguntas?',
            text: 'Se eliminarán las preguntas que estabas armando en este examen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                preguntasTmp = [];
                renderPreguntasTmp();
            }
        });
    });
}

// --- Guardar examen completo ---
const btnGuardarExamen = document.getElementById("guardarExamen");
if (btnGuardarExamen) {
    btnGuardarExamen.addEventListener("click", async () => {
        if (preguntasTmp.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Examen vacío', text: 'Debe agregar al menos una pregunta antes de guardar.' });
            return;
        }

        const { value: cExamen } = await Swal.fire({
            title: 'Guardar Nuevo Examen',
            input: 'text',
            inputLabel: 'Nombre o Título del Examen:',
            inputPlaceholder: 'Ej: Evaluación Práctica de Enterobacterias',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Guardar y Publicar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return '¡Debe ingresar un nombre para el examen!';
                }
            }
        });

        if (!cExamen) return;

        Swal.fire({
            title: 'Guardando examen...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append("accion", "agregar");
        formData.append("cExamen", cExamen.trim());
        formData.append("preguntas", JSON.stringify(preguntasTmp));

        try {
            const res = await fetch("controladores/examen.php", { method: "POST", body: formData });
            const data = await res.json();
            if (data.status === "ok") {
                Swal.fire({
                    icon: 'success',
                    title: '¡Examen Guardado con Éxito!',
                    html: `
                        <p style="color:#475568; margin-bottom:12px;">Tu examen ha sido creado y ya está disponible para los alumnos.</p>
                        <div style="background:#eff6ff; border:2px dashed #3b82f6; border-radius:10px; padding:15px; margin:10px 0;">
                            <span style="font-size:0.9rem; color:#1e40af; font-weight:600; display:block;">Código de Acceso:</span>
                            <span style="font-size:1.8rem; font-weight:800; color:#1d4ed8; letter-spacing:2px; font-family:monospace;">${data.codigo}</span>
                        </div>
                        <p style="font-size:0.85rem; color:#64748b;">Comparte este código con tus estudiantes para que puedan resolverlo.</p>
                    `,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#0284c7'
                });
                preguntasTmp = [];
                renderPreguntasTmp();
                listarExamenes();
                if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
            } else {
                Swal.fire({ icon: 'error', title: 'Error al guardar', text: data.message || 'Error al guardar examen' });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión al guardar el examen.' });
        }
    });
}

// ==================== LISTAR EXÁMENES GUARDADOS ====================
async function listarExamenes() {
    const tabla = document.getElementById("examenes-guardados-body");
    if (!tabla) return;
    tabla.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:15px; color:#888;">Cargando lista de exámenes...</td></tr>';

    try {
        const res = await fetch("controladores/examen.php", {
            method: "POST",
            body: new URLSearchParams({ accion: "listar" })
        });
        const data = await res.json();

        if (data.status === "ok") {
            tabla.innerHTML = "";
            if (data.data.length === 0) {
                tabla.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:15px; color:#777;">No hay exámenes activos registrados.</td></tr>';
                return;
            }

            data.data.forEach((examen) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td><strong>${examen.cExamen}</strong></td>
                    <td>
                        <span class="code-badge">${examen.cCodigoExamen}</span>
                        <button class="btn-copy-mini" title="Copiar código" onclick="copiarAlPortapapeles('${examen.cCodigoExamen}', this)">📋</button>
                    </td>
                    <td><span style="font-weight:700; color:#0284c7;">${examen.totalPreguntas}</span> preguntas</td>
                    <td class="table-actions">
                        <button class="btn-action btn-ver-table" onclick="verPreguntasExamen(${examen.nExamen})">👁️ Ver</button>
                        <button class="btn-action btn-edit-table" onclick="editarExamen(${examen.nExamen}, '${examen.cExamen.replace(/'/g, "\\'")}')">✏️ Editar</button>
                        <button class="btn-action btn-delete-table" onclick="eliminarExamen(${examen.nExamen})">🗑️ Eliminar</button>
                    </td>
                `;
                tabla.appendChild(tr);
            });
        }
    } catch (err) {
        console.error("Error al listar exámenes:", err);
        tabla.innerHTML = '<tr><td colspan="4" style="text-align:center; color:red; padding:15px;">Error al cargar exámenes.</td></tr>';
    }
}

// ==================== VENTANA FLOTANTE: VER PREGUNTAS DEL EXAMEN ====================
async function verPreguntasExamen(nExamen) {
    try {
        Swal.fire({
            title: 'Cargando preguntas...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const res = await fetch("controladores/examen.php", {
            method: "POST",
            body: new URLSearchParams({ accion: "verPreguntas", nExamen })
        });
        const data = await res.json();
        Swal.close();

        if (data.status === "ok" && data.data.length > 0) {
            let html = `
                <div style="text-align:left; max-height:65vh; overflow-y:auto; padding-right:6px;">
                    <p style="color:#64748b; font-size:0.9rem; margin-bottom:16px;">Total de preguntas en esta evaluación: <strong>${data.data.length}</strong></p>
            `;

            data.data.forEach((p, i) => {
                html += `
                    <div style="padding:16px; margin-bottom:14px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; border-left:4px solid #0284c7;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                            <span style="background:#0284c7; color:#fff; padding:3px 8px; border-radius:6px; font-weight:800; font-size:0.8rem;">#${i + 1}</span>
                            <strong style="color:#0f172a; font-size:1rem;">${p.cPregunta}</strong>
                        </div>
                        ${p.cFoto ? `
                            <div style="margin-top:10px;">
                                <img src="uploads/${p.cFoto}" style="max-height:120px; border-radius:8px; border:1px solid #cbd5e0; box-shadow:0 2px 6px rgba(0,0,0,0.05);" alt="Foto de cultivo">
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            html += '</div>';

            Swal.fire({
                title: '<span style="color:#0f172a; font-weight:800;">📋 Preguntas del Examen</span>',
                html: html,
                width: '680px',
                confirmButtonColor: '#0284c7',
                confirmButtonText: 'Cerrar Ventana',
                customClass: {
                    popup: 'swal-modal-custom'
                }
            });
        } else {
            Swal.fire({ icon: 'info', title: 'Sin preguntas', text: 'No hay preguntas registradas para este examen.' });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar preguntas del examen: ' + err });
    }
}

// ==================== VENTANA FLOTANTE: EDITAR EXAMEN ====================
async function editarExamen(nExamen, nombreActual) {
    const { value: nuevoNombre } = await Swal.fire({
        title: '✏️ Editar Título del Examen',
        input: 'text',
        inputValue: nombreActual,
        inputLabel: 'Nombre del examen:',
        showCancelButton: true,
        confirmButtonColor: '#0284c7',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Guardar Cambios',
        cancelButtonText: 'Cancelar',
        inputValidator: (val) => {
            if (!val || val.trim() === '') return 'El título no puede estar vacío';
        }
    });

    if (!nuevoNombre || nuevoNombre.trim() === '') return;

    const formData = new FormData();
    formData.append("accion", "editar");
    formData.append("nExamen", nExamen);
    formData.append("cExamen", nuevoNombre.trim());

    try {
        const res = await fetch("controladores/examen.php", { method: "POST", body: formData });
        const data = await res.json();
        if (data.status === "ok") {
            Swal.fire({ icon: 'success', title: '¡Actualizado!', text: data.message, timer: 1800, showConfirmButton: false });
            listarExamenes();
            if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error al actualizar examen: ' + err });
    }
}

// --- Eliminar examen ---
async function eliminarExamen(nExamen) {
    const confirmacion = await Swal.fire({
        title: '¿Desactivar este examen?',
        text: 'Los alumnos ya no podrán rendirlo con su código de acceso.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmacion.isConfirmed) return;

    try {
        const res = await fetch("controladores/examen.php", {
            method: "POST",
            body: new URLSearchParams({ accion: "eliminar", nExamen })
        });
        const data = await res.json();
        if (data.status === "ok") {
            Swal.fire({ icon: 'success', title: '¡Eliminado!', text: data.message, timer: 1800, showConfirmButton: false });
            listarExamenes();
            if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error al eliminar examen: ' + err });
    }
}

// ==================== BÚSQUEDA Y VENTANA FLOTANTE DE REVISIÓN ====================
const formBuscar = document.getElementById("form-buscar-examen");
const nombreExamenSpan = document.getElementById("nombre-examen-resultado");

if (formBuscar) {
    formBuscar.addEventListener("submit", async (e) => {
        e.preventDefault();
        const codigo = document.getElementById("codigoExamen").value.trim();
        if (!codigo) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Ingrese un código de examen.' });
            return;
        }

        const tablaResultados = document.querySelector("#table-resultados-examen tbody");

        Swal.fire({
            title: 'Buscando evaluaciones...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await fetch("controladores/examen.php", {
                method: "POST",
                body: new URLSearchParams({ accion: "buscarResultados", codigoExamen: codigo })
            });
            const data = await res.json();
            Swal.close();

            if (data.status === "ok") {
                if (nombreExamenSpan) nombreExamenSpan.textContent = `"${data.examen.cExamen}" (Código: ${codigo})`;
                if (tablaResultados) {
                    tablaResultados.innerHTML = "";

                    if (!data.resultados || data.resultados.length === 0) {
                        tablaResultados.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:25px; color:#64748b;">Ningún estudiante ha resuelto este examen aún.</td></tr>`;
                        return;
                    }

                    data.resultados.forEach((item, index) => {
                        const nombreCompleto = `${item.cApePaterno || ''} ${item.cApeMaterno || ''}, ${item.cNombres || ''}`.trim();
                        const notaRaw = item.cCalificacion ?? '';
                        const notaLimpia = (notaRaw !== '' && !isNaN(parseFloat(notaRaw)))
                            ? ((parseFloat(notaRaw) % 1 === 0) ? parseInt(notaRaw, 10).toString() : parseFloat(parseFloat(notaRaw).toFixed(2)).toString())
                            : notaRaw;

                        const tr = document.createElement("tr");
                        tr.dataset.nombre = nombreCompleto;
                        tr.innerHTML = `
                            <td><strong>${index + 1}</strong></td>
                            <td><strong>${nombreCompleto}</strong></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <input type="number" step="0.1" min="0" max="20"
                                        value="${notaLimpia}" 
                                        data-id="${item.nCalificacion}" class="input-nota" style="width:75px; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e0; font-weight:700;"/>
                                    <button type="button" class="btn-guardar-nota" data-id="${item.nCalificacion}" title="Guardar Nota">💾</button>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn-ver-respuestas" data-id="${item.nCalificacion}"
                                        data-nombre="${nombreCompleto}"
                                        data-calificacion="${notaLimpia}">
                                    📝 Revisar y Calificar
                                </button>
                            </td>
                        `;
                        tablaResultados.appendChild(tr);
                    });
                }
            } else {
                Swal.fire({ icon: 'error', title: 'No encontrado', text: data.message });
                if (nombreExamenSpan) nombreExamenSpan.textContent = "";
                if (tablaResultados) tablaResultados.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:20px; color:#ef4444;">No se encontró ningún examen con este código.</td></tr>`;
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la búsqueda de resultados: ' + err });
        }
    });
}

// ==================== DELEGACIÓN GLOBAL PARA REVISIÓN Y GUARDADO DE NOTAS ====================
document.addEventListener("click", async (e) => {
    const targetBtn = e.target.closest('button');
    if (!targetBtn) return;
    const id = targetBtn.dataset.id;

    // 1. Abrir Modal Flotante de Revisión y Calificación
    if (targetBtn.classList.contains("btn-ver-respuestas") && id) {
        const nCalificacion = id;
        const nombreAlumno = targetBtn.dataset.nombre || 'Estudiante';
        const calificacionActual = targetBtn.dataset.calificacion || '';
        const calificacionFormateada = (calificacionActual !== '' && !isNaN(parseFloat(calificacionActual)))
            ? ((parseFloat(calificacionActual) % 1 === 0) ? parseInt(calificacionActual, 10).toString() : parseFloat(parseFloat(calificacionActual).toFixed(2)).toString())
            : calificacionActual;

        try {
            Swal.fire({
                title: 'Cargando respuestas del alumno...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const res = await fetch("controladores/examen.php", {
                method: "POST",
                body: new URLSearchParams({ accion: "verRespuestas", nCalificacion })
            });
            const data = await res.json();
            Swal.close();

            if (data.status === "ok") {
                let modalHtml = `
                    <div style="text-align:left; max-height:65vh; overflow-y:auto; padding-right:8px;">
                        <!-- Header del Alumno con Nota Directa -->
                        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:16px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                            <div>
                                <span style="font-size:0.8rem; color:#1e40af; font-weight:700; text-transform:uppercase;">Alumno Evaluado</span>
                                <h4 style="margin:2px 0 0 0; color:#1e293b; font-size:1.15rem;">👤 ${nombreAlumno}</h4>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <label style="font-weight:700; color:#1e3a8a; font-size:0.9rem;">Calificación (0-20):</label>
                                <input type="number" id="modal-calificacion-input" step="0.1" min="0" max="20" value="${calificacionFormateada}" style="width:80px; padding:8px 10px; font-weight:800; font-size:1.05rem; border-radius:8px; border:2px solid #3b82f6; text-align:center;">
                            </div>
                        </div>
                `;

                if (!data.respuestas || data.respuestas.length === 0) {
                    modalHtml += `<p style="color:#64748b; text-align:center; padding:20px;">No se encontraron respuestas registradas para este estudiante.</p>`;
                } else {
                    data.respuestas.forEach((r, i) => {
                        modalHtml += `
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:16px;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                    <span style="background:#0284c7; color:#ffffff; padding:2px 8px; border-radius:6px; font-weight:800; font-size:0.8rem;">Pregunta ${i + 1}</span>
                                    <strong style="color:#0f172a; font-size:0.95rem;">${r.cPregunta}</strong>
                                </div>
                                
                                ${r.cFoto ? `
                                    <div style="margin:8px 0;">
                                        <img src="uploads/${r.cFoto}" style="max-height:110px; border-radius:8px; border:1px solid #cbd5e0;" alt="Foto de cultivo">
                                    </div>
                                ` : ''}

                                <div style="background:#ffffff; border-radius:8px; padding:10px 14px; margin:10px 0; border:1px solid #e2e8f0;">
                                    <strong style="color:#0284c7; font-size:0.85rem; text-transform:uppercase; display:block; margin-bottom:4px;">Respuesta del Estudiante:</strong>
                                    <p style="margin:0; color:#334155; font-size:0.94rem; line-height:1.4;">${r.cRespuesta ? r.cRespuesta : '<em>(Sin respuesta)</em>'}</p>
                                </div>

                                <div style="margin-top:10px;">
                                    <label style="font-size:0.85rem; font-weight:700; color:#475568; display:block; margin-bottom:4px;">
                                        💬 Observación / Corrección del Docente:
                                    </label>
                                    <textarea class="swal2-textarea-comentario" data-idres="${r.nRespuesta}" rows="2" style="width:100%; border-radius:8px; border:1.5px solid #cbd5e0; padding:10px; font-size:0.9rem; outline:none; box-sizing:border-box; background:#ffffff;" placeholder="Escribe observaciones o retroalimentación...">${r.cComentario ?? ''}</textarea>
                                </div>
                            </div>
                        `;
                    });
                }

                modalHtml += `</div>`;

                Swal.fire({
                    title: '<span style="color:#0f172a; font-weight:800;">📝 Revisión y Calificación</span>',
                    html: modalHtml,
                    width: '740px',
                    showCancelButton: true,
                    confirmButtonText: '💾 Guardar Nota y Comentarios',
                    cancelButtonText: '✕ Cerrar',
                    confirmButtonColor: '#0284c7',
                    cancelButtonColor: '#64748b',
                    preConfirm: async () => {
                        const notaInput = document.getElementById("modal-calificacion-input");
                        const nuevaNota = notaInput ? notaInput.value.trim() : '';

                        // 1. Validar y guardar nota si se ingresó
                        if (nuevaNota !== '') {
                            const notaNum = parseFloat(nuevaNota);
                            if (isNaN(notaNum) || notaNum < 0 || notaNum > 20) {
                                Swal.showValidationMessage('La calificación debe ser un número entre 0 y 20.');
                                return false;
                            }

                            try {
                                const resNota = await fetch("controladores/examen.php", {
                                    method: "POST",
                                    body: new URLSearchParams({ accion: "guardarCalificacion", nCalificacion, calificacion: nuevaNota })
                                });
                                const dataNota = await resNota.json();
                                if (dataNota.status !== "ok") {
                                    Swal.showValidationMessage(`Error al guardar nota: ${dataNota.message}`);
                                    return false;
                                }
                            } catch (err) {
                                Swal.showValidationMessage(`Error de conexión al guardar nota: ${err}`);
                                return false;
                            }
                        }

                        // 2. Guardar comentarios de cada pregunta
                        const textareas = document.querySelectorAll(".swal2-textarea-comentario");
                        const comentarios = Array.from(textareas).map(t => ({
                            nRespuesta: t.dataset.idres,
                            comentario: t.value.trim()
                        }));

                        if (comentarios.length > 0) {
                            try {
                                const resGuardar = await fetch("controladores/examen.php", {
                                    method: "POST",
                                    body: new URLSearchParams({ accion: "guardarComentarios", comentarios: JSON.stringify(comentarios) })
                                });
                                const dataGuardar = await resGuardar.json();
                                if (dataGuardar.status !== "ok") {
                                    Swal.showValidationMessage(`Error al guardar comentarios: ${dataGuardar.message}`);
                                    return false;
                                }
                            } catch (err) {
                                Swal.showValidationMessage(`Error al guardar comentarios: ${err}`);
                                return false;
                            }
                        }

                        return { nuevaNota };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({ icon: 'success', title: '¡Guardado Exitoso!', text: 'La calificación y los comentarios se registraron correctamente.', timer: 1800, showConfirmButton: false });
                        const notaGuardada = result.value?.nuevaNota;
                        const inputNotaFila = document.querySelector(`.input-nota[data-id="${nCalificacion}"]`);
                        if (inputNotaFila && notaGuardada !== undefined && notaGuardada !== '') {
                            inputNotaFila.value = notaGuardada;
                        }
                        targetBtn.dataset.calificacion = notaGuardada || '';
                        if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
                    }
                });

            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al obtener respuestas: ' + err });
        }
    }

    // 2. Guardar Calificación Rápida en la Fila
    if (targetBtn.classList.contains("btn-guardar-nota") && id) {
        const input = document.querySelector(`.input-nota[data-id="${id}"]`);
        const nota = input ? input.value.trim() : '';
        if (nota === '' || isNaN(nota) || parseFloat(nota) < 0 || parseFloat(nota) > 20) {
            Swal.fire({ icon: 'warning', title: 'Nota inválida', text: 'Ingrese una calificación válida entre 0 y 20.' });
            return;
        }

        try {
            const res = await fetch("controladores/examen.php", {
                method: "POST",
                body: new URLSearchParams({ accion: "guardarCalificacion", nCalificacion: id, calificacion: nota })
            });
            const data = await res.json();
            if (data.status === "ok") {
                Swal.mixin({ toast: true, position: 'top-end', timer: 2000, showConfirmButton: false }).fire({
                    icon: 'success',
                    title: `Calificación ${nota} guardada 💾`
                });
                const btnVer = document.querySelector(`.btn-ver-respuestas[data-id="${id}"]`);
                if (btnVer) btnVer.dataset.calificacion = nota;
                if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar la calificación: ' + err });
        }
    }
});

document.addEventListener("DOMContentLoaded", () => {
    listarExamenes();
});