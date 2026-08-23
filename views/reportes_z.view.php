<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Cierre Z Consolidado Fiscal</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Emisión de cierres fiscales correlativos Z acumulados por caja</p>
  </div>

  <form method="POST" action="reportes_z.php" onsubmit="return confirm('¿Confirmas la emisión del Cierre Z? Esta acción agrupará todas las ventas pendientes.')">
    <?= csrfField() ?>
    <input type="hidden" name="caja_id" value="1">
    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 125rem;">
      <i class="fa-solid fa-file-invoice-dollar"></i> Generar Cierre Z Diario
    </button>
  </form>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #34d399; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #f87171; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Reportes Z Emitidos</h2>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N° Z</th>
        <th>Fecha Emisión</th>
        <th>Caja</th>
        <th>Supervisor</th>
        <th>Boletas / Facturas</th>
        <th>Monto Neto</th>
        <th>IVA (19%)</th>
        <th>Total Z</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($cierresZ)): ?>
        <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay cierres Z generados.</td></tr>
      <?php else: ?>
        <?php foreach ($cierresZ as $z): ?>
          <tr>
            <td><strong>#<?= $z['NumeroZ'] ?></strong></td>
            <td><?= date('d/m/Y H:i', strtotime($z['FechaEmision'])) ?></td>
            <td><?= htmlspecialchars($z['CajaName']) ?></td>
            <td><?= htmlspecialchars($z['Usuario']) ?></td>
            <td><?= $z['CantidadBoletas'] ?> Boletas / <?= $z['CantidadFacturas'] ?> Facturas</td>
            <td><?= formatCLP($z['MontoNeto']) ?></td>
            <td><?= formatCLP($z['MontoIva']) ?></td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($z['MontoTotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
