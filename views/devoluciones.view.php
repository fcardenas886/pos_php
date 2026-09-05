<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Devoluciones y Notas de Crédito</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Reembolso de productos a clientes y reintegración de stock a Kardex</p>
  </div>
  <div style="display: flex; gap: 0.75rem;">
    <button onclick="abrirModalDevolucion('Nota de Credito')" class="btn btn-primary">
      <i class="fa-solid fa-file-invoice-dollar"></i> Registrar Nota de Crédito
    </button>
    <button onclick="abrirModalDevolucion('Cambio de Mercaderia')" class="btn btn-success">
      <i class="fa-solid fa-right-left"></i> Registrar Ticket de Cambio
    </button>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #34d399; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
    <i class="fa-solid fa-circle-check"></i> <?= $message ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #f87171; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.5rem;">

  <!-- Historial de Devoluciones -->
  <div class="table-card" style="margin: 0;">
    <div class="table-header">
      <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Devoluciones</h2>
      <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($devoluciones) ?> registros</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>N° Dev.</th>
          <th>Fecha</th>
          <th>N° Venta</th>
          <th>Productos Devueltos</th>
          <th>Método Reembolso</th>
          <th>Monto Devuelto</th>
          <th>Motivo</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($devoluciones)): ?>
          <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay devoluciones registradas.</td></tr>
        <?php else: ?>
          <?php foreach ($devoluciones as $d): ?>
            <tr>
              <td>#<?= $d['DevolucionID'] ?></td>
              <td><?= date('d/m/Y H:i', strtotime($d['FechaDevolucion'])) ?></td>
              <td><strong>#<?= $d['VentaID'] ?></strong></td>
              <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($d['ItemsDevueltos'] ?: 'Devolución parcial') ?></td>
              <td><span class="badge badge-warning"><?= htmlspecialchars($d['MetodoDevolucion']) ?></span></td>
              <td style="font-weight: 700; color: var(--danger);"><?= formatCLP($d['MontoDevuelto']) ?></td>
              <td style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($d['Motivo']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Vales y Notas de Crédito Activos -->
  <div class="table-card" style="margin: 0;">
    <div class="table-header">
      <h2 style="font-size: 1.1rem; font-weight: 600; color: #818cf8;">Notas de Crédito y Vales Activos</h2>
      <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($valesActivos) ?> vigentes</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Código</th>
          <th style="text-align: right;">Original</th>
          <th style="text-align: right;">Disponible</th>
          <th style="text-align: center;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($valesActivos)): ?>
          <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay vales activos vigentes.</td></tr>
        <?php else: ?>
          <?php foreach ($valesActivos as $v): ?>
            <tr>
              <td style="font-family: monospace; font-weight: bold; color: #fff; font-size: 0.9rem;"><?= htmlspecialchars($v['CodigoVale']) ?></td>
              <td style="text-align: right; color: var(--text-muted);"><?= formatCLP($v['MontoOriginal']) ?></td>
              <td style="text-align: right; font-weight: 700; color: var(--success);"><?= formatCLP($v['MontoDisponible']) ?></td>
              <td style="text-align: center;">
                <button type="button" class="btn btn-success" style="padding: 0.2rem 0.4rem; font-size: 0.75rem;" onclick="reembolsarVale('<?= htmlspecialchars($v['CodigoVale']) ?>', <?= $v['MontoDisponible'] ?>)">
                  <i class="fa-solid fa-money-bill-transfer"></i> Reembolsar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Nueva Devolución -->
<div id="devModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: flex-start; overflow-y: auto; padding: 1.5rem 1rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 620px; padding: 1.75rem; box-shadow: var(--shadow-lg); margin: 1.5rem auto;">
    <h2 id="modalDevolucionTitle" style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #818cf8;">Procesar Devolución</h2>

    <!-- Paso 1: Buscar Boleta -->
    <div style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 1.25rem;">
      <div style="flex: 1;">
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">N° BOLETA / ID VENTA *</label>
        <input type="number" id="devVentaIdInput" class="form-control" placeholder="Ej: 60" style="font-size: 1.1rem; font-weight: bold;">
      </div>
      <button type="button" onclick="buscarBoletaDevolucion()" class="btn btn-primary" style="padding: 0.65rem 1.25rem;"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
    </div>

    <!-- Mensaje de error dinámico -->
    <div id="devErrorMsg" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #f87171; padding: 0.65rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.25rem;">
    </div>

    <!-- Paso 2 y 3: se muestra al encontrar la boleta -->
    <div id="detallesDevolucionBody" style="display: none; flex-direction: column; gap: 1.25rem; border-top: 1px solid var(--border-dark); padding-top: 1.25rem;">

      <!-- Añadir producto a la devolución (puede agregarse más de uno) -->
      <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border-dark); border-radius: 12px; padding: 1rem;">
        <label style="font-size: 0.75rem; color: #818cf8; font-weight: 700; display: block; margin-bottom: 0.75rem; text-transform: uppercase;">Añadir Producto a la Devolución</label>
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 0.75rem; align-items: end;">
          <div>
            <label style="font-size: 0.7rem; color: var(--text-muted);">PRODUCTO *</label>
            <select id="devProductoSelect" class="form-control" style="font-size: 0.85rem; padding: 0.35rem 0.5rem;" onchange="actualizarInfoProductoDev()"></select>
          </div>
          <div>
            <label style="font-size: 0.7rem; color: var(--text-muted);">CANTIDAD *</label>
            <input type="number" step="0.001" id="devCantidadInput" class="form-control" value="1" min="0.001" style="font-size: 0.85rem; padding: 0.35rem 0.5rem; font-weight: 700;">
            <span style="font-size: 0.7rem; color: #818cf8; font-weight: 600;" id="devMaxCantLabel"></span>
          </div>
          <button type="button" onclick="agregarProductoADevolucion()" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; height: 35px;">
            <i class="fa-solid fa-plus"></i> Agregar
          </button>
        </div>
      </div>

      <!-- Grilla de productos a devolver -->
      <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-dark); border-radius: 8px;">
        <table class="table" style="margin: 0; font-size: 0.85rem;">
          <thead style="background: rgba(0,0,0,0.3); position: sticky; top: 0;">
            <tr>
              <th>Producto</th>
              <th style="text-align: right; width: 90px;">Cantidad</th>
              <th style="text-align: right; width: 120px;">Subtotal</th>
              <th style="text-align: center; width: 70px;">Quitar</th>
            </tr>
          </thead>
          <tbody id="devGrillaItems">
            <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">Aún no agregas productos a esta devolución.</td></tr>
          </tbody>
        </table>
      </div>

      <div style="display: flex; justify-content: flex-end; font-size: 1rem; font-weight: bold;">
        Total a Devolver: <span id="devTotalLabel" style="color: var(--danger); margin-left: 0.5rem;">$0</span>
      </div>

      <div id="containerMetodoSelect" style="display: grid; grid-template-columns: 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">PROCESO / REEMBOLSO *</label>
          <select id="devMetodoSelect" class="form-control" required>
            <option value="Nota de Credito">Nota de Crédito — vale para usar luego o reembolsar posterior</option>
            <option value="Efectivo">Efectivo — se retira de la caja ahora mismo</option>
            <option value="Tarjeta">Tarjeta — se reversa en Transbank (no toca caja)</option>
          </select>
        </div>
      </div>



      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">MOTIVO DE LA DEVOLUCIÓN *</label>
        <input type="text" id="devMotivoInput" class="form-control" placeholder="Ej: Producto en mal estado / Cambio de producto">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="button" id="btnConfirmarDevolucion" onclick="confirmarDevolucion()" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Confirmar Devolución</button>
        <button type="button" onclick="cerrarDevolucionModal()" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Vale de Devolución Generado (Copia Cliente) -->
