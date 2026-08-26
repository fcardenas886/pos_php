<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Notas de Pedido</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Lo acordado con el proveedor (cantidad y costo) antes de que llegue la mercadería</p>
  </div>
  <button onclick="abrirModalNotaPedido()" class="btn btn-primary">
    <i class="fa-solid fa-file-signature"></i> Nueva Nota de Pedido
  </button>
</div>

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
      </tr>
    </thead>
    <tbody>
      <?php if (empty($notasPendientes)): ?>
        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay notas de pedido pendientes. Cuando recibas mercadería en <a href="compras.php">Recepción de Compras</a>, vas a poder elegir una nota pendiente para precargarla.</td></tr>
      <?php else: ?>
        <?php foreach ($notasPendientes as $np): ?>
          <tr>
            <td>#<?= $np['NotaPedidoID'] ?><?= $np['NotaPedidoOrigenID'] ? ' <span style="font-size:0.75rem; color: var(--text-muted);">(de #'.$np['NotaPedidoOrigenID'].')</span>' : '' ?></td>
            <td><?= date('d/m/Y H:i', strtotime($np['FechaPedido'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($np['Proveedor']) ?></td>
            <td><?= htmlspecialchars($np['NumeroDocumento'] ?: 'Sin N° Doc') ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($np['Items'] ?: '') ?></td>
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
      </tr>
    </thead>
    <tbody>
      <?php if (empty($historialNotas)): ?>
        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">Aún no hay notas de pedido recibidas.</td></tr>
      <?php else: ?>
        <?php foreach ($historialNotas as $np): ?>
          <tr>
            <td>#<?= $np['NotaPedidoID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($np['FechaPedido'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($np['Proveedor']) ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($np['Items'] ?: '') ?></td>
            <td><span class="badge <?= $np['Estado'] === 'Recibida' ? 'badge-success' : 'badge-danger' ?>"><?= htmlspecialchars($np['Estado']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
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
