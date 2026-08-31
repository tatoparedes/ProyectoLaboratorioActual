// ============================================================
// DASHBOARD DOCENTE: MÉTRICAS, GRÁFICO CHART.JS, ACTIVIDAD Y REPORTES
// ============================================================

let chartRendimientoInstance = null;

function mostrarToast(mensaje, tipo = 'info') {
  let toast = document.getElementById('toast-notificacion');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast-notificacion';
    toast.className = 'toast-notification';
    document.body.appendChild(toast);
  }
  toast.textContent = mensaje;
  toast.className = `toast-notification toast-${tipo} show`;
  setTimeout(() => {
    toast.className = 'toast-notification';
  }, 2800);
}

function copiarAlPortapapeles(texto, elementoBtn = null) {
  navigator.clipboard.writeText(texto).then(() => {
    Swal.mixin({ toast: true, position: 'top-end', timer: 2000, showConfirmButton: false }).fire({
      icon: 'success',
      title: `Código copiado: ${texto} 📋`
    });
    if (elementoBtn) {
      const textoOriginal = elementoBtn.innerHTML;
      elementoBtn.innerHTML = '✓ Copiado';
      setTimeout(() => { elementoBtn.innerHTML = textoOriginal; }, 1800);
    }
  }).catch(() => {
    const tempInput = document.createElement('input');
    tempInput.value = texto;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    Swal.mixin({ toast: true, position: 'top-end', timer: 2000, showConfirmButton: false }).fire({
      icon: 'success',
      title: `Código copiado: ${texto} 📋`
    });
  });
}

function navegarAPanel(panelId) {
  const btn = document.querySelector(`.sidebar-btn[href="#${panelId}"]`);
  if (btn) {
    btn.click();
  }
}

function irARevisarExamen(codigoExamen) {
  navegarAPanel('panel-revision-examenes');
  const inputCodigo = document.getElementById('codigoExamen');
  const formBuscar = document.getElementById('form-buscar-examen');
  if (inputCodigo && formBuscar) {
    inputCodigo.value = codigoExamen;
    setTimeout(() => {
      formBuscar.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    }, 150);
  }
}

let primerExamenPendienteGlobal = null;
let nombreExamenPendienteGlobal = null;

function irAPendientesCalificar() {
  if (primerExamenPendienteGlobal) {
    irARevisarExamen(primerExamenPendienteGlobal);
    Swal.mixin({ toast: true, position: 'top-end', timer: 3000, showConfirmButton: false }).fire({
      icon: 'info',
      title: `Cargando evaluaciones pendientes del examen "${nombreExamenPendienteGlobal || primerExamenPendienteGlobal}" ⏳`
    });
  } else {
    // Consultar al servidor si hay evaluaciones pendientes
    fetch('controladores/dashboard.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ accion: 'metricasDocente' })
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'ok' && res.data.primerExamenPendiente) {
        primerExamenPendienteGlobal = res.data.primerExamenPendiente;
        nombreExamenPendienteGlobal = res.data.nombreExamenPendiente;
        irARevisarExamen(res.data.primerExamenPendiente);
        Swal.mixin({ toast: true, position: 'top-end', timer: 3000, showConfirmButton: false }).fire({
          icon: 'info',
          title: `Cargando evaluaciones pendientes ⏳`
        });
      } else {
        navegarAPanel('panel-revision-examenes');
        Swal.fire({
          icon: 'success',
          title: '¡Todo al día!',
          text: 'No tienes evaluaciones pendientes de calificar en este momento 🎉',
          confirmButtonColor: '#0284c7'
        });
      }
    })
    .catch(() => {
      navegarAPanel('panel-revision-examenes');
    });
  }
}