<div id="valeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1100; align-items: center; justify-content: center;">
  <div style="background: #fff; color: #000; width: 340px; border-radius: 12px; padding: 1.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: monospace; text-align: center;">
    <div style="border-bottom: 1px dashed #000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
      <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.2rem;">MINIMARKET</h2>
      <p style="font-size: 0.8rem;" id="valeTitulo">Vale de Devolución</p>
      <p style="font-size: 0.75rem; color: #555;" id="valeFecha"></p>
    </div>
    <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">Código de Canje:</div>
    <div style="font-size: 1.6rem; font-weight: bold; background: #eee; padding: 0.5rem; border-radius: 6px; letter-spacing: 2px; margin-bottom: 1rem; color: #111;" id="valeCodigoLabel"></div>
    <div style="font-size: 1rem; margin-bottom: 1.5rem;">
      Monto Disponible: <strong style="font-size: 1.3rem;" id="valeMontoLabel"></strong>
    </div>
    <div style="display: flex; gap: 0.5rem;">
      <button onclick="window.print()" class="btn btn-primary btn-block" style="font-size: 0.85rem; padding: 0.5rem;">
        <i class="fa-solid fa-print"></i> Imprimir Vale
      </button>
      <button onclick="document.getElementById('valeModal').style.display='none'; location.reload();" class="btn btn-secondary btn-block" style="font-size: 0.85rem; padding: 0.5rem; background: #eee; color: #000; border: 1px solid #ccc;">
        Cerrar
      </button>
    </div>
  </div>
</div>

<script>
let ventaEnDevolucion = null;
let detallesVentaDevolucion = [];
let itemsDevolucion = [];
let flujoActual = 'Nota de Credito';

