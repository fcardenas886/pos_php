<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Devoluciones y Notas de Crédito</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Reembolso de productos a clientes y reintegración de stock a Kardex</p>
  </div>
  <button onclick="document.getElementById('devModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-rotate-left"></i> Registrar Devolución
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Devoluciones</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($devoluciones) ?> registros</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N° Dev.</th>
        <th>Fecha</th>
        <th>N° Venta</th>
        <th>Productos Devueltos</th>
        <th>Método Reembolso</th>
        <th>Monto Devuelto</th>
        <th>Motivo</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($devoluciones)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay devoluciones registradas.</td></tr>
      <?php else: ?>
        <?php foreach ($devoluciones as $d): ?>
          <tr>
            <td>#<?= $d['DevolucionID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($d['FechaDevolucion'])) ?></td>
            <td><strong>#<?= $d['VentaID'] ?></strong></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($d['ItemsDevueltos'] ?: 'Devolución parcial') ?></td>
            <td><span class="badge badge-warning"><?= $d['MetodoDevolucion'] ?></span></td>
            <td style="font-weight: 700; color: var(--danger);"><?= formatCLP($d['MontoDevuelto']) ?></td>
            <td style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($d['Motivo']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nueva Devolución -->
<div id="devModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 440px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Procesar Devolución</h2>
    
    <form method="POST" action="devoluciones.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">N° ID DE VENTA *</label>
        <input type="number" name="venta_id" class="form-control" required placeholder="Ej: 15">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">ID O CÓDIGO DE PRODUCTO *</label>
        <input type="number" name="producto_id" class="form-control" required placeholder="ID del producto vendido">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CANTIDAD *</label>
          <input type="number" step="0.001" name="cantidad" class="form-control" required value="1">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">REEMBOLSO *</label>
          <select name="metodo_devolucion" class="form-control" required>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
            <option value="Nota de Credito">Nota de Crédito / Vale</option>
          </select>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MOTIVO DE LA DEVOLUCIÓN *</label>
        <input type="text" name="motivo" class="form-control" required placeholder="Ej: Producto defectuoso / Cambio de opinión">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Confirmar Devolución</button>
        <button type="button" onclick="document.getElementById('devModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
