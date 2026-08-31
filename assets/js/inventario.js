// Toma de inventario físico: crear, contar por categoría, comparar y procesar el ajuste.
let invData = null;          // { inventario, productos, conteo }
let invEditados = {};        // ProductoID -> cantidad física escrita (string)

function fmtN(n) { return new Intl.NumberFormat('es-CL').format(n); }
function escH(s) {
  return String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}
const EST_BADGE = { 'Borrador': 'badge-warning', 'Procesado': 'badge-success' };

document.addEventListener('DOMContentLoaded', () => {
  if (window.INVENTARIO_ID) {
    abrirConteo(window.INVENTARIO_ID);
  } else {
    cargarLista();
  }
  const f = document.getElementById('tomaFecha');
  if (f) {
    const d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    f.value = d.toISOString().slice(0, 16);
  }
});

/* ---------- LISTA ---------- */
async function cargarLista() {
  const tbody = document.getElementById('invTbody');
  try {
    const res = await fetch('api/inventario.php?action=list');
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    if (data.inventarios.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Aún no hay tomas de inventario.</td></tr>';
      return;
    }
    tbody.innerHTML = data.inventarios.map(i => `
      <tr>
        <td>#${i.InventarioID}</td>
        <td style="font-weight:600;color:#fff;">${escH(i.Nombre)}</td>
        <td>${escH((i.FechaHoraInventario || '').replace('T', ' '))}</td>
        <td><span class="badge ${EST_BADGE[i.Estado] || 'badge-secondary'}">${escH(i.Estado)}</span></td>
        <td style="text-align:center;">${i.Contados}</td>
        <td style="text-align:center;font-weight:700;${Number(i.DifTotal) > 0 ? 'color:var(--warning);' : ''}">${fmtN(i.DifTotal)}</td>
        <td style="color:var(--text-muted);font-size:0.85rem;">${escH(i.Usuario)}</td>
        <td style="text-align:center;">
          <a href="inventario.php?id=${i.InventarioID}" class="btn ${i.Estado === 'Borrador' ? 'btn-primary' : 'btn-secondary'}" style="padding:0.3rem 0.7rem;font-size:0.8rem;">
            <i class="fa-solid fa-${i.Estado === 'Borrador' ? 'pen-to-square' : 'eye'}"></i> ${i.Estado === 'Borrador' ? 'Contar' : 'Ver'}
          </a>
        </td>
      </tr>`).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--danger);padding:2rem;">Error: ${escH(err.message)}</td></tr>`;
  }
}

function abrirNuevaToma() { document.getElementById('nuevaTomaModal').style.display = 'flex'; }