// ============================================================
// CARGA Y RENDERIZADO DEL DASHBOARD DOCENTE
// ============================================================
function cargarDashboardDocente() {
  fetch('controladores/dashboard.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ accion: 'metricasDocente' })
  })
  .then(res => res.json())
  .then(resData => {
    if (resData.status !== 'ok') {
      console.error('Error al cargar métricas de dashboard:', resData.message);
      return;
    }

    const { kpis, ultimosExamenes, ultimasEntregas, primerExamenPendiente, nombreExamenPendiente } = resData.data;

    primerExamenPendienteGlobal = primerExamenPendiente || null;
    nombreExamenPendienteGlobal = nombreExamenPendiente || null;

    // 1. Actualizar KPIs
    const elFamilias = document.getElementById('kpi-familias-val');
    const elGeneros = document.getElementById('kpi-generos-val');
    const elEspecies = document.getElementById('kpi-especies-val');
    const elPruebas = document.getElementById('kpi-pruebas-val');
    const elExamenes = document.getElementById('kpi-examenes-val');
    const elEvaluaciones = document.getElementById('kpi-evaluaciones-val');
    const elPendientes = document.getElementById('kpi-pendientes-val');
    const elPromedio = document.getElementById('kpi-promedio-val');

    if (elFamilias) elFamilias.textContent = kpis.familias;
    if (elGeneros) elGeneros.textContent = kpis.generos;
    if (elEspecies) elEspecies.textContent = kpis.especies;
    if (elPruebas) elPruebas.textContent = kpis.pruebas;
    if (elExamenes) elExamenes.textContent = kpis.examenes;
    if (elEvaluaciones) elEvaluaciones.textContent = kpis.evaluaciones;
    if (elPendientes) {
      elPendientes.textContent = kpis.pendientes;
      const cardPend = document.getElementById('kpi-card-pendientes');
      if (cardPend) {
        if (kpis.pendientes > 0) cardPend.classList.add('kpi-alert');
        else cardPend.classList.remove('kpi-alert');
      }
    }
    if (elPromedio) {
      if (kpis.promedio > 0) {
        const promTexto = (kpis.promedio % 1 === 0) ? kpis.promedio.toString() : parseFloat(Number(kpis.promedio).toFixed(2)).toString();
        elPromedio.textContent = `${promTexto} / 20`;
      } else {
        elPromedio.textContent = 'S/N';
      }
    }

    // 2. Gráfico Chart.js de Rendimiento Académico
    renderizarGraficoRendimiento(kpis.aprobados, kpis.desaprobados, kpis.pendientes);

    // 3. Actualizar Tabla de Últimos Exámenes
    const tbodyExamenes = document.querySelector('#tabla-dashboard-examenes tbody');
    if (tbodyExamenes) {
      tbodyExamenes.innerHTML = '';
      if (ultimosExamenes.length === 0) {
        tbodyExamenes.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#64748b; padding:20px;">No hay exámenes activos registrados aún.</td></tr>';
      } else {
        ultimosExamenes.forEach((ex, idx) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><strong>${idx + 1}</strong></td>
            <td><strong>${ex.cExamen}</strong></td>
            <td>
              <span class="code-badge">${ex.cCodigoExamen}</span>
              <button class="btn-copy-mini" title="Copiar código" onclick="copiarAlPortapapeles('${ex.cCodigoExamen}', this)">📋</button>
            </td>
            <td>${ex.totalPreguntas} preguntas (${ex.totalRendidos} entregas)</td>
            <td>
              <button class="btn-action-sm btn-revisar" onclick="irARevisarExamen('${ex.cCodigoExamen}')">👁️ Revisar</button>
            </td>
          `;
          tbodyExamenes.appendChild(tr);
        });
      }
    }

    // 4. Actualizar Tabla de Últimas Entregas
    const tbodyEntregas = document.querySelector('#tabla-dashboard-entregas tbody');
    if (tbodyEntregas) {
      tbodyEntregas.innerHTML = '';
      if (ultimasEntregas.length === 0) {
        tbodyEntregas.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#64748b; padding:20px;">Aún no se han recibido evaluaciones de estudiantes.</td></tr>';
      } else {
        ultimasEntregas.forEach(ent => {
          const tr = document.createElement('tr');
          const nombreCompleto = `${ent.cApePaterno} ${ent.cApeMaterno}, ${ent.cNombres}`;
          let badgeNota = '';
          if (ent.cCalificacion === null || ent.cCalificacion === '') {
            badgeNota = '<span class="badge-nota badge-pendiente">Pendiente</span>';
          } else {
            const notaNum = parseFloat(ent.cCalificacion);
            const notaTexto = (notaNum % 1 === 0) ? notaNum.toString() : parseFloat(notaNum.toFixed(2)).toString();
            if (notaNum >= 14) badgeNota = `<span class="badge-nota badge-aprobado">${notaTexto}</span>`;
            else if (notaNum >= 11) badgeNota = `<span class="badge-nota badge-regular">${notaTexto}</span>`;
            else badgeNota = `<span class="badge-nota badge-desaprobado">${notaTexto}</span>`;
          }

          tr.innerHTML = `
            <td><strong>${nombreCompleto}</strong></td>
            <td>${ent.cExamen}</td>
            <td>${badgeNota}</td>
            <td>
              <button class="btn-action-sm btn-calificar" onclick="irARevisarExamen('${ent.cCodigoExamen}')">
                ${ent.cCalificacion === null ? '✏️ Calificar' : '👁️ Ver'}
              </button>
            </td>
          `;
          tbodyEntregas.appendChild(tr);
        });
      }
    }

  })
  .catch(err => console.error('Error al solicitar métricas de dashboard:', err));
}

// ============================================================
// GRÁFICO CHART.JS (RENDIMIENTO ACADÉMICO)
// ============================================================
function renderizarGraficoRendimiento(aprobados = 0, desaprobados = 0, pendientes = 0) {
  const canvas = document.getElementById('chart-rendimiento-docente');
  if (!canvas || typeof Chart === 'undefined') return;

  const ctx = canvas.getContext('2d');
  if (chartRendimientoInstance) {
    chartRendimientoInstance.destroy();
  }

  const total = aprobados + desaprobados + pendientes;
  if (total === 0) {
    // Si no hay datos, mostrar placeholder
    aprobados = 1;
  }

  chartRendimientoInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Aprobados (≥ 11)', 'Desaprobados (< 11)', 'Por Calificar'],
      datasets: [{
        data: total === 0 ? [0, 0, 0] : [aprobados, desaprobados, pendientes],
        backgroundColor: ['#10b981', '#ef4444', '#0284c7'],
        borderWidth: 2,
        borderColor: '#ffffff',
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 12,
            padding: 15,
            font: { size: 12, weight: '600' },
            color: '#334155'
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const val = context.raw || 0;
              return ` ${context.label}: ${val} evaluación(es)`;
            }
          }
        }
      },
      cutout: '68%'
    }
  });
}

// ============================================================
// EXPORTAR ACTA DE NOTAS A CSV / EXCEL O IMPRIMIR
// ============================================================
function exportarActaNotasCSV() {
  const tabla = document.getElementById('table-resultados-examen');
  const nombreExamenEl = document.getElementById('nombre-examen-resultado');
  const nombreExamen = nombreExamenEl ? nombreExamenEl.textContent.trim() : 'Acta_Examen';

  if (!tabla) return;
  const filas = tabla.querySelectorAll('tbody tr');
  if (filas.length === 0 || filas[0].querySelector('td[colspan]')) {
    Swal.fire({ icon: 'warning', title: 'Sin datos', text: 'No hay calificaciones disponibles para exportar.' });
    return;
  }

  let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
  csvContent += `"N°","Apellidos y Nombres","Calificación (0-20)"\n`;

  filas.forEach((tr, index) => {
    const tds = tr.querySelectorAll('td');
    if (tds.length >= 3) {
      const alumno = tds[1].textContent.trim().replace(/"/g, '""');
      const notaInput = tds[2].querySelector('input');
      const nota = notaInput ? notaInput.value.trim() : (tds[2].textContent.trim());
      csvContent += `"${index + 1}","${alumno}","${nota || 'Pendiente'}"\n`;
    }
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `Acta_Notas_${nombreExamen.replace(/[^a-zA-Z0-9]/g, '_')}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  Swal.mixin({ toast: true, position: 'top-end', timer: 2000, showConfirmButton: false }).fire({
    icon: 'success',
    title: 'Acta de notas exportada a CSV 📄'
  });
}

function imprimirActaNotas() {
  const tabla = document.getElementById('table-resultados-examen');
  const nombreExamenEl = document.getElementById('nombre-examen-resultado');
  if (!tabla || tabla.querySelectorAll('tbody tr td').length <= 1) {
    Swal.fire({ icon: 'warning', title: 'Sin datos', text: 'Realiza una búsqueda de examen para imprimir el acta.' });
    return;
  }

  const ventanaImpresion = window.open('', '', 'height=700,width=900');
  ventanaImpresion.document.write('<html><head><title>Acta Oficial de Notas</title>');
  ventanaImpresion.document.write('<style>');
  ventanaImpresion.document.write('body { font-family: Arial, sans-serif; padding: 30px; color: #333; }');
  ventanaImpresion.document.write('h2 { color: #0284c7; text-align: center; margin-bottom: 5px; }');
  ventanaImpresion.document.write('h4 { text-align: center; color: #666; margin-top: 0; }');
  ventanaImpresion.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
  ventanaImpresion.document.write('th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }');
  ventanaImpresion.document.write('th { background: #f0f9ff; color: #0369a1; }');
  ventanaImpresion.document.write('.footer { margin-top: 50px; display: flex; justify-content: space-around; text-align: center; }');
  ventanaImpresion.document.write('.firma { border-top: 1px solid #333; width: 220px; padding-top: 5px; }');
  ventanaImpresion.document.write('</style></head><body>');
  ventanaImpresion.document.write('<h2>Laboratorio Clínico - IESTP Trujillo</h2>');
  ventanaImpresion.document.write(`<h4>Acta de Evaluación: ${nombreExamenEl ? nombreExamenEl.textContent : 'Examen'}</h4>`);
  
  let tablaHtml = '<table><thead><tr><th>N°</th><th>Estudiante</th><th>Calificación</th></tr></thead><tbody>';
  const filas = tabla.querySelectorAll('tbody tr');
  filas.forEach((tr, idx) => {
    const tds = tr.querySelectorAll('td');
    if (tds.length >= 3) {
      const alumno = tds[1].textContent.trim();
      const notaInput = tds[2].querySelector('input');
      const nota = notaInput ? notaInput.value.trim() : tds[2].textContent.trim();
      tablaHtml += `<tr><td>${idx+1}</td><td>${alumno}</td><td><strong>${nota || 'Pendiente'}</strong></td></tr>`;
    }
  });
  tablaHtml += '</tbody></table>';

  ventanaImpresion.document.write(tablaHtml);
  ventanaImpresion.document.write('<div class="footer"><div class="firma">Firma del Docente</div><div class="firma">Mesa de Partes / Dirección</div></div>');
  ventanaImpresion.document.write('</body></html>');
  ventanaImpresion.document.close();
  ventanaImpresion.print();
}

// ============================================================
// BUSCADORES EN VIVO EN TODAS LAS TABLAS
// ============================================================
function inicializarBuscadoresEnVivo() {
  const configuracionesBuscador = [
    { inputId: 'buscar-familias', tableId: 'table-familias' },
    { inputId: 'buscar-generos', tableId: 'table-generos' },
    { inputId: 'buscar-especies', tableId: 'table-especies' },
    { inputId: 'buscar-examenes', tableId: 'table-examenes' }
  ];

  configuracionesBuscador.forEach(cfg => {
    const input = document.getElementById(cfg.inputId);
    const table = document.getElementById(cfg.tableId);
    if (input && table) {
      input.addEventListener('input', () => {
        const termino = input.value.toLowerCase().trim();
        const filas = table.querySelectorAll('tbody tr');
        filas.forEach(tr => {
          const textoFila = tr.textContent.toLowerCase();
          tr.style.display = textoFila.includes(termino) ? '' : 'none';
        });
      });
    }
  });

  const inputPruebas = document.getElementById('buscar-pruebas');
  const contenedorPruebas = document.getElementById('contenedorProductos');
  if (inputPruebas && contenedorPruebas) {
    inputPruebas.addEventListener('input', () => {
      const termino = inputPruebas.value.toLowerCase().trim();
      const tarjetas = contenedorPruebas.querySelectorAll('.producto-card, .producto');
      tarjetas.forEach(card => {
        const texto = card.textContent.toLowerCase();
        card.style.display = texto.includes(termino) ? '' : 'none';
      });
    });
  }
}

// ============================================================
// INICIALIZACIÓN
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  cargarDashboardDocente();
  inicializarBuscadoresEnVivo();

  const btnDashboard = document.querySelector('.sidebar-btn[href="#panel-dashboard"]');
  if (btnDashboard) {
    btnDashboard.addEventListener('click', () => {
      cargarDashboardDocente();
    });
  }
});
