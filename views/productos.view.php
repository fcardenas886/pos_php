<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Catálogo de Productos</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Gestión de precios, stock mínimo y códigos de barra</p>
  </div>
  <button onclick="abrirNuevoModal()" class="btn btn-primary">
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
        <th style="text-align: center; width: 140px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($productos)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay productos registrados.</td></tr>
      <?php else: ?>
        <?php foreach ($productos as $p): ?>
          <tr style="<?= !$p['Activo'] ? 'opacity: 0.5; background: rgba(255,255,255,0.02);' : '' ?>">
            <td><code><?= htmlspecialchars($p['CodigoBarras'] ?: 'SIN CÓDIGO') ?></code></td>
            <td style="font-weight: 600; color: #fff;">
              <?= htmlspecialchars($p['Nombre']) ?>
              <?php if (!$p['Activo']): ?>
                <span class="badge badge-danger" style="font-size: 0.65rem; padding: 0.15rem 0.3rem; margin-left: 0.4rem;">INACTIVO</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['Categoria'] ?: 'General') ?></td>
            <td style="font-weight: 600;"><?= $p['Stock'] ?></td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($p['PrecioVenta']) ?></td>
            <td>
              <?php if (!$p['Activo']): ?>
                <span class="badge badge-secondary">Inactivo</span>
              <?php elseif ($p['Stock'] <= 0): ?>
                <span class="badge badge-danger">Sin Stock</span>
              <?php elseif ($p['Stock'] <= $p['StockMinimo']): ?>
                <span class="badge badge-warning">Bajo Stock</span>
              <?php else: ?>
                <span class="badge badge-success">OK</span>
              <?php endif; ?>
            </td>
            <td style="text-align: center;">
              <div style="display: flex; gap: 0.4rem; justify-content: center;">
                <button type="button" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                        onclick='abrirEditarModal(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <?php if ($p['Activo']): ?>
                  <a href="productos.php?toggle_activo=<?= $p['ProductoID'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);" title="Desactivar">
                    <i class="fa-solid fa-ban"></i>
                  </a>
                <?php else: ?>
                  <a href="productos.php?toggle_activo=<?= $p['ProductoID'] ?>" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Activar">
                    <i class="fa-solid fa-check"></i>
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo/Editar Producto -->
<div id="productModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 450px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 id="modalTitle" style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Agregar Nuevo Producto</h2>
    
    <form method="POST" action="productos.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <input type="hidden" name="producto_id" id="prodIdInput" value="">
      
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CÓDIGO DE BARRAS</label>
        <input type="text" name="codigo_barras" id="prodCodigoInput" class="form-control" placeholder="Ej: 780123456789">
      </div>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE PRODUCTO *</label>
        <input type="text" name="nombre" id="prodNombreInput" class="form-control" required placeholder="Ej: Coca Cola 1.5L">
      </div>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CATEGORÍA *</label>
        <select name="categoria_id" id="prodCategoriaSelect" class="form-control" required>
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['CategoriaID'] ?>"><?= htmlspecialchars($cat['Nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PRECIO VENTA ($) *</label>
          <input type="number" name="precio_venta" id="prodPrecioVentaInput" class="form-control" required placeholder="1500">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">COSTO COMPRA ($)</label>
          <input type="number" name="costo_compra" id="prodCostoCompraInput" class="form-control" placeholder="1000">
        </div>
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">STOCK ACTUAL</label>
          <input type="number" step="0.001" name="stock" id="prodStockInput" class="form-control" value="0">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">STOCK MÍNIMO</label>
          <input type="number" step="0.001" name="stock_minimo" id="prodStockMinimoInput" class="form-control" value="0">
        </div>
      </div>
      <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
        <input type="checkbox" name="activo" id="prodActivoInput" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
        <label for="prodActivoInput" style="font-size: 0.85rem; font-weight: 600; cursor: pointer; user-select: none;">Producto Activo (disponible para venta)</label>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <button type="button" onclick="document.getElementById('productModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirNuevoModal() {
  document.getElementById('modalTitle').textContent = 'Agregar Nuevo Producto';
  document.getElementById('prodIdInput').value = '';
  document.getElementById('prodCodigoInput').value = '';
  document.getElementById('prodNombreInput').value = '';
  document.getElementById('prodCategoriaSelect').selectedIndex = 0;
  document.getElementById('prodPrecioVentaInput').value = '';
  document.getElementById('prodCostoCompraInput').value = '';
  document.getElementById('prodStockInput').value = '0';
  document.getElementById('prodStockMinimoInput').value = '0';
  document.getElementById('prodActivoInput').checked = true;
  document.getElementById('productModal').style.display = 'flex';
}

function abrirEditarModal(p) {
  document.getElementById('modalTitle').textContent = 'Editar Producto';
  document.getElementById('prodIdInput').value = p.ProductoID;
  document.getElementById('prodCodigoInput').value = p.CodigoBarras || '';
  document.getElementById('prodNombreInput').value = p.Nombre;
  document.getElementById('prodCategoriaSelect').value = p.CategoriaID;
  document.getElementById('prodPrecioVentaInput').value = p.PrecioVenta;
  document.getElementById('prodCostoCompraInput').value = p.CostoCompra;
  document.getElementById('prodStockInput').value = p.Stock;
  document.getElementById('prodStockMinimoInput').value = p.StockMinimo;
  document.getElementById('prodActivoInput').checked = parseInt(p.Activo) === 1;
  document.getElementById('productModal').style.display = 'flex';
}
</script>
