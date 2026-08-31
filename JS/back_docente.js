// ============================================================
// GESTIÓN DE FAMILIAS, GÉNEROS, ESPECIES Y PRUEBAS CON SWEETALERT2
// ============================================================

// Helper para SweetAlert2 Toasts
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  }
});

// ==================== GESTIÓN DE FAMILIAS ====================
const formFamilias = document.getElementById('form-familias');
const tbodyFamilias = document.querySelector('#table-familias tbody');
const inputIdFamilia = document.getElementById('id_familia_edit');
const inputAccionFamilia = document.getElementById('accion_familia');
const inputNombreFamilia = document.getElementById('nombre_familia');
const btnCancelarFamilia = document.getElementById('btn-cancelar');

// Selectores de familias en los diferentes módulos
const selectFamiliaGenero = document.getElementById('familia_select_genero');
const selectFamiliaEspecie = document.getElementById('familia_select_especie');
const selectFamiliaPrueba = document.getElementById('familiaSelect');
const selectFamiliaExamen = document.getElementById('familia');

function actualizarTodosLosSelectsFamilias(familias) {
  const selects = [
    { el: selectFamiliaGenero, defaultText: '-- Elige una familia --' },
    { el: selectFamiliaEspecie, defaultText: '-- Elige una familia --' },
    { el: selectFamiliaPrueba, defaultText: '-- Elige una familia --' },
    { el: selectFamiliaExamen, defaultText: '-- Seleccione familia --' }
  ];

  selects.forEach(({ el, defaultText }) => {
    if (!el) return;
    const valorActual = el.value;
    el.innerHTML = `<option value="">${defaultText}</option>`;
    familias.forEach(fam => {
      const opt = document.createElement('option');
      opt.value = fam.nFamilia;
      opt.textContent = fam.cFamilia;
      if (fam.nFamilia == valorActual) {
        opt.selected = true;
      }
      el.appendChild(opt);
    });
  });
}

