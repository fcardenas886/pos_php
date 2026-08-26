<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Notas de Pedido</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Lo acordado con el proveedor (cantidad y costo) antes de que llegue la mercadería</p>
  </div>
  <button onclick="abrirModalNotaPedido()" class="btn btn-primary">
    <i class="fa-solid fa-file-signature"></i> Nueva Nota de Pedido
  </button>
</div>

<form method="GET" action="notaspedido.php" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
  <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Buscar por N° de nota, proveedor o N° de documento..." style="max-width: 420px;">
  <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
  <?php if ($q !== ''): ?>
    <a href="notaspedido.php" class="btn btn-secondary">Limpiar</a>
  <?php endif; ?>
</form>

<div class="table-card" style="margin-bottom: 1.5rem;">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Pendientes de Recibir</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($notasPendientes) ?> notas</span>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th>N° Nota</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th>N° Doc</th>
        <th>Productos Pedidos</th>
        <th style="width: 100px;">Detalle</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($notasPendientes)): ?>
        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
          <?= $q !== '' ? 'No hay notas pendientes que coincidan con la búsqueda.' : 'No hay notas de pedido pendientes. Cuando recibas mercadería en <a href="compras.php">Recepción de Compras</a>, vas a poder elegir una nota pendiente para precargarla.' ?>
        </td></tr>
      <?php else: ?>
        <?php foreach ($notasPendientes as $np): ?>
          <tr>
            <td>#<?= $np['NotaPedidoID'] ?><?= $np['NotaPedidoOrigenID'] ? ' <span style="font-size:0.75rem; color: var(--text-muted);">(de #'.$np['NotaPedidoOrigenID'].')</span>' : '' ?></td>
            <td><?= date('d/m/Y H:i', strtotime($np['FechaPedido'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($np['Proveedor']) ?></td>
            <td><?= htmlspecialchars($np['NumeroDocumento'] ?: 'Sin N° Doc') ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($np['Items'] ?: '') ?></td>
            <td>
              <button onclick="verDetalleNotaPedido(<?= $np['NotaPedidoID'] ?>)" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                <i class="fa-solid fa-eye"></i> Ver
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial (Recibidas / Canceladas)</h2>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th>N° Nota</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th>Productos Pedidos</th>
        <th>Estado</th>
        <th style="width: 100px;">Detalle</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($historialNotas)): ?>
        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;"><?= $q !== '' ? 'No hay notas en el historial que coincidan con la búsqueda.' : 'Aún no hay notas de pedido recibidas.' ?></td></tr>
      <?php else: ?>
        <?php foreach ($historialNotas as $np): ?>
          <tr>
            <td>#<?= $np['NotaPedidoID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($np['FechaPedido'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($np['Proveedor']) ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($np['Items'] ?: '') ?></td>
            <td><span class="badge <?= $np['Estado'] === 'Recibida' ? 'badge-success' : 'badge-danger' ?>"><?= htmlspecialchars($np['Estado']) ?></span></td>
            <td>
              <button onclick="verDetalleNotaPedido(<?= $np['NotaPedidoID'] ?>)" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                <i class="fa-solid fa-eye"></i> Ver
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Detalle de Nota de Pedido -->
<div id="detalleNotaPedidoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1100; justify-content: center; align-items: flex-start; overflow-y: auto; padding: 1.5rem 1rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 620px; padding: 1.75rem; box-shadow: var(--shadow-lg); margin: 1.5rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-dark); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
      <h2 style="font-size: 1.2rem; font-weight: 700; color: #818cf8; margin: 0;" id="detalleNPTitulo">Detalle de Nota de Pedido</h2>
      <button onclick="cerrarDetalleNotaPedido()" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="detalleNPBody" style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
      <p style="color: var(--text-muted); text-align: center;">Cargando...</p>
    </div>
  </div>
</div>

<!-- Modal Nueva Nota de Pedido -->
<div id="notaPedidoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: flex-start; overflow-y: auto; padding: 1.5rem 1rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 700px; padding: 1.75rem; box-shadow: var(--shadow-lg); margin: 1.5rem auto;">
    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #818cf8; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa-solid fa-file-signature"></i> Nueva Nota de Pedido
    </h2>

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
      <div>
        <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">PROVEEDOR *</label>
        <select id="npProveedor" class="form-control" style="font-size: 0.9rem;">
          <?php foreach ($proveedores as $prov): ?>
            <option value="<?= $prov['ProveedorID'] ?>"><?= htmlspecialchars($prov['RazonSocial']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">N° COTIZACIÓN / REFERENCIA</label>
        <input type="text" id="npNumeroDoc" class="form-control" placeholder="Ej: COT-2049" style="font-size: 0.9rem;">
      </div>
    </div>

    <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border-dark); border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
      <label style="font-size: 0.75rem; color: #818cf8; font-weight: 700; display: block; margin-bottom: 0.75rem; text-transform: uppercase;">Añadir Producto Pedido</label>
      <div style="display: grid; grid-template-columns: 2fr 1fr 1.2fr auto; gap: 0.75rem; align-items: end;">
        <div>
          <label style="font-size: 0.7rem; color: var(--text-muted);">PRODUCTO *</label>
          <select id="npProductoSelect" class="form-control" style="font-size: 0.85rem; padding: 0.35rem 0.5rem;" onchange="actualizarSugerenciaCostoNP()">
            <option value="">-- Selecciona --</option>
            <?php foreach ($productos as $prod): ?>
              <option value="<?= $prod['ProductoID'] ?>" data-costo="<?= $prod['CostoCompra'] ?>">
                <?= htmlspecialchars($prod['Nombre']) ?> (Stock: <?= $prod['Stock'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="font-size: 0.7rem; color: var(--text-muted);">CANTIDAD *</label>
          <input type="number" id="npCantidad" step="0.001" min="0.001" class="form-control" placeholder="Ej: 24" style="font-size: 0.85rem; padding: 0.35rem 0.5rem;">
        </div>
        <div>
          <label style="font-size: 0.7rem; color: var(--text-muted);">COSTO ACORDADO ($) *</label>
          <input type="number" id="npCosto" min="0" class="form-control" placeholder="Ej: 850" style="font-size: 0.85rem; padding: 0.35rem 0.5rem;">
        </div>
        <button type="button" onclick="agregarProductoANotaPedido()" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; height: 35px;">
          <i class="fa-solid fa-plus"></i> Agregar
        </button>
      </div>
    </div>

    <div style="max-height: 220px; overflow-y: auto; border: 1px solid var(--border-dark); border-radius: 8px; margin-bottom: 1.25rem;">
      <table class="table" style="margin: 0; font-size: 0.85rem;">
        <thead style="background: rgba(0,0,0,0.3); position: sticky; top: 0;">
          <tr>
            <th>Producto</th>
            <th style="text-align: right; width: 90px;">Cantidad</th>
            <th style="text-align: right; width: 110px;">Costo Acordado</th>
            <th style="text-align: right; width: 120px;">Subtotal</th>
            <th style="text-align: center; width: 70px;">Quitar</th>
          </tr>
        </thead>
        <tbody id="npGrillaItems">
          <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">No hay productos añadidos a este pedido.</td></tr>
        </tbody>
      </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-dark); padding-top: 1.25rem;">
      <div style="font-size: 1rem; color: var(--success); font-weight: bold;">TOTAL ESTIMADO: <span id="npTotalLabel">$0</span></div>
      <div style="display: flex; gap: 0.75rem;">
        <button type="button" id="btnGuardarNotaPedido" onclick="guardarNotaPedido()" class="btn btn-success" style="padding: 0.6rem 1.5rem;">
          <i class="fa-solid fa-floppy-disk"></i> Guardar Nota de Pedido
        </button>
        <button type="button" onclick="cerrarModalNotaPedido()" class="btn btn-secondary">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<script>
let itemsNotaPedido = [];

async function verDetalleNotaPedido(id) {
  const modal = document.getElementById('detalleNotaPedidoModal');
  const body = document.getElementById('detalleNPBody');
  document.getElementById('detalleNPTitulo').textContent = `Detalle de Nota de Pedido #${id}`;
  body.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Cargando...</p>';
  modal.style.display = 'flex';

  try {
    const res = await fetch(`api/ver_nota_pedido.php?id=${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    const np = data.nota_pedido;
    const badgeClase = np.estado === 'Recibida' ? 'badge-success' : (np.estado === 'Pendiente' ? 'badge-warning' : 'badge-danger');

    let html = `
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: rgba(0,0,0,0.2); padding: 0.85rem; border-radius: 8px;">
        <div><span style="color: var(--text-muted); font-size: 0.75rem;">PROVEEDOR</span><br><strong style="color:#fff;">${escapeHtmlNP(np.proveedor)}</strong></div>
        <div><span style="color: var(--text-muted); font-size: 0.75rem;">ESTADO</span><br><span class="badge ${badgeClase}">${np.estado}</span></div>
        <div><span style="color: var(--text-muted); font-size: 0.75rem;">FECHA</span><br>${np.fecha}</div>
        <div><span style="color: var(--text-muted); font-size: 0.75rem;">N° DOCUMENTO</span><br>${np.numero_documento || 'Sin N° Doc'}</div>
      </div>
    `;

    if (data.nota_origen) {
      html += `<div style="font-size: 0.85rem; color: #a5b4fc;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Generada por lo que faltó recibir de la Nota <a href="#" onclick="verDetalleNotaPedido(${data.nota_origen.id}); return false;">#${data.nota_origen.id}</a> (${data.nota_origen.estado}).</div>`;
    }
    if (data.nota_derivada) {
      html += `<div style="font-size: 0.85rem; color: #fbbf24;"><i class="fa-solid fa-arrow-down-left"></i> Quedó pendiente algo por recibir: se generó la Nota <a href="#" onclick="verDetalleNotaPedido(${data.nota_derivada.id}); return false;">#${data.nota_derivada.id}</a> (${data.nota_derivada.estado}).</div>`;
    }

    html += `
      <table class="table" style="margin: 0; font-size: 0.85rem;">
        <thead>
          <tr><th>Producto</th><th style="text-align:right;">Cant. Pedida</th><th style="text-align:right;">Costo Acordado</th><th style="text-align:right;">Subtotal</th></tr>
        </thead>
        <tbody>
          ${data.detalles.map(d => `
            <tr>
              <td style="font-weight:600; color:#fff;">${escapeHtmlNP(d.nombre)}</td>
              <td style="text-align:right;">${d.cantidad_pedida}</td>
              <td style="text-align:right;">$${new Intl.NumberFormat('es-CL').format(d.costo_acordado)}</td>
              <td style="text-align:right; font-weight:bold; color: var(--success);">$${new Intl.NumberFormat('es-CL').format(Math.round(d.cantidad_pedida * d.costo_acordado))}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;

    if (data.compras_asociadas.length > 0) {
      html += `
        <div>
          <span style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Recibida mediante</span>
          <ul style="margin: 0.4rem 0 0; padding-left: 1.2rem;">
            ${data.compras_asociadas.map(c => `<li>Compra #${c.compra_id} — ${c.fecha} — $${new Intl.NumberFormat('es-CL').format(c.monto_total)}</li>`).join('')}
          </ul>
        </div>
      `;
    } else if (np.estado === 'Pendiente') {
      html += `<div style="color: var(--text-muted); font-size: 0.85rem;">Aún no se ha recibido. Ve a <a href="compras.php">Recepción de Compras</a> para procesarla.</div>`;
    }

    body.innerHTML = html;
  } catch (err) {
    body.innerHTML = `<p style="color: var(--danger); text-align: center;">${err.message}</p>`;
  }
}

function cerrarDetalleNotaPedido() {
  document.getElementById('detalleNotaPedidoModal').style.display = 'none';
}

function escapeHtmlNP(text) {
  return String(text).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}

function abrirModalNotaPedido() {
  itemsNotaPedido = [];
  document.getElementById('npNumeroDoc').value = '';
  renderGrillaNotaPedido();
  document.getElementById('notaPedidoModal').style.display = 'flex';
}

function cerrarModalNotaPedido() {
  document.getElementById('notaPedidoModal').style.display = 'none';
}

function actualizarSugerenciaCostoNP() {
  const sel = document.getElementById('npProductoSelect');
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('npCosto').value = (opt && opt.value !== '') ? (opt.dataset.costo || '0') : '';
}

function fmtNP(val) {
  return '$' + new Intl.NumberFormat('es-CL').format(val);
}

function agregarProductoANotaPedido() {
  const sel = document.getElementById('npProductoSelect');
  const pid = parseInt(sel.value) || 0;
  const opt = sel.options[sel.selectedIndex];
  const cant = parseFloat(document.getElementById('npCantidad').value) || 0;
  const costo = parseInt(document.getElementById('npCosto').value) || 0;

  if (pid <= 0 || cant <= 0 || costo < 0) {
    alert('Selecciona un producto, cantidad válida y costo acordado.');
    return;
  }

  const nombre = opt.text.split(' (')[0].trim();
  const existente = itemsNotaPedido.findIndex(i => i.producto_id === pid);
  if (existente !== -1) {
    itemsNotaPedido[existente].cantidad = cant;
    itemsNotaPedido[existente].costo_acordado = costo;
  } else {
    itemsNotaPedido.push({ producto_id: pid, nombre, cantidad: cant, costo_acordado: costo });
  }

  sel.value = '';
  document.getElementById('npCantidad').value = '';
  document.getElementById('npCosto').value = '';
  renderGrillaNotaPedido();
}

function quitarDeNotaPedido(index) {
  itemsNotaPedido.splice(index, 1);
  renderGrillaNotaPedido();
}

function renderGrillaNotaPedido() {
  const tbody = document.getElementById('npGrillaItems');
  if (itemsNotaPedido.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">No hay productos añadidos a este pedido.</td></tr>`;
    document.getElementById('npTotalLabel').textContent = '$0';
    return;
  }

  let total = 0;
  tbody.innerHTML = itemsNotaPedido.map((item, idx) => {
    const subtotal = Math.round(item.cantidad * item.costo_acordado);
    total += subtotal;
    return `
      <tr>
        <td style="font-weight: 600; color: #fff;">${item.nombre}</td>
        <td style="text-align: right;">${item.cantidad}</td>
        <td style="text-align: right;">${fmtNP(item.costo_acordado)}</td>
        <td style="text-align: right; font-weight: bold; color: var(--success);">${fmtNP(subtotal)}</td>
        <td style="text-align: center;">
          <button type="button" onclick="quitarDeNotaPedido(${idx})" class="btn btn-secondary" style="padding: 0.15rem 0.4rem; font-size: 0.75rem; color: var(--danger);">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
  document.getElementById('npTotalLabel').textContent = fmtNP(total);
}

async function guardarNotaPedido() {
  if (itemsNotaPedido.length === 0) {
    alert('Agrega al menos un producto al pedido.');
    return;
  }

  const btn = document.getElementById('btnGuardarNotaPedido');
  btn.disabled = true;

  try {
    const res = await fetch('api/registrar_nota_pedido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({
        proveedor_id: parseInt(document.getElementById('npProveedor').value),
        numero_documento: document.getElementById('npNumeroDoc').value.trim(),
        items: itemsNotaPedido.map(i => ({ producto_id: i.producto_id, cantidad: i.cantidad, costo_acordado: i.costo_acordado }))
      })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    alert(`Nota de Pedido #${data.nota_pedido_id} guardada. Cuando llegue la mercadería, elígela desde Recepción de Compras.`);
    location.reload();
  } catch (err) {
    alert('Error al guardar la nota de pedido: ' + err.message);
  } finally {
    btn.disabled = false;
  }
}
</script>
