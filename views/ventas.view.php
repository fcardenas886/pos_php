<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Historial de Ventas</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Consulta de ventas por fecha, reimpresión y anulación autorizada con devolución de stock</p>
  </div>
  
  <form method="GET" action="ventas.php" style="display: flex; gap: 0.5rem; align-items: center;">
    <label style="font-size: 0.85rem; color: var(--text-muted);">FECHA:</label>
    <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" class="form-control" style="padding: 0.4rem 0.85rem;" onchange="this.form.submit()">
  </form>
</div>

<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Ventas del día <?= date('d/m/Y', strtotime($fecha)) ?></h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($ventas) ?> registros encontrados</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N° Venta</th>
        <th>Hora</th>
        <th>Cajero</th>
        <th>Documento</th>
        <th>Detalle Productos</th>
        <th>Total</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($ventas)): ?>
        <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">No se encontraron ventas para esta fecha.</td></tr>
      <?php else: ?>
        <?php foreach ($ventas as $v): ?>
          <tr>
            <td><strong>#<?= $v['VentaID'] ?></strong></td>
            <td><?= date('H:i:s', strtotime($v['FechaVenta'])) ?></td>
            <td><?= htmlspecialchars($v['Cajero']) ?></td>
            <td><?= htmlspecialchars($v['TipoDocumento']) ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted); max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              <?= htmlspecialchars($v['ProductosDetalle'] ?: 'Sin detalle') ?>
            </td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($v['MontoTotal']) ?></td>
            <td>
              <span class="badge <?= $v['Estado'] == 'Completada' ? 'badge-success' : 'badge-danger' ?>">
                <?= $v['Estado'] ?>
              </span>
            </td>
            <td>
              <button onclick="reimprimirVenta(<?= $v['VentaID'] ?>)" class="btn btn-primary" style="padding: 0.35rem 0.65rem; font-size: 0.8rem; margin-right: 0.25rem;" title="Ver e Imprimir Ticket">
                <i class="fa-solid fa-print"></i> Ticket
              </button>
              <?php if ($v['Estado'] === 'Completada'): ?>
                <button onclick="anularVenta(<?= $v['VentaID'] ?>)" class="btn btn-danger" style="padding: 0.35rem 0.65rem; font-size: 0.8rem;" title="Anular Venta y Devolver Stock">
                  <i class="fa-solid fa-ban"></i> Anular
                </button>
              <?php else: ?>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Anulada</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
async function anularVenta(id) {
  const motivo = prompt(`¿Motivo de anulación para la venta #${id}?`, 'Error en marcado / Solicitud de cliente');
  if (motivo === null) return;

  const pass = prompt('Clave de Supervisor / Administrador para autorizar anulación (dejar en blanco si eres Admin):', '');

  try {
    const res = await fetch('api/anular_venta.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({
        venta_id: id,
        motivo: motivo,
        supervisor_pass: pass
      })
    });
    const data = await res.json();

    if (!data.success) throw new Error(data.error);

    alert(data.mensaje);
    location.reload();
  } catch (err) {
    alert('Error al anular venta: ' + err.message);
  }
}

async function reimprimirVenta(id) {
  try {
    const res = await fetch(`api/ver_venta.php?id=${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    const v = data.venta;
    document.getElementById('ticketFecha').textContent = v.fecha;
    document.getElementById('ticketTotal').textContent = `$${new Intl.NumberFormat('es-CL').format(v.total)}`;
    document.getElementById('ticketPagado').textContent = `$${new Intl.NumberFormat('es-CL').format(v.pagado)}`;
    document.getElementById('ticketVuelto').textContent = `$${new Intl.NumberFormat('es-CL').format(v.vuelto)}`;

    const detalleEl = document.getElementById('ticketDetalle');
    detalleEl.innerHTML = data.detalles.map(i => {
      let promoLabel = '';
      if (i.descuento > 0) {
         promoLabel = `<div style="font-size: 0.75rem; color: #555;">Dcto aplicado: -$${new Intl.NumberFormat('es-CL').format(i.descuento)}</div>`;
      }
      return `
        <div style="margin-bottom: 0.25rem;">
          <div style="display: flex; justify-content: space-between;">
            <span>${i.cantidad}x ${i.nombre.substring(0, 18)}</span>
            <span>$${new Intl.NumberFormat('es-CL').format(i.subtotal)}</span>
          </div>
          ${promoLabel}
        </div>
      `;
    }).join('');

    document.getElementById('ticketModal').style.display = 'flex';
  } catch (err) {
    alert('Error al recuperar ticket: ' + err.message);
  }
}

function cerrarTicket() {
  document.getElementById('ticketModal').style.display = 'none';
}
</script>

<!-- Modal de Ticket / Comprobante (Impresión Térmica) -->
<div id="ticketModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #fff; color: #000; width: 340px; border-radius: 12px; padding: 1.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: monospace;">
    <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
      <h2 style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.2rem;">MINIMARKET</h2>
      <p style="font-size: 0.8rem;">Comprobante de Venta (Copia)</p>
      <p id="ticketFecha" style="font-size: 0.75rem; color: #555;"></p>
    </div>

    <div id="ticketDetalle" style="font-size: 0.85rem; margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.3rem;">
    </div>

    <div style="border-top: 1px dashed #000; padding-top: 0.5rem; font-size: 0.95rem; font-weight: bold; display: flex; justify-content: space-between;">
      <span>TOTAL:</span>
      <span id="ticketTotal"></span>
    </div>

    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-top: 0.25rem;">
      <span>PAGADO:</span>
      <span id="ticketPagado"></span>
    </div>
    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-top: 0.1rem;">
      <span>VUELTO:</span>
      <span id="ticketVuelto"></span>
    </div>

    <div style="margin-top: 1.25rem; display: flex; gap: 0.5rem;">
      <button onclick="window.print()" class="btn btn-primary btn-block" style="font-size: 0.85rem; padding: 0.5rem;">
        <i class="fa-solid fa-print"></i> Imprimir
      </button>
      <button onclick="cerrarTicket()" class="btn btn-secondary btn-block" style="font-size: 0.85rem; padding: 0.5rem; background: #eee; color: #000; border: 1px solid #ccc;">
        Cerrar
      </button>
    </div>
  </div>
</div>