function cargarFamilias() {
  fetch('controladores/familia.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({accion: 'listar'})
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      if (tbodyFamilias) {
        tbodyFamilias.innerHTML = '';
        if (data.data.length === 0) {
          tbodyFamilias.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#777; padding:15px;">No hay familias registradas.</td></tr>';
        } else {
          data.data.forEach(familia => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td><strong>#${familia.nFamilia}</strong></td>
              <td><span class="taxo-badge badge-familia">🧬 ${familia.cFamilia}</span></td>
              <td class="table-actions">
                <button type="button" class="btn-action btn-edit-table" data-id="${familia.nFamilia}" data-nombre="${familia.cFamilia}">✏️ Editar</button>
                <button type="button" class="btn-action btn-delete-table" data-id="${familia.nFamilia}">🗑️ Eliminar</button>
              </td>
            `;
            tbodyFamilias.appendChild(tr);
          });
          agregarEventosAccionFamilia();
        }
      }
      actualizarTodosLosSelectsFamilias(data.data);
    } else {
      console.error('Error al cargar familias:', data.message);
    }
  })
  .catch(err => console.error('Error en la solicitud familias:', err));
}

function limpiarFormularioFamilia() {
  if (inputIdFamilia) inputIdFamilia.value = '0';
  if (inputAccionFamilia) inputAccionFamilia.value = 'agregar';
  if (inputNombreFamilia) inputNombreFamilia.value = '';
  if (btnCancelarFamilia) btnCancelarFamilia.style.display = 'none';
  const btnSubmit = formFamilias ? formFamilias.querySelector('.btn-submit') : null;
  if (btnSubmit) btnSubmit.innerHTML = '💾 Guardar Familia';
}

function agregarEventosAccionFamilia() {
  document.querySelectorAll('#table-familias .btn-edit-table').forEach(btn => {
    btn.onclick = e => {
      e.preventDefault();
      if (btn.dataset.nombre) {
        inputIdFamilia.value = btn.dataset.id;
        inputNombreFamilia.value = btn.dataset.nombre;
        inputAccionFamilia.value = 'editar';
        if (btnCancelarFamilia) btnCancelarFamilia.style.display = 'inline-flex';
        const btnSubmit = formFamilias.querySelector('.btn-submit');
        if (btnSubmit) btnSubmit.innerHTML = '🔄 Actualizar Familia';
        inputNombreFamilia.focus();
        Toast.fire({ icon: 'info', title: `Editando familia: ${btn.dataset.nombre}` });
      }
    };
  });

  document.querySelectorAll('#table-familias .btn-delete-table').forEach(btn => {
    btn.onclick = e => {
      e.preventDefault();
      const id = btn.dataset.id;
      Swal.fire({
        title: '¿Eliminar esta familia?',
        text: 'Esta acción no se puede deshacer si no tiene dependencias.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('controladores/familia.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({accion: 'eliminar', nFamilia: id})
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'ok') {
              Swal.fire({ icon: 'success', title: '¡Eliminada!', text: data.message, timer: 1800, showConfirmButton: false });
              cargarFamilias();
              cargarGeneros();
              cargarEspecies();
              cargarPruebas();
              limpiarFormularioFamilia();
              if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
            } else {
              Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: data.message });
            }
          })
          .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error en la solicitud: ' + err }));
        }
      });
    };
  });
}

if (formFamilias) {
  formFamilias.addEventListener('submit', e => {
    e.preventDefault();
    const accion = inputAccionFamilia.value;
    const nombre = inputNombreFamilia.value.trim();
    const id = inputIdFamilia.value;

    if (nombre === '') {
      Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre de la familia no puede estar vacío.' });
      return;
    }

    const datos = new URLSearchParams();
    datos.append('accion', accion);
    datos.append('cFamilia', nombre);
    if (accion === 'editar') {
      datos.append('nFamilia', id);
    }

    fetch('controladores/familia.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: datos
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message, timer: 1800, showConfirmButton: false });
        limpiarFormularioFamilia();
        cargarFamilias();
        cargarGeneros();
        cargarEspecies();
        cargarPruebas();
        if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
      }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + err }));
  });
}

if (btnCancelarFamilia) {
  btnCancelarFamilia.onclick = () => {
    limpiarFormularioFamilia();
    Toast.fire({ icon: 'info', title: 'Edición cancelada' });
  };
}


// ==================== GESTIÓN DE GÉNEROS ====================
const formGeneros = document.getElementById('form-generos');
const tbodyGeneros = document.querySelector('#table-generos tbody');
const inputIdGenero = document.getElementById('id_genero_edit');
const inputAccionGenero = document.getElementById('accion_genero');
const inputNombreGenero = document.getElementById('nombre_genero');
const btnCancelarGenero = document.getElementById('btn-cancelar-genero');

function cargarGeneros(nFamilia = null) {
  const params = nFamilia 
    ? new URLSearchParams({accion: 'listarPorFamilia', nFamilia}) 
    : new URLSearchParams({accion: 'listar'});

  fetch('controladores/genero.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: params
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      if (tbodyGeneros) {
        tbodyGeneros.innerHTML = '';
        if (data.data.length === 0) {
          tbodyGeneros.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#777; padding:15px;">No hay géneros registrados.</td></tr>';
        } else {
          data.data.forEach(genero => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td><strong>#${genero.nGenero}</strong></td>
              <td><span class="taxo-badge badge-familia">🧬 ${genero.cFamilia || ''}</span></td>
              <td><span class="taxo-badge badge-genero">🌿 ${genero.cGenero}</span></td>
              <td class="table-actions">
                <button type="button" class="btn-action btn-edit-table" 
                  data-id="${genero.nGenero}" 
                  data-nombre="${genero.cGenero}" 
                  data-familia="${genero.nFamilia}">✏️ Editar</button>
                <button type="button" class="btn-action btn-delete-table" data-id="${genero.nGenero}">🗑️ Eliminar</button>
              </td>
            `;
            tbodyGeneros.appendChild(tr);
          });
          agregarEventosAccionGenero();
        }
      }
    } else {
      console.error('Error al cargar géneros:', data.message);
    }
  })
  .catch(err => console.error('Error en la solicitud géneros:', err));
}

function limpiarFormularioGenero() {
  if (inputIdGenero) inputIdGenero.value = '0';
  if (inputAccionGenero) inputAccionGenero.value = 'agregar';
  if (inputNombreGenero) inputNombreGenero.value = '';
  if (selectFamiliaGenero) selectFamiliaGenero.value = '';
  if (btnCancelarGenero) btnCancelarGenero.style.display = 'none';
  const btnSubmit = formGeneros ? formGeneros.querySelector('.btn-submit') : null;
  if (btnSubmit) btnSubmit.innerHTML = '💾 Guardar Género';
}

function agregarEventosAccionGenero() {
  document.querySelectorAll('#table-generos .btn-edit-table').forEach(btn => {
    btn.onclick = e => {
      e.preventDefault();
      if (btn.dataset.nombre) {
        inputIdGenero.value = btn.dataset.id;
        inputNombreGenero.value = btn.dataset.nombre;
        if (selectFamiliaGenero) selectFamiliaGenero.value = btn.dataset.familia;
        inputAccionGenero.value = 'editar';
        if (btnCancelarGenero) btnCancelarGenero.style.display = 'inline-flex';
        const btnSubmit = formGeneros.querySelector('.btn-submit');
        if (btnSubmit) btnSubmit.innerHTML = '🔄 Actualizar Género';
        inputNombreGenero.focus();
        Toast.fire({ icon: 'info', title: `Editando género: ${btn.dataset.nombre}` });
      }
    };
  });

  document.querySelectorAll('#table-generos .btn-delete-table').forEach(btn => {
    btn.onclick = e => {
      e.preventDefault();
      const id = btn.dataset.id;
      Swal.fire({
        title: '¿Eliminar este género?',
        text: 'Esta acción no se puede deshacer si no tiene especies asociadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('controladores/genero.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({accion: 'eliminar', nGenero: id})
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'ok') {
              Swal.fire({ icon: 'success', title: '¡Eliminado!', text: data.message, timer: 1800, showConfirmButton: false });
              cargarGeneros();
              cargarEspecies();
              cargarPruebas();
              limpiarFormularioGenero();
              if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
            } else {
              Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: data.message });
            }
          })
          .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + err }));
        }
      });
    };
  });
}

