// ============================================================
// DASHBOARD ESTUDIANTE: MÉTRICAS PERSONALES Y ACCESO RÁPIDO
// ============================================================

function navegarAPanelAlumno(panelId) {
  const btn = document.querySelector(`.sidebar-btn[href="#${panelId}"]`);
  if (btn) {
    btn.click();
  }
}

function irAConsultarResultado(codigoExamen) {
  navegarAPanelAlumno('panel-resultado-examen');
  const inputCodigo = document.getElementById('codigoExamenRevision');
  const formRevision = document.getElementById('form-acceso-revision');
  if (inputCodigo && formRevision) {
    inputCodigo.value = codigoExamen;
    setTimeout(() => {
      formRevision.dispatchEvent(new Event('submit'));
    }, 150);
  }
}

function cargarDashboardAlumno() {
  fetch('controladores/dashboard.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ accion: 'metricasAlumno' })
  })
  .then(res => res.json())
  .then(resData => {
    if (resData.status !== 'ok') {
      console.error('Error al cargar métricas de alumno:', resData.message);
      return;
    }

    const { kpis, historial } = resData.data;

    // 1. Actualizar KPIs del alumno
    const elRendidos = document.getElementById('alumno-kpi-rendidos');
    const elPromedio = document.getElementById('alumno-kpi-promedio');
    const elCalificados = document.getElementById('alumno-kpi-calificados');
    const elPendientes = document.getElementById('alumno-kpi-pendientes');

    if (elRendidos) elRendidos.textContent = kpis.rendidos;
    if (elPromedio) {
      if (kpis.promedio > 0) {
        const promTexto = (kpis.promedio % 1 === 0) ? kpis.promedio.toString() : parseFloat(Number(kpis.promedio).toFixed(2)).toString();
        elPromedio.textContent = `${promTexto} / 20`;
      } else {
        elPromedio.textContent = 'S/N';
      }
    }
    if (elCalificados) elCalificados.textContent = kpis.calificados;
    if (elPendientes) elPendientes.textContent = kpis.pendientes;

    // 2. Actualizar Tabla de Historial
    const tbodyHistorial = document.querySelector('#tabla-alumno-historial tbody');
    if (tbodyHistorial) {
      tbodyHistorial.innerHTML = '';
      if (historial.length === 0) {
        tbodyHistorial.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#777; padding:15px;">Aún no has rendido ningún examen. ¡Usa el código de tu profesor para comenzar!</td></tr>';
      } else {
        historial.forEach((item, idx) => {
          const tr = document.createElement('tr');
          const fechaFormateada = item.fechaRegistro ? item.fechaRegistro.substring(0, 10) : '-';
          let badgeNota = '';

          if (item.cCalificacion === null || item.cCalificacion === '') {
            badgeNota = '<span class="badge-nota badge-pendiente">En revisión</span>';
          } else {
            const notaNum = parseFloat(item.cCalificacion);
            const notaTexto = (notaNum % 1 === 0) ? notaNum.toString() : parseFloat(notaNum.toFixed(2)).toString();
            if (notaNum >= 14) {
              badgeNota = `<span class="badge-nota badge-aprobado">${notaTexto}</span>`;
            } else if (notaNum >= 11) {
              badgeNota = `<span class="badge-nota badge-regular">${notaTexto}</span>`;
            } else {
              badgeNota = `<span class="badge-nota badge-desaprobado">${notaTexto}</span>`;
            }
          }

          tr.innerHTML = `
            <td>${idx + 1}</td>
            <td><strong>${item.cExamen}</strong></td>
            <td><span class="code-badge">${item.cCodigoExamen}</span></td>
            <td>${badgeNota}</td>
            <td>
              <button class="btn-action-sm btn-revisar" onclick="irAConsultarResultado('${item.cCodigoExamen}')">Ver Detalle</button>
            </td>
          `;
          tbodyHistorial.appendChild(tr);
        });
      }
    }
  })
  .catch(err => console.error('Error al solicitar métricas de alumno:', err));
}

document.addEventListener('DOMContentLoaded', () => {
  cargarDashboardAlumno();

  // Acceso rápido a examen desde el dashboard
  const formQuick = document.getElementById('form-quick-examen');
  if (formQuick) {
    formQuick.addEventListener('submit', e => {
      e.preventDefault();
      const codigo = document.getElementById('quick-codigo-examen').value.trim();
      if (!codigo) return;
      navegarAPanelAlumno('panel-examenes');
      const inputCodigoPrincipal = document.getElementById('codigoExamen');
      const formPrincipal = document.getElementById('form-acceso-examen');
      if (inputCodigoPrincipal && formPrincipal) {
        inputCodigoPrincipal.value = codigo;
        setTimeout(() => {
          formPrincipal.dispatchEvent(new Event('submit'));
        }, 150);
      }
    });
  }

  // Refrescar al hacer click en la pestaña Dashboard
  const btnDashboard = document.querySelector('.sidebar-btn[href="#panel-dashboard"]');
  if (btnDashboard) {
    btnDashboard.addEventListener('click', () => {
      cargarDashboardAlumno();
    });
  }
});
