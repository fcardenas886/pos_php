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
              <button onclick="verCuentaCliente(<?= $cl['ClienteID'] ?>)" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; margin-right: 0.25rem;">
                <i class="fa-solid fa-eye"></i> Cuenta
              </button>
              <?php if (($cl['SaldoDeudor'] ?? 0) > 0): ?>
                <button onclick="abrirAbonoCliente(<?= $cl['ClienteID'] ?>, <?= htmlspecialchars(json_encode($cl['Nombre']), ENT_QUOTES) ?>, <?= $cl['SaldoDeudor'] ?>)" class="btn btn-success" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
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

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MONTO DE ABONO ($) *</label>
          <input type="number" name="monto_abono" class="form-control" required placeholder="Ej: 10000" style="font-size: 1.1rem; font-weight: bold;">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MÉTODO PAGO *</label>
          <select name="metodo_pago" class="form-control" required>
            <option value="Efectivo">Efectivo</option>
            <option value="Tarjeta">Tarjeta</option>
            <option value="Transferencia">Transferencia</option>
          </select>
        </div>
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

</div>

<div class="table-card" style="margin-top: 2rem;">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Abonos Recientes</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;">Mostrando últimos 30 registros</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N° Abono</th>
        <th>Fecha y Hora</th>
        <th>Cliente</th>
        <th>Aplicado a</th>
        <th>Método de Pago</th>
        <th>Monto Abonado</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($abonos)): ?>
        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay abonos registrados en el sistema.</td></tr>
      <?php else: ?>
        <?php foreach ($abonos as $ab): ?>
          <tr>
            <td>#<?= $ab['AbonoID'] ?></td>
            <td><?= date('d/m/Y H:i:s', strtotime($ab['FechaAbono'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($ab['ClienteNombre']) ?></td>
            <td><?= $ab['VentaID'] ? 'Venta #' . $ab['VentaID'] : '<span style="color: var(--text-muted);">General</span>' ?></td>
            <td><span class="badge badge-success"><?= htmlspecialchars($ab['MetodoPago']) ?></span></td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($ab['Monto']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
function abrirAbonoCliente(id, nombre, deuda) {
  const select = document.getElementById('abonoClienteSelect');
  if (select) select.value = id;
  document.getElementById('abonoModal').style.display = 'flex';
}

async function verCuentaCliente(id) {
  try {
    const res = await fetch(`api/detalle_cuenta_cliente.php?id=${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    document.getElementById('cuentaClienteNombre').textContent = data.cliente.nombre;
    document.getElementById('cuentaSaldoDeudor').textContent = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(data.cliente.saldo_deudor);
    document.getElementById('cuentaLimiteCredito').textContent = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(data.cliente.limite_credito);

    // Poblar deudas, con el estado real de cada venta (Pagado / Parcial / Pendiente)
    const tbodyDeudas = document.getElementById('cuentaTablaDeudas');
    if (data.deudas.length === 0) {
      tbodyDeudas.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1rem;">No registra deudas (ventas al fiado).</td></tr>`;
    } else {
      const badgeEstado = { 'Pagado': 'badge-success', 'Parcial': 'badge-warning', 'Pendiente': 'badge-danger' };
      tbodyDeudas.innerHTML = data.deudas.map(d => `
        <tr>
          <td><strong>#${d.venta_id}</strong></td>
          <td>${d.fecha}</td>
          <td style="font-weight: 700; color: #fff;">$${new Intl.NumberFormat('es-CL').format(d.credito)}</td>
          <td style="font-weight: 700; color: var(--danger);">$${new Intl.NumberFormat('es-CL').format(d.pendiente)}</td>
          <td><span class="badge ${badgeEstado[d.estado]}">${d.estado}</span></td>
        </tr>
      `).join('');
    }

    // Poblar abonos, con la venta a la que quedaron aplicados
    const tbodyAbonos = document.getElementById('cuentaTablaAbonos');
    if (data.abonos.length === 0) {
      tbodyAbonos.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1rem;">No registra abonos de pago.</td></tr>`;
    } else {
      tbodyAbonos.innerHTML = data.abonos.map(a => `
        <tr>
          <td>#${a.abono_id}</td>
          <td>${a.fecha}</td>
          <td>${a.venta_id ? `Venta #${a.venta_id}` : `<span style="color: var(--text-muted);">General</span>`}</td>
          <td><span class="badge badge-success">${a.metodo}</span></td>
          <td style="font-weight: 700; color: var(--success);">$${new Intl.NumberFormat('es-CL').format(a.monto)}</td>
        </tr>
      `).join('');
    }

    document.getElementById('cuentaModal').style.display = 'flex';
  } catch (err) {
    alert('Error al cargar detalle de cuenta: ' + err.message);
  }
}

function cerrarCuentaModal() {
  document.getElementById('cuentaModal').style.display = 'none';
}
</script>

<!-- Modal Estado de Cuenta de Cliente -->
<div id="cuentaModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 680px; max-width: 95%; padding: 1.75rem; box-shadow: var(--shadow-lg); max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-dark); padding-bottom: 0.75rem;">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #818cf8;">Estado de Cuenta: <span id="cuentaClienteNombre" style="color: #fff;"></span></h2>
      <button onclick="cerrarCuentaModal()" class="btn btn-secondary" style="padding: 0.3rem 0.6rem; font-size: 1.2rem; line-height: 1;">&times;</button>
    </div>

    <!-- Resumen Financiero -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 10px; border: 1px solid var(--border-dark);">
      <div>
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">SALDO DEUDOR ACTUAL (FIADO)</div>
        <div id="cuentaSaldoDeudor" style="font-size: 1.5rem; font-weight: 700; color: var(--danger);"></div>
      </div>
      <div>
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">LÍMITE DE CRÉDITO AUTORIZADO</div>
        <div id="cuentaLimiteCredito" style="font-size: 1.5rem; font-weight: 700; color: var(--text-muted);"></div>
      </div>
    </div>

    <!-- Sección Deudas -->
    <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; color: #f87171;"><i class="fa-solid fa-file-invoice-dollar"></i> Deudas / Compras al Fiado</h3>
    <div style="max-height: 200px; overflow-y: auto; margin-bottom: 1.5rem; border: 1px solid var(--border-dark); border-radius: 8px;">
      <table class="table" style="margin-bottom: 0;">
        <thead>
          <tr>
            <th>N° Venta</th>
            <th>Fecha</th>
            <th>Monto al Fiado</th>
            <th>Saldo Pendiente</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody id="cuentaTablaDeudas">
        </tbody>
      </table>
    </div>

    <!-- Sección Abonos -->
    <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; color: #34d399;"><i class="fa-solid fa-hand-holding-dollar"></i> Abonos / Pagos Realizados</h3>
    <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border-dark); border-radius: 8px;">
      <table class="table" style="margin-bottom: 0;">
        <thead>
          <tr>
            <th>N° Abono</th>
            <th>Fecha</th>
            <th>Aplicado a</th>
            <th>Método Pago</th>
            <th>Monto Abonado</th>
          </tr>
        </thead>
        <tbody id="cuentaTablaAbonos">
        </tbody>
      </table>
    </div>
  </div>
</div>