if (formGeneros) {
  formGeneros.addEventListener('submit', e => {
    e.preventDefault();

    const accion = inputAccionGenero.value;
    const nombre = inputNombreGenero.value.trim();
    const familiaId = selectFamiliaGenero.value;

    if (!familiaId) {
      Swal.fire({ icon: 'warning', title: 'Selección requerida', text: 'Por favor seleccione una familia.' });
      return;
    }
    if (nombre === '') {
      Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre del género no puede estar vacío.' });
      return;
    }

    const datos = new URLSearchParams();
    datos.append('accion', accion);
    datos.append('cGenero', nombre);
    datos.append('nFamilia', familiaId);
    if (accion === 'editar') {
      datos.append('nGenero', inputIdGenero.value);
    }

    fetch('controladores/genero.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: datos
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message, timer: 1800, showConfirmButton: false });
        limpiarFormularioGenero();
        cargarGeneros();
        cargarEspecies();
        cargarPruebas();
        if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
      }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + err }));
  });
}

if (btnCancelarGenero) {
  btnCancelarGenero.onclick = () => {
    limpiarFormularioGenero();
    Toast.fire({ icon: 'info', title: 'Edición cancelada' });
  };
}


// ==================== GESTIÓN DE ESPECIES ====================
const formEspecies = document.getElementById('form-especies');
const tbodyEspecies = document.querySelector('#table-especies tbody');
const inputIdEspecie = document.getElementById('id_especie_edit');
const inputAccionEspecie = document.getElementById('accion_especie');
const inputNombreEspecie = document.getElementById('nombre_especie');
const selectGeneroEspecie = document.getElementById('genero_select_especie');
const btnCancelarEspecie = document.getElementById('btn-cancelar-especie');

function cargarGenerosPorFamiliaEspecie(familiaId, selectedGeneroId = null) {
  if (!selectGeneroEspecie) return Promise.resolve();
  if (!familiaId) {
    selectGeneroEspecie.innerHTML = '<option value="">-- Elige un género --</option>';
    selectGeneroEspecie.disabled = true;
    return Promise.resolve();
  }

  return fetch('controladores/genero.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({accion: 'listarPorFamilia', nFamilia: familiaId})
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      selectGeneroEspecie.innerHTML = '<option value="">-- Elige un género --</option>';
      data.data.forEach(gen => {
        const selected = (selectedGeneroId && selectedGeneroId == gen.nGenero) ? 'selected' : '';
        selectGeneroEspecie.insertAdjacentHTML('beforeend', `<option value="${gen.nGenero}" ${selected}>${gen.cGenero}</option>`);
      });
      selectGeneroEspecie.disabled = false;
    } else {
      console.error('Error al cargar géneros:', data.message);
    }
  })
  .catch(err => console.error('Error en géneros:', err));
}

