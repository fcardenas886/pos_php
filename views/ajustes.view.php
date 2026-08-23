<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Ajustes Manuales de Stock</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Corrección de diferencias, mermas, pérdidas o ingresos manuales de inventario</p>
  </div>
  <button onclick="document.getElementById('ajusteModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-sliders"></i> Nuevo Ajuste de Stock
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Ajustes de Stock</h2>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th>N° Ajuste</th>
        <th>Fecha / Hora</th>
        <th>Usuario</th>
        <th>Producto</th>
        <th>Tipo</th>
        <th>Cantidad</th>
        <th>Motivo</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($historialAjustes)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay ajustes registrados.</td></tr>
      <?php else: ?>
        <?php foreach ($historialAjustes as $aj): ?>
          <tr>
            <td>#<?= $aj['AjusteStockID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($aj['FechaAjuste'])) ?></td>
            <td><?= htmlspecialchars($aj['Usuario']) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($aj['ProductoName'] ?: 'Múltiples') ?></td>
            <td>
              <span class="badge <?= $aj['TipoMovimiento'] == 'ENTRADA' ? 'badge-success' : 'badge-danger' ?>">
                <?= $aj['TipoMovimiento'] ?>
              </span>
            </td>
            <td style="font-weight: bold;"><?= $aj['Cantidad'] ?></td>
            <td style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($aj['Motivo']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo Ajuste -->
<div id="ajusteModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 440px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Realizar Ajuste de Stock</h2>
    
    <form method="POST" action="ajustes.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PRODUCTO *</label>
        <select name="producto_id" class="form-control" required>
          <option value="">-- Selecciona Producto --</option>
          <?php foreach ($productosList as $p): ?>
            <option value="<?= $p['ProductoID'] ?>"><?= htmlspecialchars($p['Nombre']) ?> (Stock: <?= $p['Stock'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TIPO AJUSTE *</label>
          <select name="tipo_movimiento" class="form-control" required>
            <option value="ENTRADA">Entrada (+)</option>
            <option value="SALIDA">Salida (- Merma/Pérdida)</option>
          </select>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CANTIDAD *</label>
          <input type="number" step="0.001" name="cantidad" class="form-control" required placeholder="Ej: 5">
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MOTIVO DEL AJUSTE *</label>
        <input type="text" name="motivo" class="form-control" required placeholder="Ej: Merma por vencimiento, Corrección de inventario">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Aplicar Ajuste</button>
        <button type="button" onclick="document.getElementById('ajusteModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
