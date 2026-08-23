<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Categorías de Productos</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Organización del catálogo por familias y grupos</p>
  </div>
  <button onclick="document.getElementById('catModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-folder-plus"></i> Nueva Categoría
  </button>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #34d399; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Listado de Categorías</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($categorias) ?> categorías</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre Categoría</th>
        <th>Descripción</th>
        <th>Productos Asignados</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categorias as $cat): ?>
        <tr>
          <td>#<?= $cat['CategoriaID'] ?></td>
          <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($cat['Nombre']) ?></td>
          <td style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($cat['Descripcion'] ?: 'Sin descripción') ?></td>
          <td><span class="badge badge-success"><?= $cat['TotalProductos'] ?> productos</span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nueva Categoría -->
<div id="catModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 400px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Crear Categoría</h2>
    
    <form method="POST" action="categorias.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE CATEGORÍA *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Congelados">
      </div>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">DESCRIPCIÓN</label>
        <input type="text" name="descripcion" class="form-control" placeholder="Descripción opcional">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <button type="button" onclick="document.getElementById('catModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
