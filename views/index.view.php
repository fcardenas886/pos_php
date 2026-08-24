<!-- Tarjetas de Estadísticas -->
<div class="grid-stats">
  <div class="stat-card">
    <div class="stat-info">
      <h3>Ventas del Día</h3>
      <div class="stat-value"><?= formatCLP($statsVentas['total_monto']) ?></div>
      <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">
        <?= $statsVentas['total_ventas'] ?> transacciones completadas
      </p>
    </div>
    <div class="stat-icon">
      <i class="fa-solid fa-sack-dollar"></i>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <h3>Estado de Caja</h3>
      <div class="stat-value" style="font-size: 1.35rem;">
        <?php if ($turnoActivo): ?>
          <span style="color: var(--success);"><i class="fa-solid fa-circle-check"></i> Turno Abierto</span>
        <?php else: ?>
          <span style="color: var(--warning);"><i class="fa-solid fa-lock"></i> Turno Cerrado</span>
        <?php endif; ?>
      </div>
      <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">
        <?= $turnoActivo ? 'Abierto a las ' . date('H:i', strtotime($turnoActivo['FechaApertura'])) : 'Abre caja para vender' ?>
      </p>
    </div>
    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
      <i class="fa-solid fa-cash-register"></i>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <h3>Alertas de Stock</h3>
      <div class="stat-value" style="color: <?= $lowStockCount > 0 ? 'var(--danger)' : 'var(--text-muted)' ?>;">
        <?= $lowStockCount ?>
      </div>
      <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">
        <a href="alertas_stock.php" style="color: var(--danger); text-decoration: none;">Ver sugerencias &rarr;</a>
      </p>
    </div>
    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: var(--danger);">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
  </div>
</div>

<!-- Accesos Rápidos & Últimas Ventas -->
<div style="display: grid; grid-template-columns: 340px 1fr; gap: 1.5rem;">
  
  <!-- Accesos directos -->
  <div class="table-card" style="padding: 1.25rem;">
    <h2 style="font-size: 1.1rem; margin-bottom: 1rem; font-weight: 600;">Módulos del Sistema</h2>
    <div style="display: flex; flex-direction: column; gap: 0.6rem;">
      <a href="pos.php" class="btn btn-primary" style="justify-content: flex-start; padding: 0.75rem 1rem;">
        <i class="fa-solid fa-cart-shopping" style="font-size: 1.1rem;"></i> Caja Registradora (POS)
      </a>
      <?php if ($esSupervisorNav): ?>
      <a href="compras.php" class="btn btn-secondary" style="justify-content: flex-start; padding: 0.75rem 1rem;">
        <i class="fa-solid fa-truck-ramp-box" style="font-size: 1.1rem;"></i> Recepción de Compras
      </a>
      <?php endif; ?>
      <a href="caja.php" class="btn btn-secondary" style="justify-content: flex-start; padding: 0.75rem 1rem;">
        <i class="fa-solid fa-vault" style="font-size: 1.1rem;"></i> Turnos y Arqueo de Caja
      </a>
      <?php if ($esSupervisorNav): ?>
      <a href="productos.php" class="btn btn-secondary" style="justify-content: flex-start; padding: 0.75rem 1rem;">
        <i class="fa-solid fa-boxes-stacked" style="font-size: 1.1rem;"></i> Catálogo de Productos
      </a>
      <?php endif; ?>
      <a href="clientes.php" class="btn btn-secondary" style="justify-content: flex-start; padding: 0.75rem 1rem;">
        <i class="fa-solid fa-users" style="font-size: 1.1rem;"></i> Clientes y Puntos
      </a>
      <a href="alertas_stock.php" class="btn btn-secondary" style="justify-content: flex-start; padding: 0.75rem 1rem;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem;"></i> Sugerencias de Reabastecimiento
      </a>
      <a href="ventas.php" class="btn btn-secondary" style="justify-content: flex-start; padding: 0.75rem 1rem;">
        <i class="fa-solid fa-receipt" style="font-size: 1.1rem;"></i> Historial de Ventas
      </a>
    </div>
  </div>

  <!-- Últimas Ventas -->
  <div class="table-card">
    <div class="table-header">
      <h2 style="font-size: 1.1rem; font-weight: 600;">Últimas Ventas Registradas</h2>
      <a href="ventas.php" style="color: #818cf8; text-decoration: none; font-size: 0.85rem;">Ver todas &rarr;</a>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>ID Venta</th>
          <th>Fecha / Hora</th>
          <th>Documento</th>
          <th>Total</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($ultimasVentas)): ?>
          <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay ventas registradas aún.</td></tr>
        <?php else: ?>
          <?php foreach ($ultimasVentas as $v): ?>
            <tr>
              <td>#<?= $v['VentaID'] ?></td>
              <td><?= date('d/m/Y H:i', strtotime($v['FechaVenta'])) ?></td>
              <td><?= htmlspecialchars($v['TipoDocumento']) ?></td>
              <td style="font-weight: 700; color: var(--success);"><?= formatCLP($v['MontoTotal']) ?></td>
              <td>
                <span class="badge <?= $v['Estado'] == 'Completada' ? 'badge-success' : 'badge-danger' ?>">
                  <?= $v['Estado'] ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>
