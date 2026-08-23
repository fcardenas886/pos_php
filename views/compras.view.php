<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Recepción de Compras e Ingreso de Stock</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Registro de facturas/guías de proveedores e incremento automático en Kardex</p>
  </div>
  <button onclick="document.getElementById('compraModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-truck-ramp-box"></i> Ingresar Mercadería
  </button>
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

<!-- Tabla de Historial de Compras -->
<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Compras Recientes</h2>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th>N° Compra</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th>N° Doc / Factura</th>
        <th>Productos Ingresados</th>
        <th>Total Compra</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($historialCompras)): ?>
        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No se han registrado ingresos de mercadería.</td></tr>
      <?php else: ?>
        <?php foreach ($historialCompras as $c): ?>
          <tr>
            <td>#<?= $c['CompraID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($c['FechaCompra'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($c['Proveedor']) ?></td>
            <td><?= htmlspecialchars($c['NumeroDocumento'] ?: 'Sin N° Doc') ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($c['Items'] ?: 'Detalle de compra') ?></td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($c['MontoTotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Ingresar Compra -->
<div id="compraModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 480px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Ingreso de Mercadería</h2>
    
    <form method="POST" action="compras.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PROVEEDOR *</label>
        <select name="proveedor_id" class="form-control" required>
          <?php foreach ($proveedores as $prov): ?>
            <option value="<?= $prov['ProveedorID'] ?>"><?= htmlspecialchars($prov['RazonSocial']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">N° FACTURA O GUÍA DE DESPACHO</label>
        <input type="text" name="numero_documento" class="form-control" placeholder="Ej: F-12049">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PRODUCTO A INGRESAR *</label>
        <select name="producto_id" class="form-control" required>
          <option value="">-- Selecciona Producto --</option>
          <?php foreach ($productos as $prod): ?>
            <option value="<?= $prod['ProductoID'] ?>"><?= htmlspecialchars($prod['Nombre']) ?> (Stock Actual: <?= $prod['Stock'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CANTIDAD A INGRESAR *</label>
          <input type="number" step="0.001" name="cantidad" class="form-control" required placeholder="Ej: 24">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">COSTO UNITARIO ($) *</label>
          <input type="number" name="costo_unitario" class="form-control" required placeholder="Ej: 850">
        </div>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-plus-circle"></i> Registrar Ingreso</button>
        <button type="button" onclick="document.getElementById('compraModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
