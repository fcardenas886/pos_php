<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Clientes y Cuentas Corrientes (Fiado)</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Gestión de clientes, saldo deudor acumulado y registro de abonos</p>
  </div>
  <div style="display: flex; gap: 0.5rem;">
    <button onclick="document.getElementById('abonoModal').style.display='flex'" class="btn btn-secondary">
      <i class="fa-solid fa-hand-holding-dollar"></i> Registrar Abono
    </button>
    <button onclick="document.getElementById('clienteModal').style.display='flex'" class="btn btn-primary">
      <i class="fa-solid fa-user-plus"></i> Nuevo Cliente
    </button>
  </div>
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Listado de Clientes y Cuentas de Crédito</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;">Total: <?= count($clientes) ?> clientes</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>RUT</th>
        <th>Nombre del Cliente</th>
        <th>Teléfono</th>
        <th>Saldo Deudor (Fiado)</th>
        <th>Límite Crédito</th>
        <th>Puntos</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($clientes)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay clientes registrados.</td></tr>
      <?php else: ?>
        <?php foreach ($clientes as $cl): ?>
          <tr>
            <td><code><?= $cl['RutCuerpo'] ? $cl['RutCuerpo'] . '-' . $cl['RutDv'] : 'S/R' ?></code></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($cl['Nombre']) ?></td>
            <td><?= htmlspecialchars($cl['Telefono'] ?: '-') ?></td>
            <td style="font-weight: 700; color: <?= ($cl['SaldoDeudor'] ?? 0) > 0 ? 'var(--danger)' : 'var(--text-muted)' ?>;">
              <?= formatCLP($cl['SaldoDeudor'] ?? 0) ?>
            </td>
            <td style="color: var(--text-muted);"><?= formatCLP($cl['LimiteCredito'] ?? 50000) ?></td>
            <td style="font-weight: 700; color: var(--success);"><?= number_format($cl['PuntosAcumulados'], 0, ',', '.') ?> pts</td>
            <td>
              <?php if (($cl['SaldoDeudor'] ?? 0) > 0): ?>
                <button onclick="abrirAbonoCliente(<?= $cl['ClienteID'] ?>, '<?= htmlspecialchars($cl['Nombre'], ENT_QUOTES) ?>', <?= $cl['SaldoDeudor'] ?>)" class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                  <i class="fa-solid fa-money-bill-transfer"></i> Abonar
                </button>
              <?php else: ?>
                <span style="font-size: 0.8rem; color: var(--success);">Al día</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo Cliente -->
<div id="clienteModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 420px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Registrar Cliente</h2>
    
    <form method="POST" action="clientes.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="save_client">
      
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE COMPLETO *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
      </div>

      <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 0.5rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">RUT CUERPO</label>
          <input type="number" name="rut_cuerpo" class="form-control" placeholder="12345678">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">DV</label>
          <input type="text" name="rut_dv" maxlength="1" class="form-control" placeholder="K">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TELÉFONO</label>
          <input type="text" name="telefono" class="form-control" placeholder="+56 9 1234 5678">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">LÍMITE CRÉDITO ($)</label>
          <input type="number" name="limite_credito" value="50000" class="form-control">
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">EMAIL</label>
        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar Cliente</button>
        <button type="button" onclick="document.getElementById('clienteModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Registrar Abono -->
<div id="abonoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 420px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Registrar Abono a Deuda</h2>
    
    <form method="POST" action="clientes.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="abono">
      
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CLIENTE *</label>
        <select id="abonoClienteSelect" name="cliente_id" class="form-control" required>
          <?php foreach ($clientes as $c): ?>
            <option value="<?= $c['ClienteID'] ?>">
              <?= htmlspecialchars($c['Nombre']) ?> (Deuda: <?= formatCLP($c['SaldoDeudor'] ?? 0) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MONTO DE ABONO ($) *</label>
        <input type="number" name="monto_abono" class="form-control" required placeholder="Ej: 10000" style="font-size: 1.1rem; font-weight: bold;">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CONCEPTO / OBSERVACIÓN</label>
        <input type="text" name="concepto" class="form-control" placeholder="Abono parcial / Pago total deuda">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-success btn-block"><i class="fa-solid fa-check"></i> Registrar Abono</button>
        <button type="button" onclick="document.getElementById('abonoModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirAbonoCliente(id, nombre, deuda) {
  const select = document.getElementById('abonoClienteSelect');
  if (select) select.value = id;
  document.getElementById('abonoModal').style.display = 'flex';
}
</script>
