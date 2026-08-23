<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Administración de Cajas Físicas</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Definición y control de puntos de cobro y terminales de caja</p>
  </div>
  <button onclick="document.getElementById('cajaModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-cash-register"></i> Nueva Caja
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Cajas del Sistema</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($cajas) ?> cajas registradas</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N° ID</th>
        <th>Nombre de la Caja</th>
        <th>Turnos Registrados</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cajas as $cj): ?>
        <tr>
          <td>#<?= $cj['CajaID'] ?></td>
          <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($cj['Nombre']) ?></td>
          <td><span class="badge badge-warning"><?= $cj['TotalTurnos'] ?> turnos</span></td>
          <td>
            <span class="badge <?= $cj['Activa'] ? 'badge-success' : 'badge-danger' ?>">
              <?= $cj['Activa'] ? 'Activa' : 'Inactiva' ?>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nueva Caja -->
<div id="cajaModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 400px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Crear Nueva Caja</h2>
    
    <form method="POST" action="cajas.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE DE LA CAJA *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Caja 2 / Caja Express">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar Caja</button>
        <button type="button" onclick="document.getElementById('cajaModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
