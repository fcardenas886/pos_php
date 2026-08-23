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
</script>
