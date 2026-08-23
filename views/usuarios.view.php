<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Gestión de Usuarios y Roles</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Control de acceso para Cajeros, Supervisores y Administradores</p>
  </div>
  <button onclick="document.getElementById('userModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-user-plus"></i> Crear Usuario
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Usuarios Registrados</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($usuarios) ?> usuarios</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre Completo</th>
        <th>Usuario (Login)</th>
        <th>Rol de Acceso</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usuarios as $u): ?>
        <tr>
          <td>#<?= $u['UsuarioID'] ?></td>
          <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($u['Nombre']) ?></td>
          <td><code><?= htmlspecialchars($u['NombreUsuario']) ?></code></td>
          <td><span class="badge badge-warning"><?= htmlspecialchars($u['Rol']) ?></span></td>
          <td>
            <span class="badge <?= $u['Activo'] ? 'badge-success' : 'badge-danger' ?>">
              <?= $u['Activo'] ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo Usuario -->
<div id="userModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 400px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Crear Nuevo Usuario</h2>
    
    <form method="POST" action="usuarios.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE COMPLETO *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Pedro Morales">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">USUARIO LOGIN *</label>
        <input type="text" name="username" class="form-control" required placeholder="Ej: pmorales">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CONTRASEÑA *</label>
        <input type="password" name="password" class="form-control" required placeholder="••••••••">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">ROL ASIGNADO *</label>
        <select name="rol_id" class="form-control" required>
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['RolID'] ?>"><?= htmlspecialchars($r['Nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar Usuario</button>
        <button type="button" onclick="document.getElementById('userModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
