<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Alertas de Stock y Sugerencias de Reabastecimiento</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Algoritmo inteligente de compras según rotación de ventas a 7, 15 y 30 días</p>
  </div>
  <?php if ($esSupervisorNav): ?>
  <a href="compras.php" class="btn btn-primary">
    <i class="fa-solid fa-truck-ramp-box"></i> Ir a Ingreso de Compras
  </a>
  <?php endif; ?>
</div>

<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--danger);">
      <i class="fa-solid fa-triangle-exclamation"></i> Productos Críticos Bajo Stock Mínimo
    </h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($alertas) ?> productos requieren compra</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Código</th>
        <th>Nombre Producto</th>
        <th>Stock Actual</th>
        <th>Stock Mínimo</th>
        <th>Ventas (7d / 15d / 30d)</th>
        <th>Sugerencia de Compra</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($alertas)): ?>
        <tr>
          <td colspan="6" style="text-align: center; color: var(--success); padding: 3rem;">
            <i class="fa-solid fa-circle-check" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
            <p>¡Excelente! Todos los productos están por encima de su stock mínimo.</p>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($alertas as $a): ?>
          <?php 
            // Calcular sugerencia de compra recomendada para cubrir 15 días según consumo medio diario
            $consumoDiario = $a['Ventas15Dias'] > 0 ? ($a['Ventas15Dias'] / 15) : ($a['Ventas30Dias'] / 30);
            $sugerido = ceil(max(10, ($consumoDiario * 15) - $a['Stock']));
          ?>
          <tr>
            <td><code><?= htmlspecialchars($a['CodigoBarras'] ?: 'S/C') ?></code></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($a['Nombre']) ?></td>
            <td style="font-weight: 700; color: var(--danger);"><?= $a['Stock'] ?></td>
            <td><?= $a['StockMinimo'] ?></td>
            <td>
              <span style="color: var(--text-muted);"><?= (int)$a['Ventas7Dias'] ?>u</span> / 
              <span style="color: var(--text-muted);"><?= (int)$a['Ventas15Dias'] ?>u</span> / 
              <span style="color: var(--text-muted);"><?= (int)$a['Ventas30Dias'] ?>u</span>
            </td>
            <td>
              <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.3rem 0.75rem;">
                Comprar +<?= $sugerido ?> unidades
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