function abrirModalDevolucion(tipo) {
  flujoActual = tipo || 'Nota de Credito';
  
  document.getElementById('devVentaIdInput').value = '';
  document.getElementById('devErrorMsg').style.display = 'none';
  document.getElementById('detallesDevolucionBody').style.display = 'none';
  itemsDevolucion = [];
  ventaEnDevolucion = null;
  
  const titleEl = document.getElementById('modalDevolucionTitle');
  const containerMetodo = document.getElementById('containerMetodoSelect');
  const btnEl = document.getElementById('btnConfirmarDevolucion');
  
  if (flujoActual === 'Cambio de Mercaderia') {
    if (titleEl) titleEl.textContent = 'Registrar Ticket de Cambio';
    if (containerMetodo) containerMetodo.style.display = 'none';
    if (btnEl) btnEl.innerHTML = '<i class="fa-solid fa-right-left"></i> Generar Ticket de Cambio';
  } else {
    if (titleEl) titleEl.textContent = 'Procesar Nota de Crédito';
    if (containerMetodo) containerMetodo.style.display = 'grid';
    if (btnEl) btnEl.innerHTML = '<i class="fa-solid fa-file-invoice-dollar"></i> Confirmar Nota de Crédito';
  }

  document.getElementById('devModal').style.display = 'flex';
}

function cerrarDevolucionModal() {
  document.getElementById('devModal').style.display = 'none';
}

function fmtDev(val) {
  return '$' + new Intl.NumberFormat('es-CL').format(val);
}