async function crearToma() {
  const nombre = document.getElementById('tomaNombre').value.trim();
  const fecha = document.getElementById('tomaFecha').value;
  if (!nombre) { toast('Ponle un nombre a la toma.', 'warn'); return; }
  const btn = document.getElementById('btnCrearToma');
  btn.disabled = true;
  try {
    const res = await fetch('api/inventario.php?action=create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({ nombre, fecha_hora: fecha }),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    window.location.href = 'inventario.php?id=' + data.inventario_id;
  } catch (err) {
    toast('Error: ' + err.message, 'error');
    btn.disabled = false;
  }
}

/* ---------- CONTEO ---------- */
async function abrirConteo(id) {
  document.getElementById('invListaView').style.display = 'none';
  document.getElementById('invConteoView').style.display = 'block';
  document.getElementById('invConteoBody').innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">Cargando&hellip;</td></tr>';

  try {
    const res = await fetch('api/inventario.php?action=get&id=' + id);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    invData = data;
    invEditados = {};

    const inv = data.inventario;
    const esBorrador = inv.Estado === 'Borrador';
    document.getElementById('invConteoTitulo').textContent = `#${inv.InventarioID} · ${inv.Nombre}`;
    document.getElementById('invConteoMeta').textContent =
      `${(inv.FechaHoraInventario || '').replace('T', ' ')} · ${inv.Usuario} · ${inv.Estado}` +
      (inv.FechaProcesamiento ? ` · procesado ${inv.FechaProcesamiento}` : '');

    const acc = document.getElementById('invConteoAcciones');
    if (esBorrador) {
      acc.innerHTML = `
        <button onclick="guardarConteo()" class="btn btn-secondary"><i class="fa-solid fa-floppy-disk"></i> Guardar conteo</button>
        <button onclick="procesarInventario()" class="btn btn-primary"><i class="fa-solid fa-check-double"></i> Procesar y ajustar stock</button>`;
    } else {
      acc.innerHTML = '<span class="badge badge-success" style="align-self:center;">Procesado — stock ya ajustado</span>';
    }

    renderConteo();
  } catch (err) {
    document.getElementById('invConteoBody').innerHTML =
      `<tr><td colspan="5" style="text-align:center;color:var(--danger);padding:2rem;">Error: ${escH(err.message)}</td></tr>`;
  }
}

function cantFisicaDe(pid) {
  if (Object.prototype.hasOwnProperty.call(invEditados, pid)) return invEditados[pid];
  const c = invData.conteo[pid];
  return c ? String(parseFloat(c.CantidadFisica)) : '';
}

function renderConteo() {
  if (!invData) return;
  const esBorrador = invData.inventario.Estado === 'Borrador';
  const cat = document.getElementById('invFiltroCat').value;
  const q = document.getElementById('invBuscar').value.trim().toLowerCase();
  const soloPend = document.getElementById('invSoloPendientes').checked;

  let contados = 0, conDif = 0;
  const rows = invData.productos.filter(p => {
    if (cat && String(p.CategoriaID) !== cat) return false;
    if (q && !p.Nombre.toLowerCase().includes(q) && !(p.CodigoBarras || '').toLowerCase().includes(q)) return false;
    const tiene = cantFisicaDe(p.ProductoID) !== '';
    if (soloPend && tiene) return false;
    return true;
  }).map(p => {
    const val = cantFisicaDe(p.ProductoID);
    const sis = parseFloat(p.Stock);
    let difCell = '<span style="color:var(--text-muted);">—</span>';
    if (val !== '') {
      contados++;
      const dif = parseFloat(val) - sis;
      if (dif !== 0) conDif++;
      const color = dif === 0 ? 'var(--success)' : (dif > 0 ? '#818cf8' : 'var(--danger)');
      difCell = `<span style="font-weight:700;color:${color};">${dif > 0 ? '+' : ''}${(Math.round(dif * 1000) / 1000)}</span>`;
    }
    const input = esBorrador
      ? `<input type="number" step="0.001" min="0" value="${escH(val)}" data-pid="${p.ProductoID}"
             onchange="setFisica(${p.ProductoID}, this.value)" class="form-control" style="padding:0.35rem 0.5rem;text-align:center;">`
      : `<strong>${val === '' ? '—' : val}</strong>`;
    return `
      <tr>
        <td style="font-weight:600;color:#fff;">${escH(p.Nombre)}</td>
        <td style="color:var(--text-muted);font-size:0.85rem;">${escH(p.Categoria)}</td>
        <td style="text-align:center;">${parseFloat(p.Stock)}</td>
        <td style="text-align:center;">${input}</td>
        <td style="text-align:center;">${difCell}</td>
      </tr>`;
  }).join('');

  document.getElementById('invConteoBody').innerHTML = rows ||
    '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">Sin productos para este filtro.</td></tr>';
  document.getElementById('invConteoResumen').textContent =
    `${contados} contado(s) · ${conDif} con diferencia`;
}

function setFisica(pid, val) {
  invEditados[pid] = val.trim();
  renderConteo();
}

function itemsParaGuardar() {
  const items = [];
  for (const pid in invEditados) {
    const v = invEditados[pid];
    if (v !== '') items.push({ producto_id: parseInt(pid), cantidad_fisica: parseFloat(v) });
  }
  return items;
}

async function guardarConteo(silencioso) {
  const items = itemsParaGuardar();
  if (items.length === 0) {
    if (!silencioso) toast('No hay conteos nuevos para guardar.', 'info');
    return true;
  }
  try {
    const res = await fetch('api/inventario.php?action=save_conteo', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({ inventario_id: invData.inventario.InventarioID, items }),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    // fusionar lo guardado al estado local
    items.forEach(it => {
      invData.conteo[it.producto_id] = {
        ProductoID: it.producto_id, CantidadFisica: it.cantidad_fisica,
        StockSistemaAlMomento: null, Diferencia: null,
      };
    });
    invEditados = {};
    if (!silencioso) { toast(`Conteo guardado (${data.guardados}).`, 'success'); renderConteo(); }
    return true;
  } catch (err) {
    toast('Error al guardar: ' + err.message, 'error');
    return false;
  }
}

async function procesarInventario() {
  if (!await guardarConteo(true)) return;

  const contados = Object.keys(invData.conteo).length;
  if (contados === 0) { toast('Cuenta al menos un producto antes de procesar.', 'warn'); return; }

  const ok = await confirmDialog({
    title: 'Procesar inventario',
    message: `Se generará un ajuste de stock con las diferencias de ${contados} producto(s) contado(s). Esta acción no se puede deshacer.`,
    confirmText: 'Procesar y ajustar', danger: true,
  });
  if (!ok) return;

  try {
    const res = await fetch('api/inventario.php?action=procesar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({ inventario_id: invData.inventario.InventarioID }),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    toast(`Inventario procesado: ${data.productos_ajustados} ajustado(s), ${data.sin_diferencia} sin diferencia.`, 'success');
    setTimeout(() => { window.location.href = 'inventario.php?id=' + invData.inventario.InventarioID; }, 900);
  } catch (err) {
    toast('Error al procesar: ' + err.message, 'error');
  }
}