function cargarEspecies() {
  fetch('controladores/especie.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({accion: 'listar'})
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      if (tbodyEspecies) {
        tbodyEspecies.innerHTML = '';
        if (data.data.length === 0) {
          tbodyEspecies.innerHTML = '<tr><td colspan="5" style="text-align:center; color:#777; padding:15px;">No hay especies registradas.</td></tr>';
        } else {
          data.data.forEach(esp => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td><strong>#${esp.nEspecie}</strong></td>
              <td><span class="taxo-badge badge-familia">🧬 ${esp.cFamilia}</span></td>
              <td><span class="taxo-badge badge-genero">🌿 ${esp.cGenero}</span></td>
              <td><span class="taxo-badge badge-especie">🔬 ${esp.cEspecie}</span></td>
              <td class="table-actions">
                <button type="button" class="btn-action btn-edit-table" data-id="${esp.nEspecie}" data-nombre="${esp.cEspecie}" data-familia="${esp.nFamilia}" data-genero="${esp.nGenero}">✏️ Editar</button>
                <button type="button" class="btn-action btn-delete-table" data-id="${esp.nEspecie}">🗑️ Eliminar</button>
              </td>
            `;
            tbodyEspecies.appendChild(tr);
          });
          agregarEventosAccionEspecie();
        }
      }
    } else {
      console.error('Error al cargar especies:', data.message);
    }
  })
  .catch(err => console.error('Error en especies:', err));
}

function limpiarFormularioEspecie() {
  if (inputIdEspecie) inputIdEspecie.value = '0';
  if (inputAccionEspecie) inputAccionEspecie.value = 'agregar';
  if (inputNombreEspecie) inputNombreEspecie.value = '';
  if (selectFamiliaEspecie) selectFamiliaEspecie.value = '';
  if (selectGeneroEspecie) {
    selectGeneroEspecie.innerHTML = '<option value="">-- Elige un género --</option>';
    selectGeneroEspecie.disabled = true;
  }
  if (btnCancelarEspecie) btnCancelarEspecie.style.display = 'none';
  const btnSubmit = formEspecies ? formEspecies.querySelector('.btn-submit') : null;
  if (btnSubmit) btnSubmit.innerHTML = '💾 Guardar Especie';
}

function agregarEventosAccionEspecie() {
  document.querySelectorAll('#table-especies .btn-edit-table').forEach(btn => {
    btn.onclick = async e => {
      e.preventDefault();
      if (btn.dataset.nombre) {
        inputIdEspecie.value = btn.dataset.id;
        inputNombreEspecie.value = btn.dataset.nombre;
        inputAccionEspecie.value = 'editar';
        if (selectFamiliaEspecie) selectFamiliaEspecie.value = btn.dataset.familia;
        await cargarGenerosPorFamiliaEspecie(btn.dataset.familia, btn.dataset.genero);
        if (btnCancelarEspecie) btnCancelarEspecie.style.display = 'inline-flex';
        const btnSubmit = formEspecies.querySelector('.btn-submit');
        if (btnSubmit) btnSubmit.innerHTML = '🔄 Actualizar Especie';
        inputNombreEspecie.focus();
        Toast.fire({ icon: 'info', title: `Editando especie: ${btn.dataset.nombre}` });
      }
    };
  });

  document.querySelectorAll('#table-especies .btn-delete-table').forEach(btn => {
    btn.onclick = e => {
      e.preventDefault();
      const id = btn.dataset.id;
      Swal.fire({
        title: '¿Eliminar esta especie?',
        text: 'Esta acción no se puede deshacer si no tiene pruebas asociadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('controladores/especie.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({accion: 'eliminar', nEspecie: id})
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'ok') {
              Swal.fire({ icon: 'success', title: '¡Eliminada!', text: data.message, timer: 1800, showConfirmButton: false });
              cargarEspecies();
              cargarPruebas();
              limpiarFormularioEspecie();
              if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
            } else {
              Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: data.message });
            }
          })
          .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + err }));
        }
      });
    };
  });
}

if (selectFamiliaEspecie) {
  selectFamiliaEspecie.addEventListener('change', () => {
    cargarGenerosPorFamiliaEspecie(selectFamiliaEspecie.value);
  });
}

if (formEspecies) {
  formEspecies.addEventListener('submit', e => {
    e.preventDefault();

    const accion = inputAccionEspecie.value;
    const nombre = inputNombreEspecie.value.trim();
    const id = inputIdEspecie.value;
    const familiaId = selectFamiliaEspecie.value;
    const generoId = selectGeneroEspecie.value;

    if (!familiaId) {
      Swal.fire({ icon: 'warning', title: 'Selección requerida', text: 'Seleccione una familia.' });
      return;
    }
    if (!generoId) {
      Swal.fire({ icon: 'warning', title: 'Selección requerida', text: 'Seleccione un género.' });
      return;
    }
    if (nombre === '') {
      Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'El nombre de la especie no puede estar vacío.' });
      return;
    }

    const datos = new URLSearchParams();
    datos.append('accion', accion);
    datos.append('cEspecie', nombre);
    datos.append('nFamilia', familiaId);
    datos.append('nGenero', generoId);
    if (accion === 'editar') {
      datos.append('nEspecie', id);
    }

    fetch('controladores/especie.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: datos
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        Swal.fire({ icon: 'success', title: '¡Éxito!', text: data.message, timer: 1800, showConfirmButton: false });
        limpiarFormularioEspecie();
        cargarEspecies();
        cargarPruebas();
        if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
      }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + err }));
  });
}

if (btnCancelarEspecie) {
  btnCancelarEspecie.onclick = () => {
    limpiarFormularioEspecie();
    Toast.fire({ icon: 'info', title: 'Edición cancelada' });
  };
}


// ==================== GESTIÓN DE PRUEBAS & MUESTRAS ====================
// Lightbox modal para ver imágenes de muestras en alta resolución
function verMuestraLightbox(fotoUrl, titulo, familia, genero, especie) {
  Swal.fire({
    title: `<div style="font-size:1.25rem; font-weight:700; color:#1e293b;">🧫 ${titulo}</div>`,
    html: `
      <div style="margin-bottom:12px; display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
        <span class="taxo-badge badge-familia">🧬 ${familia}</span>
        <span class="taxo-badge badge-genero">🌿 ${genero}</span>
        <span class="taxo-badge badge-especie">🔬 ${especie}</span>
      </div>
      <div style="border-radius:12px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,0.15); max-height:65vh;">
        <img src="${fotoUrl}" alt="${titulo}" style="width:100%; height:auto; max-height:60vh; object-fit:contain; display:block; margin:auto;">
      </div>
    `,
    showCloseButton: true,
    showConfirmButton: false,
    width: '650px',
    background: '#ffffff',
    customClass: {
      popup: 'swal-sample-lightbox'
    }
  });
}

// Elementos del módulo de pruebas
const formPrueba = document.getElementById('formularioProducto');
const btnCancelarPrueba = document.getElementById('cancelButton');
const btnMostrarFormPrueba = document.getElementById('mostrarFormularioBtn');

const selectFamiliaPruebaModal = document.getElementById('familiaSelect');
const selectGeneroPruebaModal = document.getElementById('generoSelect');
const selectEspeciePruebaModal = document.getElementById('especieSelect');
const inputImagenPrueba = document.getElementById('imagenInput');
const previewImagenPrueba = document.getElementById('previewImagen');

const contenedorProductos = document.getElementById('contenedorProductos');
const inputAccionPrueba = document.getElementById('accion');
const inputNPrueba = document.getElementById('nPrueba');

// Al cambiar familia en pruebas, cargar géneros
if (selectFamiliaPruebaModal) {
  selectFamiliaPruebaModal.addEventListener('change', () => {
    const nFamilia = selectFamiliaPruebaModal.value;
    if (selectGeneroPruebaModal) {
      selectGeneroPruebaModal.innerHTML = '<option value="" disabled selected>-- Elige un género --</option>';
      selectGeneroPruebaModal.disabled = true;
    }
    if (selectEspeciePruebaModal) {
      selectEspeciePruebaModal.innerHTML = '<option value="" disabled selected>-- Elige una especie --</option>';
      selectEspeciePruebaModal.disabled = true;
    }

    if (!nFamilia) return;

    fetch('controladores/genero.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({accion: 'listarPorFamilia', nFamilia})
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok' && selectGeneroPruebaModal) {
        selectGeneroPruebaModal.innerHTML = '<option value="" disabled selected>-- Elige un género --</option>';
        data.data.forEach(gen => {
          const option = document.createElement('option');
          option.value = gen.nGenero;
          option.textContent = gen.cGenero;
          selectGeneroPruebaModal.appendChild(option);
        });
        selectGeneroPruebaModal.disabled = false;
      }
    });
  });
}

// Al cambiar género en pruebas, cargar especies
if (selectGeneroPruebaModal) {
  selectGeneroPruebaModal.addEventListener('change', () => {
    const nGenero = selectGeneroPruebaModal.value;
    if (selectEspeciePruebaModal) {
      selectEspeciePruebaModal.innerHTML = '<option value="" disabled selected>-- Elige una especie --</option>';
      selectEspeciePruebaModal.disabled = true;
    }

    if (!nGenero) return;

    fetch('controladores/especie.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({accion: 'listarPorGenero', nGenero})
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok' && selectEspeciePruebaModal) {
        selectEspeciePruebaModal.innerHTML = '<option value="" disabled selected>-- Elige una especie --</option>';
        data.data.forEach(esp => {
          const option = document.createElement('option');
          option.value = esp.nEspecie;
          option.textContent = esp.cEspecie;
          selectEspeciePruebaModal.appendChild(option);
        });
        selectEspeciePruebaModal.disabled = false;
      }
    });
  });
}

// Previsualización de imagen al seleccionar archivo
if (inputImagenPrueba) {
  inputImagenPrueba.addEventListener('change', e => {
    const file = e.target.files[0];
    if (file && previewImagenPrueba) {
      const reader = new FileReader();
      reader.onload = event => {
        previewImagenPrueba.src = event.target.result;
        previewImagenPrueba.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else if (previewImagenPrueba) {
      previewImagenPrueba.src = '';
      previewImagenPrueba.style.display = 'none';
    }
  });
}

// Enviar formulario de prueba (crear o editar)
if (formPrueba) {
  formPrueba.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(formPrueba);

    Swal.fire({
      title: 'Guardando prueba...',
      text: 'Por favor espere',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    fetch('controladores/prueba.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        Swal.fire({ icon: 'success', title: '¡Guardado!', text: data.message || 'Prueba guardada correctamente', timer: 1800, showConfirmButton: false });
        cargarPruebas();
        if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
        formPrueba.style.display = 'none';
        if (btnCancelarPrueba) btnCancelarPrueba.style.display = 'none';
        if (btnMostrarFormPrueba) btnMostrarFormPrueba.style.display = 'inline-flex';
        limpiarFormularioPrueba();
      } else {
        Swal.fire({ icon: 'error', title: 'Error al guardar', text: data.message });
      }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + err }));
  });
}

function limpiarFormularioPrueba() {
  if (formPrueba) formPrueba.reset();
  if (selectGeneroPruebaModal) {
    selectGeneroPruebaModal.innerHTML = '<option value="" disabled selected>-- Elige un género --</option>';
    selectGeneroPruebaModal.disabled = true;
  }
  if (selectEspeciePruebaModal) {
    selectEspeciePruebaModal.innerHTML = '<option value="" disabled selected>-- Elige una especie --</option>';
    selectEspeciePruebaModal.disabled = true;
  }
  if (inputAccionPrueba) inputAccionPrueba.value = 'agregar';
  if (inputNPrueba) inputNPrueba.value = '0';
  if (inputImagenPrueba) inputImagenPrueba.setAttribute('required', 'required');
  if (previewImagenPrueba) {
    previewImagenPrueba.style.display = 'none';
    previewImagenPrueba.src = '';
  }
  const btnSave = document.getElementById('saveButton');
  if (btnSave) btnSave.innerHTML = '💾 Guardar Prueba';
}

// Cargar pruebas en tarjetas con diseño de alta calidad
function cargarPruebas() {
  if (!contenedorProductos) return;
  fetch('controladores/prueba.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({accion: 'listar'})
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'ok') {
      contenedorProductos.innerHTML = '';

      if (data.data.length === 0) {
        contenedorProductos.innerHTML = `
          <div class="empty-state-card" style="grid-column: 1 / -1; text-align:center; padding:40px; background:#fff; border-radius:14px; border:1px dashed #cbd5e0;">
            <div style="font-size:3rem; margin-bottom:10px;">🧫</div>
            <h4 style="color:#2d3748; margin-bottom:6px;">No hay pruebas microbiológicas registradas</h4>
            <p style="color:#718096; font-size:0.95rem;">Haz clic en "Agregar Prueba" para comenzar a poblar el catálogo de muestras.</p>
          </div>
        `;
        return;
      }

      data.data.forEach(prueba => {
        const div = document.createElement('div');
        div.className = 'producto-card'; 

        const fotoUrl = prueba.cFoto ? `uploads/${prueba.cFoto}` : 'imagenes/default_bacteria.png';

        div.innerHTML = `
          <div class="card-img-container" onclick="verMuestraLightbox('${fotoUrl}', '${prueba.cBacteria}', '${prueba.cFamilia}', '${prueba.cGenero}', '${prueba.cEspecie}')" title="Toca para ampliar imagen">
            <img src="${fotoUrl}" alt="${prueba.cBacteria}" class="card-sample-img" loading="lazy">
            <div class="img-zoom-badge">🔍 Ampliar</div>
          </div>

          <div class="card-body">
            <div class="card-taxo-badges">
              <span class="taxo-badge badge-familia">🧬 ${prueba.cFamilia}</span>
              <span class="taxo-badge badge-genero">🌿 ${prueba.cGenero}</span>
              <span class="taxo-badge badge-especie">🔬 ${prueba.cEspecie}</span>
            </div>

            <h3 class="card-sample-title">${prueba.cBacteria}</h3>

            <div class="card-info-box">
              <div class="info-row">
                <span class="info-label">📋 Descripción:</span>
                <p class="info-text">${prueba.cDescripcion}</p>
              </div>
              <div class="info-row" style="margin-top:8px;">
                <span class="info-label">🧪 Resultado Bioquímico:</span>
                <div class="resultado-highlight">${prueba.cResultado}</div>
              </div>
            </div>

            <div class="card-action-buttons">
              <button type="button" class="btn-card-view" onclick="verMuestraLightbox('${fotoUrl}', '${prueba.cBacteria}', '${prueba.cFamilia}', '${prueba.cGenero}', '${prueba.cEspecie}')" title="Ver muestra en pantalla completa">
                <i class="fas fa-eye"></i> Ver
              </button>
              <button type="button" class="btn-card-edit" data-id="${prueba.nPrueba}" title="Modificar datos de la prueba">
                <i class="fas fa-edit"></i> Editar
              </button>
              <button type="button" class="btn-card-delete" data-id="${prueba.nPrueba}" title="Eliminar prueba del catálogo">
                <i class="fas fa-trash-alt"></i> Eliminar
              </button>
            </div>
          </div>
        `;

        contenedorProductos.appendChild(div);
      });

      // Eventos de edición y eliminación
      document.querySelectorAll('.btn-card-edit').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          editarPrueba(id);
        });
      });

      document.querySelectorAll('.btn-card-delete').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-id');
          eliminarPrueba(id);
        });
      });

      // Filtro de búsqueda en tiempo real
      const inputBuscarPruebas = document.getElementById('buscar-pruebas');
      if (inputBuscarPruebas && !inputBuscarPruebas.dataset.hasListener) {
        inputBuscarPruebas.dataset.hasListener = 'true';
        inputBuscarPruebas.addEventListener('input', () => {
          const query = inputBuscarPruebas.value.toLowerCase().trim();
          document.querySelectorAll('#contenedorProductos .producto-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(query) ? 'flex' : 'none';
          });
        });
      }

    } else {
      contenedorProductos.innerHTML = '<p>No se encontraron pruebas registradas.</p>';
    }
  })
  .catch(() => { if (contenedorProductos) contenedorProductos.innerHTML = '<p>Error al cargar catálogo de pruebas.</p>'; });
}

// Cargar datos de una prueba al formulario para editar
async function editarPrueba(id) {
  try {
    Swal.fire({
      title: 'Cargando datos...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    const res = await fetch('controladores/prueba.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({accion: 'listar'})
    });
    const data = await res.json();
    Swal.close();

    if (data.status !== 'ok') {
      Swal.fire({ icon: 'error', title: 'Error', text: data.message });
      return;
    }

    const prueba = data.data.find(p => p.nPrueba == id);
    if (!prueba) {
      Swal.fire({ icon: 'error', title: 'No encontrada', text: 'La prueba seleccionada no existe.' });
      return;
    }

    if (selectFamiliaPruebaModal) selectFamiliaPruebaModal.value = prueba.nFamilia;

    // Cargar géneros
    const resGen = await fetch('controladores/genero.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({accion: 'listarPorFamilia', nFamilia: prueba.nFamilia})
    });
    const dataGen = await resGen.json();
    if (dataGen.status === 'ok' && selectGeneroPruebaModal) {
      selectGeneroPruebaModal.innerHTML = '<option value="" disabled>-- Elige un género --</option>';
      dataGen.data.forEach(gen => {
        const opt = document.createElement('option');
        opt.value = gen.nGenero;
        opt.textContent = gen.cGenero;
        if (gen.nGenero == prueba.nGenero) opt.selected = true;
        selectGeneroPruebaModal.appendChild(opt);
      });
      selectGeneroPruebaModal.disabled = false;
    }

    // Cargar especies
    const resEsp = await fetch('controladores/especie.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({accion: 'listarPorGenero', nGenero: prueba.nGenero})
    });
    const dataEsp = await resEsp.json();
    if (dataEsp.status === 'ok' && selectEspeciePruebaModal) {
      selectEspeciePruebaModal.innerHTML = '<option value="" disabled>-- Elige una especie --</option>';
      dataEsp.data.forEach(esp => {
        const opt = document.createElement('option');
        opt.value = esp.nEspecie;
        opt.textContent = esp.cEspecie;
        if (esp.nEspecie == prueba.nEspecie) opt.selected = true;
        selectEspeciePruebaModal.appendChild(opt);
      });
      selectEspeciePruebaModal.disabled = false;
    }

    const inputNombreP = document.getElementById('nombrePruebaInput');
    const inputDescP = document.getElementById('descripcionInput');
    const inputResP = document.getElementById('resultadoInput');
    if (inputNombreP) inputNombreP.value = prueba.cBacteria;
    if (inputDescP) inputDescP.value = prueba.cDescripcion;
    if (inputResP) inputResP.value = prueba.cResultado;
    if (inputAccionPrueba) inputAccionPrueba.value = 'editar';
    if (inputNPrueba) inputNPrueba.value = prueba.nPrueba;

    if (inputImagenPrueba) inputImagenPrueba.removeAttribute('required');
    if (previewImagenPrueba) {
      if (prueba.cFoto) {
        previewImagenPrueba.src = 'uploads/' + prueba.cFoto;
        previewImagenPrueba.style.display = 'block';
      } else {
        previewImagenPrueba.src = '';
        previewImagenPrueba.style.display = 'none';
      }
    }

    const btnSave = document.getElementById('saveButton');
    if (btnSave) btnSave.innerHTML = '🔄 Actualizar Prueba';

    if (formPrueba) {
      formPrueba.style.display = 'block';
      formPrueba.scrollIntoView({ behavior: 'smooth' });
    }
    if (btnCancelarPrueba) btnCancelarPrueba.style.display = 'inline-flex';
    if (btnMostrarFormPrueba) btnMostrarFormPrueba.style.display = 'none';

    Toast.fire({ icon: 'info', title: `Editando muestra: ${prueba.cBacteria}` });

  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Error al cargar prueba: ' + err });
  }
}

function eliminarPrueba(id) {
  Swal.fire({
    title: '¿Eliminar esta prueba microbiológica?',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('controladores/prueba.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({accion: 'eliminar', nPrueba: id})
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          Swal.fire({ icon: 'success', title: '¡Eliminada!', text: data.message || 'Prueba eliminada con éxito', timer: 1800, showConfirmButton: false });
          cargarPruebas();
          if (typeof cargarDashboardDocente === 'function') cargarDashboardDocente();
        } else {
          Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: data.message });
        }
      })
      .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: 'Error en la solicitud: ' + err }));
    }
  });
}

if (btnMostrarFormPrueba) {
  btnMostrarFormPrueba.addEventListener('click', () => {
    limpiarFormularioPrueba();
    if (formPrueba) {
      formPrueba.style.display = 'block';
      formPrueba.scrollIntoView({ behavior: 'smooth' });
    }
    if (btnCancelarPrueba) btnCancelarPrueba.style.display = 'inline-flex';
    btnMostrarFormPrueba.style.display = 'none';
  });
}

if (btnCancelarPrueba) {
  btnCancelarPrueba.addEventListener('click', () => {
    limpiarFormularioPrueba();
    if (formPrueba) formPrueba.style.display = 'none';
    btnCancelarPrueba.style.display = 'none';
    if (btnMostrarFormPrueba) btnMostrarFormPrueba.style.display = 'inline-flex';
    Toast.fire({ icon: 'info', title: 'Operación cancelada' });
  });
}

// Inicializar carga de datos de todos los módulos
document.addEventListener('DOMContentLoaded', () => {
  cargarFamilias();
  cargarGeneros();
  cargarEspecies();
  cargarPruebas();
});


// ==================== CARGA DE DATOS PARA CREAR PREGUNTAS DEL EXAMEN ====================
document.addEventListener('DOMContentLoaded', () => {
  const familiaSelectExamen = document.getElementById('familia');
  const generoSelectExamen = document.getElementById('genero');
  const especieSelectExamen = document.getElementById('especie');
  const pruebaSelectExamen = document.getElementById('pruebaSelect');
  const previewExamen = document.getElementById('preview');

  if (familiaSelectExamen) {
    familiaSelectExamen.addEventListener('change', () => {
      const nFamilia = familiaSelectExamen.value;
      generoSelectExamen.innerHTML = '<option value="">-- Seleccione género --</option>';
      generoSelectExamen.disabled = true;
      especieSelectExamen.innerHTML = '<option value="">-- Seleccione especie --</option>';
      especieSelectExamen.disabled = true;
      pruebaSelectExamen.innerHTML = '<option value="">-- Seleccione prueba --</option>';
      pruebaSelectExamen.disabled = true;
      if (previewExamen) previewExamen.style.display = 'none';

      if (!nFamilia) return;

      fetch('controladores/genero.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({accion: 'listarPorFamilia', nFamilia})
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          data.data.forEach(gen => {
            generoSelectExamen.insertAdjacentHTML('beforeend',
              `<option value="${gen.nGenero}">${gen.cGenero}</option>`);
          });
          generoSelectExamen.disabled = false;
        }
      });
    });
  }

  if (generoSelectExamen) {
    generoSelectExamen.addEventListener('change', () => {
      const nGenero = generoSelectExamen.value;
      especieSelectExamen.innerHTML = '<option value="">-- Seleccione especie --</option>';
      especieSelectExamen.disabled = true;
      pruebaSelectExamen.innerHTML = '<option value="">-- Seleccione prueba --</option>';
      pruebaSelectExamen.disabled = true;
      if (previewExamen) previewExamen.style.display = 'none';

      if (!nGenero) return;

      fetch('controladores/especie.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({accion: 'listarPorGenero', nGenero})
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          data.data.forEach(esp => {
            especieSelectExamen.insertAdjacentHTML('beforeend',
              `<option value="${esp.nEspecie}">${esp.cEspecie}</option>`);
          });
          especieSelectExamen.disabled = false;
        }
      });
    });
  }

  if (especieSelectExamen) {
    especieSelectExamen.addEventListener('change', () => {
      const nEspecie = especieSelectExamen.value;
      pruebaSelectExamen.innerHTML = '<option value="">-- Seleccione prueba --</option>';
      pruebaSelectExamen.disabled = true;
      if (previewExamen) previewExamen.style.display = 'none';

      if (!nEspecie) return;

      fetch('controladores/prueba.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({accion: 'listarPorEspecie', nEspecie})
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          data.data.forEach(pru => {
            pruebaSelectExamen.insertAdjacentHTML('beforeend',
              `<option value="${pru.nPrueba}" data-foto="${pru.cFoto || ''}">${pru.cBacteria}</option>`);
          });
          pruebaSelectExamen.disabled = false;
        }
      });
    });
  }

  if (pruebaSelectExamen) {
    pruebaSelectExamen.addEventListener('change', () => {
      const selectedOption = pruebaSelectExamen.options[pruebaSelectExamen.selectedIndex];
      const foto = selectedOption ? selectedOption.dataset.foto : null;
      if (foto && previewExamen) {
        previewExamen.src = 'uploads/' + foto;
        previewExamen.style.display = 'block';
      } else if (previewExamen) {
        previewExamen.style.display = 'none';
      }
    });
  }
});