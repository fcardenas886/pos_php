// Pantalla de Cotizaciones: listar, crear, ver, anular y cargar en el POS.
let cotizItems = [];

document.addEventListener('DOMContentLoaded', cargarCotizaciones);

function fmt(n) { return new Intl.NumberFormat('es-CL').format(Math.round(n || 0)); }
function esc(s) {
  return String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}

const ESTADO_BADGE = {
  'Pendiente': 'badge-warning',
  'Restaurada': 'badge-secondary',
  'Convertida': 'badge-success',
  'Anulada': 'badge-danger',
};

async function cargarCotizaciones() {
  const tbody = document.getElementById('cotizTbody');
  const estado = document.getElementById('filtroEstado').value;
  tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Cargando&hellip;</td></tr>';

  try {
    const res = await fetch(`api/cotizaciones.php?action=list&estado=${encodeURIComponent(estado)}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    document.getElementById('cotizTotalInfo').textContent = `${data.cotizaciones.length} cotización(es)`;

    if (data.cotizaciones.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">No hay cotizaciones para este filtro.</td></tr>';
      return;
    }

    tbody.innerHTML = data.cotizaciones.map(c => {
      const puedeUsar = c.Estado === 'Pendiente' || c.Estado === 'Restaurada';
      return `
        <tr>
          <td>#${c.CotizacionID}</td>
          <td>${esc(c.FechaCotizacion)}</td>
          <td style="font-weight:600;color:#fff;">${esc(c.Cliente)}</td>
          <td style="text-align:center;">${c.Items}</td>
          <td style="text-align:right;font-weight:700;color:var(--success);">$${fmt(c.Total)}</td>
          <td><span class="badge ${ESTADO_BADGE[c.Estado] || 'badge-secondary'}">${esc(c.Estado)}</span></td>
          <td style="color:var(--text-muted);font-size:0.85rem;">${esc(c.Usuario)}</td>
          <td style="text-align:center;">
            <div style="display:flex;gap:0.35rem;justify-content:center;flex-wrap:wrap;">
              <button onclick="verCotizacion(${c.CotizacionID})" class="btn btn-secondary" style="padding:0.25rem 0.5rem;font-size:0.78rem;" title="Ver detalle"><i class="fa-solid fa-eye"></i></button>
              ${puedeUsar ? `<button onclick="cargarEnPOS(${c.CotizacionID})" class="btn btn-primary" style="padding:0.25rem 0.6rem;font-size:0.78rem;"><i class="fa-solid fa-cash-register"></i> A la caja</button>` : ''}
              ${c.Estado !== 'Convertida' && c.Estado !== 'Anulada' ? `<button onclick="anularCotizacion(${c.CotizacionID})" class="btn btn-secondary" style="padding:0.25rem 0.5rem;font-size:0.78rem;color:var(--danger);" title="Anular"><i class="fa-solid fa-ban"></i></button>` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--danger);padding:2rem;">Error: ${esc(err.message)}</td></tr>`;
  }
}

function cargarEnPOS(id) {
  window.location.href = 'pos.php?cotizacion=' + id;
}

async function anularCotizacion(id) {
  const ok = await confirmDialog({
    title: 'Anular cotización',
    message: `¿Anular la cotización #${id}? No se podrá cargar en la caja.`,
    confirmText: 'Anular', danger: true,
  });
  if (!ok) return;
  try {
    const res = await fetch('api/cotizaciones.php?action=anular', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    toast('Cotización anulada.', 'success');
    cargarCotizaciones();
  } catch (err) {
    toast('Error: ' + err.message, 'error');
  }
}

async function verCotizacion(id) {
  const modal = document.getElementById('verCotizModal');
  const body = document.getElementById('verCotizBody');
  document.getElementById('verCotizTitulo').textContent = `Cotización #${id}`;
  body.innerHTML = '<p style="color:var(--text-muted);">Cargando&hellip;</p>';
  modal.style.display = 'flex';

  try {
    const res = await fetch(`api/cotizaciones.php?action=get&id=${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    const c = data.cotizacion;
    const filas = data.detalles.map(d => `
      <tr>
        <td>${esc(d.Nombre)}</td>
        <td style="text-align:center;">${parseFloat(d.Cantidad)}</td>
        <td style="text-align:right;">$${fmt(d.PrecioUnitario)}</td>
        <td style="text-align:right;">$${fmt(d.Subtotal)}</td>
      </tr>`).join('');

    body.innerHTML = `
      <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1rem;font-size:0.9rem;">
        <div><span style="color:var(--text-muted);">Cliente:</span> <strong>${esc(c.Cliente)}</strong></div>
        <div><span style="color:var(--text-muted);">Fecha:</span> ${esc(c.FechaCotizacion)}</div>
        <div><span style="color:var(--text-muted);">Estado:</span> <span class="badge ${ESTADO_BADGE[c.Estado] || 'badge-secondary'}">${esc(c.Estado)}</span></div>
        <div><span style="color:var(--text-muted);">Vendedor:</span> ${esc(c.Usuario)}</div>
      </div>
      <table class="table" style="font-size:0.88rem;">
        <thead><tr><th>Producto</th><th style="text-align:center;">Cant.</th><th style="text-align:right;">Precio</th><th style="text-align:right;">Subtotal</th></tr></thead>
        <tbody>${filas}</tbody>
      </table>
      <div style="display:flex;justify-content:space-between;border-top:1px dashed var(--border-dark);padding-top:0.6rem;margin-top:0.4rem;font-size:1.05rem;font-weight:700;">
        <span>TOTAL</span><span style="color:var(--success);">$${fmt(c.Total)}</span>
      </div>
      ${(c.Estado === 'Pendiente' || c.Estado === 'Restaurada')
        ? `<button onclick="cargarEnPOS(${id})" class="btn btn-primary btn-block" style="margin-top:1rem;padding:0.75rem;"><i class="fa-solid fa-cash-register"></i> Cargar en la caja</button>`
        : ''}
    `;
  } catch (err) {
    body.innerHTML = `<p style="color:var(--danger);">Error: ${esc(err.message)}</p>`;
  }
}

/* ---- Nueva cotización ---- */
function abrirNuevaCotizacion() {
  cotizItems = [];
  document.getElementById('cotizClienteSelect').value = '';
  document.getElementById('cotizAddProducto').value = '';
  document.getElementById('cotizAddCant').value = '1';
  document.getElementById('cotizAddPrecio').value = '0';
  renderCotizItems();
  document.getElementById('nuevaCotizModal').style.display = 'flex';
}
function cerrarNuevaCotiz() {
  document.getElementById('nuevaCotizModal').style.display = 'none';
}

document.addEventListener('change', (e) => {
  if (e.target && e.target.id === 'cotizAddProducto') {
    const opt = e.target.selectedOptions[0];
    if (opt && opt.dataset.precio) document.getElementById('cotizAddPrecio').value = opt.dataset.precio;
  }
});

function cotizAgregarItem() {
  const sel = document.getElementById('cotizAddProducto');
  const opt = sel.selectedOptions[0];
  const pid = parseInt(sel.value);
  const cant = parseFloat(document.getElementById('cotizAddCant').value);
  const precio = parseInt(document.getElementById('cotizAddPrecio').value) || 0;

  if (!pid) { toast('Selecciona un producto.', 'warn'); return; }
  if (!(cant > 0)) { toast('Cantidad inválida.', 'warn'); return; }

  const existe = cotizItems.find(i => i.producto_id === pid);
  if (existe) {
    existe.cantidad += cant;
  } else {
    cotizItems.push({ producto_id: pid, nombre: opt.dataset.nombre, cantidad: cant, precio });
  }
  document.getElementById('cotizAddCant').value = '1';
  renderCotizItems();
}

function cotizQuitar(pid) {
  cotizItems = cotizItems.filter(i => i.producto_id !== pid);
  renderCotizItems();
}

function renderCotizItems() {
  const body = document.getElementById('cotizItemsBody');
  if (cotizItems.length === 0) {
    body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:1rem;">Sin productos aún.</td></tr>';
    document.getElementById('cotizTotalNuevo').textContent = '$0';
    return;
  }
  let total = 0;
  body.innerHTML = cotizItems.map(i => {
    const sub = Math.round(i.cantidad * i.precio);
    total += sub;
    return `
      <tr>
        <td>${esc(i.nombre)}</td>
        <td style="text-align:center;">${i.cantidad}</td>
        <td style="text-align:right;">$${fmt(i.precio)}</td>
        <td style="text-align:right;">$${fmt(sub)}</td>
        <td style="text-align:center;"><button onclick="cotizQuitar(${i.producto_id})" class="btn btn-secondary" style="padding:0.15rem 0.4rem;color:var(--danger);">&times;</button></td>
      </tr>`;
  }).join('');
  document.getElementById('cotizTotalNuevo').textContent = '$' + fmt(total);
}

async function guardarNuevaCotizacion() {
  if (cotizItems.length === 0) { toast('Agrega al menos un producto.', 'warn'); return; }
  const btn = document.getElementById('btnGuardarCotiz');
  btn.disabled = true;
  try {
    const res = await fetch('api/cotizaciones.php?action=save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({
        cliente_id: document.getElementById('cotizClienteSelect').value || null,
        items: cotizItems.map(i => ({ producto_id: i.producto_id, cantidad: i.cantidad, precio: i.precio })),
      }),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    toast(`Cotización #${data.cotizacion_id} guardada.`, 'success');
    cerrarNuevaCotiz();
    document.getElementById('filtroEstado').value = 'Pendiente';
    cargarCotizaciones();
  } catch (err) {
    toast('Error al guardar: ' + err.message, 'error');
  } finally {
    btn.disabled = false;
  }
}
