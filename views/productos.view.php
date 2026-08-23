<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Catálogo de Productos</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Gestión de precios, stock mínimo y códigos de barra</p>
  </div>
  <button onclick="document.getElementById('productModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Nuevo Producto
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

<div class="table-card">
  <div class="table-header">
    <form method="GET" action="productos.php" style="display: flex; gap: 0.5rem; width: 100%; max-width: 400px;">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Buscar por nombre o código..." style="padding: 0.5rem 0.85rem;">
      <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 1rem;"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <div style="color: var(--text-muted); font-size: 0.85rem;">Total: <?= count($productos) ?> productos</div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Código</th>
        <th>Nombre Producto</th>
        <th>Categoría</th>
        <th>Stock</th>
        <th>Precio Venta</th>
        <th>Estado Stock</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($productos)): ?>
        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay productos registrados.</td></tr>
      <?php else: ?>
        <?php foreach ($productos as $p): ?>
          <tr>
            <td><code><?= htmlspecialchars($p['CodigoBarras'] ?: 'SIN CÓDIGO') ?></code></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($p['Nombre']) ?></td>
            <td><?= htmlspecialchars($p['Categoria'] ?: 'General') ?></td>
            <td style="font-weight: 600;"><?= $p['Stock'] ?></td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($p['PrecioVenta']) ?></td>
            <td>
              <?php if ($p['Stock'] <= 0): ?>
                <span class="badge badge-danger">Sin Stock</span>
              <?php elseif ($p['Stock'] <= $p['StockMinimo']): ?>
                <span class="badge badge-warning">Bajo Stock</span>
              <?php else: ?>
                <span class="badge badge-success">OK</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo Producto -->
<div id="productModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 450px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Agregar Nuevo Producto</h2>
    
    <form method="POST" action="productos.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CÓDIGO DE BARRAS</label>
        <input type="text" name="codigo_barras" class="form-control" placeholder="Ej: 780123456789">
      </div>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE PRODUCTO *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Coca Cola 1.5L">
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PRECIO VENTA ($) *</label>
          <input type="number" name="precio_venta" class="form-control" required placeholder="1500">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">COSTO COMPRA ($)</label>
          <input type="number" name="costo_compra" class="form-control" placeholder="1000">
        </div>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">STOCK INICIAL</label>
          <input type="number" name="stock" class="form-control" value="10">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">STOCK MÍNIMO</label>
          <input type="number" name="stock_minimo" class="form-control" value="5">
        </div>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <button type="button" onclick="document.getElementById('productModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
