<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Kardex de Movimientos de Inventario</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Trazabilidad completa de entradas, salidas, ventas, compras y ajustes de stock</p>
  </div>

  <form method="GET" action="kardex.php" style="display: flex; gap: 0.5rem;">
    <select name="producto_id" class="form-control" onchange="this.form.submit()" style="padding: 0.4rem 0.85rem; font-size: 0.9rem;">
      <option value="0">-- Todos los productos --</option>
      <?php foreach ($productosList as $p): ?>
        <option value="<?= $p['ProductoID'] ?>" <?= $productoID == $p['ProductoID'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['Nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Movimientos de Stock</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($kardexList) ?> registros de Kardex</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N° Mov.</th>
        <th>Fecha / Hora</th>
        <th>Producto</th>
        <th>Tipo Transacción</th>
        <th>Entrada</th>
        <th>Salida</th>
        <th>Saldo Stock</th>
        <th>Valor Unit.</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($kardexList)): ?>
        <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay registros en Kardex.</td></tr>
      <?php else: ?>
        <?php foreach ($kardexList as $k): ?>
          <tr>
            <td>#<?= $k['KardexID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($k['FechaMovimiento'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($k['ProductoName']) ?></td>
            <td>
              <span class="badge <?= in_array($k['TipoTransaccion'], ['COMPRA', 'INICIAL', 'AJUSTE_ENTRADA', 'ANULACION_VENTA']) ? 'badge-success' : 'badge-danger' ?>">
                <?= $k['TipoTransaccion'] ?>
              </span>
            </td>
            <td style="color: var(--success); font-weight: bold;"><?= $k['CantidadEntrada'] > 0 ? '+' . $k['CantidadEntrada'] : '-' ?></td>
            <td style="color: var(--danger); font-weight: bold;"><?= $k['CantidadSalida'] > 0 ? '-' . $k['CantidadSalida'] : '-' ?></td>
            <td style="font-weight: 700; color: #fff;"><?= $k['StockSaldo'] ?></td>
            <td><?= formatCLP($k['ValorUnitario']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