async function buscarBoletaDevolucion() {
  const ventaId = document.getElementById('devVentaIdInput').value.trim();
  const errorEl = document.getElementById('devErrorMsg');
  const bodyEl = document.getElementById('detallesDevolucionBody');
  const selectEl = document.getElementById('devProductoSelect');

  errorEl.style.display = 'none';
  bodyEl.style.display = 'none';

  if (!ventaId) {
    errorEl.textContent = 'Ingresa un número de boleta válido.';
    errorEl.style.display = 'block';
    return;
  }

  try {
    const res = await fetch(`api/ver_venta.php?id=${ventaId}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    if (data.venta.estado === 'Anulada') {
      throw new Error('La boleta ingresada ya se encuentra anulada.');
    }

    if (data.detalles.length === 0) {
      throw new Error('La boleta no registra ningún producto.');
    }

    if (data.detalles.every(i => i.disponible <= 0)) {
      throw new Error('Esta boleta ya no tiene productos disponibles para devolver (todo fue devuelto anteriormente).');
    }

    ventaEnDevolucion = ventaId;
    detallesVentaDevolucion = data.detalles;
    itemsDevolucion = [];

    // Poblar select. Los productos ya devueltos por completo se muestran deshabilitados,
    // para que quede claro por qué no se pueden elegir en vez de dejarlo ambiguo.
    selectEl.innerHTML = detallesVentaDevolucion.map(i => `
      <option value="${i.producto_id}" data-disponible="${i.disponible}" data-precio="${i.precio}" ${i.disponible <= 0 ? 'disabled' : ''}>
        ${i.nombre} (Comprado: ${i.cantidad}${i.ya_devuelto > 0 ? `, ya devuelto: ${i.ya_devuelto}` : ''} - Disponible: ${i.disponible})
      </option>
    `).join('');

    const primeraOpcionDisponible = Array.from(selectEl.options).findIndex(o => !o.disabled);
    if (primeraOpcionDisponible >= 0) selectEl.selectedIndex = primeraOpcionDisponible;

    bodyEl.style.display = 'flex';
    actualizarInfoProductoDev();
    renderGrillaDevolucion();

  } catch (err) {
    errorEl.textContent = err.message;
    errorEl.style.display = 'block';
  }
}

function actualizarInfoProductoDev() {
  const select = document.getElementById('devProductoSelect');
  const option = select.options[select.selectedIndex];
  if (!option) return;

  const disponible = parseFloat(option.getAttribute('data-disponible'));
  const yaEnGrilla = itemsDevolucion.find(i => i.producto_id == option.value);
  const restante = disponible - (yaEnGrilla ? yaEnGrilla.cantidad : 0);

  document.getElementById('devCantidadInput').max = restante;
  document.getElementById('devCantidadInput').value = restante > 0 ? restante : '';
  document.getElementById('devMaxCantLabel').textContent = `Máx. disponible: ${restante}`;
}

function agregarProductoADevolucion() {
  const select = document.getElementById('devProductoSelect');
  const option = select.options[select.selectedIndex];
  const cantidad = parseFloat(document.getElementById('devCantidadInput').value) || 0;

  if (!option || option.disabled) {
    alert('Selecciona un producto con unidades disponibles.');
    return;
  }
  const disponible = parseFloat(option.getAttribute('data-disponible'));
  const precio = parseInt(option.getAttribute('data-precio'));
  const productoID = option.value;
  const nombre = option.text.split(' (')[0].trim();

  const yaEnGrilla = itemsDevolucion.find(i => i.producto_id == productoID);
  const yaAgregado = yaEnGrilla ? yaEnGrilla.cantidad : 0;

  if (cantidad <= 0 || (cantidad + yaAgregado) > disponible) {
    alert(`Cantidad inválida. Máximo disponible para este producto: ${disponible - yaAgregado}.`);
    return;
  }

  if (yaEnGrilla) {
    yaEnGrilla.cantidad += cantidad;
    yaEnGrilla.subtotal = Math.round(yaEnGrilla.cantidad * precio);
  } else {
    itemsDevolucion.push({ producto_id: productoID, nombre, cantidad, precio, subtotal: Math.round(cantidad * precio) });
  }

  renderGrillaDevolucion();
  actualizarInfoProductoDev();
}

function quitarDeGrillaDevolucion(index) {
  itemsDevolucion.splice(index, 1);
  renderGrillaDevolucion();
  actualizarInfoProductoDev();
}

function renderGrillaDevolucion() {
  const tbody = document.getElementById('devGrillaItems');
  if (itemsDevolucion.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">Aún no agregas productos a esta devolución.</td></tr>`;
    document.getElementById('devTotalLabel').textContent = '$0';
    return;
  }

  let total = 0;
  tbody.innerHTML = itemsDevolucion.map((item, idx) => {
    total += item.subtotal;
    return `
      <tr>
        <td style="font-weight: 600; color: #fff;">${item.nombre}</td>
        <td style="text-align: right;">${item.cantidad}</td>
        <td style="text-align: right; font-weight: bold; color: var(--danger);">${fmtDev(item.subtotal)}</td>
        <td style="text-align: center;">
          <button type="button" onclick="quitarDeGrillaDevolucion(${idx})" class="btn btn-secondary" style="padding: 0.15rem 0.4rem; font-size: 0.75rem; color: var(--danger);">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
  document.getElementById('devTotalLabel').textContent = fmtDev(total);
}

async function confirmarDevolucion() {
  if (itemsDevolucion.length === 0) {
    alert('Agrega al menos un producto a la devolución.');
    return;
  }
  const metodo = flujoActual === 'Cambio de Mercaderia' ? 'Cambio de Mercaderia' : document.getElementById('devMetodoSelect').value;
  const motivo = document.getElementById('devMotivoInput').value.trim();
  if (!motivo) {
    alert('Ingresa el motivo de la devolución.');
    return;
  }

  const btn = document.getElementById('btnConfirmarDevolucion');
  btn.disabled = true;

  try {
    const res = await fetch('api/registrar_devolucion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({
        venta_id: ventaEnDevolucion,
        items: itemsDevolucion.map(i => ({ producto_id: i.producto_id, cantidad: i.cantidad })),
        metodo_devolucion: metodo,
        motivo: motivo
      })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    cerrarDevolucionModal();

    if (data.vale_codigo) {
      const esCambio = data.vale_codigo.startsWith('TC-');
      document.getElementById('valeTitulo').textContent = esCambio ? 'Ticket de Cambio' : 'Nota de Crédito (Comprobante)';
      document.getElementById('valeFecha').textContent = new Date().toLocaleString('es-CL');
      document.getElementById('valeCodigoLabel').textContent = data.vale_codigo;
      document.getElementById('valeMontoLabel').textContent = fmtDev(data.monto_total);
      document.getElementById('valeModal').style.display = 'flex';
    } else {
      alert(`Devolución registrada exitosamente (ID #${data.devolucion_id}). Se reembolsaron ${fmtDev(data.monto_total)} mediante ${metodo}.`);
      location.reload();
    }
  } catch (err) {
    alert('Error al registrar devolución: ' + err.message);
  } finally {
    btn.disabled = false;
  }
}

async function reembolsarVale(codigo, disponible) {
  const formatCLP = val => '$' + new Intl.NumberFormat('es-CL').format(val);
  if (!confirm(`¿Confirmas el reembolso físico en Efectivo del vale ${codigo} por un monto de ${formatCLP(disponible)}?\n\nEsto registrará un EGRESO de dinero en la caja del turno activo.`)) {
    return;
  }

  const pass = prompt('Clave de Supervisor / Administrador para autorizar el reembolso (dejar en blanco si eres Admin):', '');
  if (pass === null) return;

  try {
    const res = await fetch('api/reembolsar_vale.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({ codigo: codigo, supervisor_pass: pass })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    
    alert(`Reembolso registrado con éxito. Se retiraron ${formatCLP(disponible)} de la caja.`);
    location.reload();
  } catch (err) {
    alert('Error al reembolsar vale: ' + err.message);
  }
}
</script>
