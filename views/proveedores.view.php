<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Proveedores y Distribuidores</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Directorio de proveedores para ordenes de compra e ingreso de facturas</p>
  </div>
  <button onclick="document.getElementById('provModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-truck"></i> Nuevo Proveedor
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Listado de Proveedores</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($proveedores) ?> proveedores</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>RUT</th>
        <th>Razón Social</th>
        <th>Giro Comercial</th>
        <th>Teléfono</th>
        <th>Email</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($proveedores)): ?>
        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay proveedores registrados.</td></tr>
      <?php else: ?>
        <?php foreach ($proveedores as $pr): ?>
          <tr>
            <td><code><?= $pr['RutCuerpo'] . '-' . $pr['RutDv'] ?></code></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($pr['RazonSocial']) ?></td>
            <td><?= htmlspecialchars($pr['Giro'] ?: 'Distribuidora General') ?></td>
            <td><?= htmlspecialchars($pr['Telefono'] ?: '-') ?></td>
            <td><?= htmlspecialchars($pr['Email'] ?: '-') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo Proveedor -->
<div id="provModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 440px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Registrar Proveedor</h2>
    
    <form method="POST" action="proveedores.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 0.5rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">RUT CUERPO *</label>
          <input type="number" name="rut_cuerpo" class="form-control" required placeholder="76123456">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">DV *</label>
          <input type="text" name="rut_dv" maxlength="1" class="form-control" required placeholder="K">
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">RAZÓN SOCIAL *</label>
        <input type="text" name="razon_social" class="form-control" required placeholder="Ej: Distribuidora Alimentos S.A.">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">GIRO COMERCIAL</label>
        <input type="text" name="giro" class="form-control" placeholder="Ej: Distribución de Alimentos y Bebidas">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TELÉFONO</label>
          <input type="text" name="telefono" class="form-control" placeholder="+56 2 2123 4567">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">EMAIL</label>
          <input type="email" name="email" class="form-control" placeholder="ventas@prov.cl">
        </div>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar Proveedor</button>
        <button type="button" onclick="document.getElementById('provModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
