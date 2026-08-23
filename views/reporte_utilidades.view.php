<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Reporte de Utilidades y Márgenes de Ganancia</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Cálculo de margen neto comparando Precio de Venta vs. Costo de Compra</p>
  </div>

  <form method="GET" action="reporte_utilidades.php" style="display: flex; gap: 0.5rem; align-items: center;">
    <input type="date" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>" class="form-control" style="padding: 0.4rem 0.6rem;">
    <span style="color: var(--text-muted);">a</span>
    <input type="date" name="fin" value="<?= htmlspecialchars($fechaFin) ?>" class="form-control" style="padding: 0.4rem 0.6rem;">
    <button type="submit" class="btn btn-secondary" style="padding: 0.4rem 0.85rem;"><i class="fa-solid fa-filter"></i> Filtrar</button>
  </form>
</div>

<!-- Resumen en Tarjetas -->
<div class="grid-stats" style="margin-bottom: 1.5rem;">
  <div class="stat-card">
    <div class="stat-info">
      <h3>Total Ingresos por Venta</h3>
      <div class="stat-value"><?= formatCLP($totalVentasMonto) ?></div>
    </div>
    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);"><i class="fa-solid fa-chart-line"></i></div>
  </div>
  <div class="stat-card">
    <div class="stat-info">
      <h3>Total Costo Mercadería</h3>
      <div class="stat-value" style="color: var(--warning);"><?= formatCLP($totalCostoMonto) ?></div>
    </div>
    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--warning);"><i class="fa-solid fa-boxes-stacked"></i></div>
  </div>
  <div class="stat-card">
    <div class="stat-info">
      <h3>Utilidad Neta Estimada</h3>
      <div class="stat-value" style="color: #818cf8;"><?= formatCLP($totalUtilidadMonto) ?></div>
    </div>
    <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;"><i class="fa-solid fa-sack-dollar"></i></div>
  </div>
</div>

<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Desglose de Margen por Producto</h2>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Categoría</th>
        <th>Unidades Vendidas</th>
        <th>Venta Total</th>
        <th>Costo Total</th>
        <th>Ganancia Neta</th>
        <th>Margen %</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($reporte)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay ventas registradas en el período seleccionado.</td></tr>
      <?php else: ?>
        <?php foreach ($reporte as $row): ?>
          <?php 
            $margen = $row['TotalVentas'] > 0 ? round(($row['UtilidadEstimada'] / $row['TotalVentas']) * 100, 1) : 0;
          ?>
          <tr>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['Producto']) ?></td>
            <td><?= htmlspecialchars($row['Categoria'] ?: 'General') ?></td>
            <td><strong><?= $row['CantidadVendida'] ?></strong> u</td>
            <td><?= formatCLP($row['TotalVentas']) ?></td>
            <td style="color: var(--text-muted);"><?= formatCLP($row['TotalCosto']) ?></td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($row['UtilidadEstimada']) ?></td>
            <td><span class="badge badge-success"><?= $margen ?>%</span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
