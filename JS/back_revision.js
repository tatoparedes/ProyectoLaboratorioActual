document.addEventListener('DOMContentLoaded', () => {
    const formRevision = document.getElementById("form-acceso-revision");
    const contenedorResultados = document.getElementById("contenedor-resultados");
    const resumenNota = document.getElementById("resumen-nota");
    const contenedorRespuestas = document.getElementById("contenedor-respuestas-revisadas");

    if (!formRevision) return;

    formRevision.addEventListener("submit", async (e) => {
        e.preventDefault();
        const codigo = document.getElementById("codigoExamenRevision").value.trim();
        if (!codigo) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Ingrese el código de examen.' });
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
            fd.append("accion", "verResultados");
            fd.append("codigoExamen", codigo);

            const res = await fetch("controladores/resultados_estudiante.php", {
                method: "POST",
                body: fd
            });
            const data = await res.json();
            Swal.close();

            if (data.status === "ok") {
                // Mostrar nota
                resumenNota.innerHTML = `<h4>Tu calificación: <span style="color:#0284c7; font-weight:800;">${data.nota ?? 'Pendiente'}</span></h4>`;

                // Mostrar respuestas
                if (data.respuestas && data.respuestas.length) {
                    let html = '<ul style="list-style:none;padding:0;">';
                    data.respuestas.forEach((r, i) => {
                        const color = r.cComentario && r.cComentario.toLowerCase().includes("correcta") ? '#16a34a' : '#dc2626';
                        html += `
                            <li style="border-bottom:1px solid #e2e8f0; padding:12px 0;">
                                <strong style="color:#1e293b;">${i+1}. ${r.cPregunta}</strong><br>
                                <span style="color:#475568;">Tu respuesta:</span> <strong>${r.cRespuesta}</strong><br>
                                <span style="color:#475568;">Comentario:</span> <span style="color:${color}; font-weight:600;">${r.cComentario ?? "Sin revisión"}</span>
                                ${r.cFoto ? `<br><img src="uploads/${r.cFoto}" class="examen-img" style="max-height:100px; border-radius:6px; margin-top:6px;" alt="Imagen de prueba">` : ''}
                            </li>
                        `;
                    });
                    html += "</ul>";
                    contenedorRespuestas.innerHTML = html;
                } else {
                    contenedorRespuestas.innerHTML = "<p style='color:#777;'>No hay respuestas registradas para este examen.</p>";
                }

                contenedorResultados.style.display = "block";
            } else {
                Swal.fire({ icon: 'error', title: 'No encontrado', text: data.message });
            }

        } catch (err) {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al obtener resultados del examen.' });
        }
    });
});