<div style="margin-bottom: 1.5rem;">
  <h1 style="font-size: 1.5rem; font-weight: 700;">Parametrización y Configuración del Sistema</h1>
  <p style="color: var(--text-muted); font-size: 0.9rem;">Parámetros del negocio, terminales de caja y boleta electrónica SII</p>
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

<!-- Tabs de Navegación de Configuración -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-dark); padding-bottom: 0.5rem;">
  <button onclick="switchTab('tabGenerales')" class="btn btn-secondary tab-btn active" id="btn-tabGenerales">
    ⚙️ Parámetros Generales
  </button>
  <button onclick="switchTab('tabTerminales')" class="btn btn-secondary tab-btn" id="btn-tabTerminales">
    💻 Terminales de Caja
  </button>
  <button onclick="switchTab('tabHaulmer')" class="btn btn-secondary tab-btn" id="btn-tabHaulmer">
    🧾 Boleta Electrónica (SII / Haulmer)
  </button>
</div>

<!-- PESTAÑA 1: PARÁMETROS GENERALES -->
<div id="tabGenerales" class="tab-pane">
  <div class="table-card" style="padding: 1.75rem; max-width: 800px;">
    <form method="POST" action="configuracion.php" style="display: flex; flex-direction: column; gap: 1.25rem;">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="save_config">

      <h2 style="font-size: 1.1rem; font-weight: 600; color: #818cf8; border-bottom: 1px solid var(--border-dark); padding-bottom: 0.4rem;">Datos Comerciales de la Empresa</h2>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE COMERCIAL / RAZÓN SOCIAL *</label>
          <input type="text" name="config[MINIMARKET_NOMBRE]" value="<?= htmlspecialchars($config['MINIMARKET_NOMBRE'] ?? 'Minimarket & Negocio de Barrio') ?>" class="form-control" required>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">IVA DEFECTO (%)</label>
          <input type="number" name="config[IVA_PORCENTAJE]" value="<?= htmlspecialchars($config['IVA_PORCENTAJE'] ?? '19') ?>" class="form-control" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">RUT EMPRESA</label>
          <input type="text" name="config[MINIMARKET_RUT]" value="<?= htmlspecialchars($config['MINIMARKET_RUT'] ?? '76.543.210-K') ?>" class="form-control">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">GIRO COMERCIAL</label>
          <input type="text" name="config[MINIMARKET_GIRO]" value="<?= htmlspecialchars($config['MINIMARKET_GIRO'] ?? 'Minimarket y Venta de Abarrotes') ?>" class="form-control">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">DIRECCIÓN COMERCIAL</label>
          <input type="text" name="config[MINIMARKET_DIRECCION]" value="<?= htmlspecialchars($config['MINIMARKET_DIRECCION'] ?? 'Av. Principal #123, Santiago') ?>" class="form-control">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TELÉFONO</label>
          <input type="text" name="config[MINIMARKET_TELEFONO]" value="<?= htmlspecialchars($config['MINIMARKET_TELEFONO'] ?? '+56 9 1234 5678') ?>" class="form-control">
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MENSAJE PIE DE PÁGINA DEL TICKET</label>
        <input type="text" name="config[TICKET_PIE_PAGINA]" value="<?= htmlspecialchars($config['TICKET_PIE_PAGINA'] ?? '¡Gracias por su preferencia!') ?>" class="form-control">
      </div>

      <h2 style="font-size: 1.1rem; font-weight: 600; color: #818cf8; border-bottom: 1px solid var(--border-dark); padding-bottom: 0.4rem; margin-top: 0.5rem;">Módulos y Reglas del Sistema</h2>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CLUB DE PUNTOS (FIDELIZACIÓN)</label>
          <select name="config[LOYALTY_ACTIVO]" class="form-control">
            <option value="true" <?= ($config['LOYALTY_ACTIVO'] ?? 'true') === 'true' ? 'selected' : '' ?>>Habilitado (Puntos por Compras)</option>
            <option value="false" <?= ($config['LOYALTY_ACTIVO'] ?? '') === 'false' ? 'selected' : '' ?>>Deshabilitado</option>
          </select>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PAGO A CRÉDITO INTERNO (FIADO)</label>
          <select name="config[CREDITO_INTERNO_ACTIVO]" class="form-control">
            <option value="true" <?= ($config['CREDITO_INTERNO_ACTIVO'] ?? 'true') === 'true' ? 'selected' : '' ?>>Permitir Ventas Fiadas</option>
            <option value="false" <?= ($config['CREDITO_INTERNO_ACTIVO'] ?? '') === 'false' ? 'selected' : '' ?>>Solo Pago Inmediato</option>
          </select>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">SONIDO LECTOR RÁPIDO</label>
          <select name="config[SONIDO_LECTOR_RAPIDO]" class="form-control">
            <option value="SI" <?= ($config['SONIDO_LECTOR_RAPIDO'] ?? 'SI') === 'SI' ? 'selected' : '' ?>>Habilitado (Beep)</option>
            <option value="NO" <?= ($config['SONIDO_LECTOR_RAPIDO'] ?? '') === 'NO' ? 'selected' : '' ?>>Silencioso</option>
          </select>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">ARQUEO DE CAJA CIEGO</label>
          <select name="config[ARQUEO_CIEGO]" class="form-control">
            <option value="true" <?= ($config['ARQUEO_CIEGO'] ?? 'true') === 'true' ? 'selected' : '' ?>>Obligatorio (Oculta saldo en sistema)</option>
            <option value="false" <?= ($config['ARQUEO_CIEGO'] ?? '') === 'false' ? 'selected' : '' ?>>Muestra saldo esperado</option>
          </select>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="padding: 0.85rem; font-weight: bold;">
        <i class="fa-solid fa-floppy-disk"></i> Guardar Parámetros Generales
      </button>
    </form>
  </div>
</div>

<!-- PESTAÑA 2: TERMINALES DE CAJA -->
<div id="tabTerminales" class="tab-pane" style="display: none;">
  <div style="display: grid; grid-template-columns: 340px 1fr; gap: 1.5rem;">
    
    <!-- Formulario Vincular Terminal -->
    <div class="table-card" style="padding: 1.5rem;">
      <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Vincular Nueva Terminal</h2>
      <form method="POST" action="configuracion.php" style="display: flex; flex-direction: column; gap: 1rem;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_terminal">

        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE EQUIPO (PC / HOSTNAME) *</label>
          <input type="text" name="nombre_equipo" class="form-control" required placeholder="Ej: CAJA-LOCAL-01, POS-BARRIO">
        </div>

        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CAJA ASIGNADA *</label>
          <select name="caja_id" class="form-control" required>
            <?php foreach ($cajas as $cj): ?>
              <option value="<?= $cj['CajaID'] ?>"><?= htmlspecialchars($cj['Nombre']) ?> (#<?= $cj['CajaID'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-link"></i> Vincular equipo a Caja</button>
      </form>
    </div>

    <!-- Lista de Terminales vinculadas -->
    <div class="table-card">
      <div class="table-header">
        <h2 style="font-size: 1.1rem; font-weight: 600;">Terminales Mapeadas</h2>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre Equipo (PC)</th>
            <th>Caja Asignada</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($terminales)): ?>
            <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay terminales vinculadas.</td></tr>
          <?php else: ?>
            <?php foreach ($terminales as $t): ?>
              <tr>
                <td>#<?= $t['TerminalID'] ?></td>
                <td style="font-weight: 600; color: #fff;"><code><?= htmlspecialchars($t['NombreEquipo']) ?></code></td>
                <td><?= htmlspecialchars($t['CajaName']) ?></td>
                <td><span class="badge badge-success">Activo</span></td>
                <td>
                  <form method="POST" action="configuracion.php" onsubmit="return confirm('¿Desvincular esta terminal?')">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete_terminal">
                    <input type="hidden" name="terminal_id" value="<?= $t['TerminalID'] ?>">
                    <button type="submit" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;"><i class="fa-solid fa-trash-can"></i> Desvincular</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- PESTAÑA 3: BOLETA ELECTRÓNICA SII / HAULMER -->
<div id="tabHaulmer" class="tab-pane" style="display: none;">
  <div class="table-card" style="padding: 1.75rem; max-width: 800px;">
    <form method="POST" action="configuracion.php" style="display: flex; flex-direction: column; gap: 1.25rem;">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="save_config">

      <h2 style="font-size: 1.1rem; font-weight: 600; color: #818cf8; border-bottom: 1px solid var(--border-dark); padding-bottom: 0.4rem;">Configuración de Facturación Electrónica SII (DTE)</h2>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PROVEEDOR DE FACTURACIÓN ELECTRÓNICA</label>
        <select name="config[DTE_PROVEEDOR]" id="dteProveedorSelect" class="form-control" onchange="toggleDteFields()" required>
          <option value="ninguno" <?= ($config['DTE_PROVEEDOR'] ?? 'ninguno') === 'ninguno' ? 'selected' : '' ?>>Ninguno (Solo Comprobante Local / Deshabilitado)</option>
          <option value="mock" <?= ($config['DTE_PROVEEDOR'] ?? '') === 'mock' ? 'selected' : '' ?>>Mock Driver (Simulador de DTE para Pruebas)</option>
          <option value="openfactura" <?= ($config['DTE_PROVEEDOR'] ?? '') === 'openfactura' ? 'selected' : '' ?>>OpenFactura API (Facturacion.cl)</option>
          <option value="haulmer" <?= ($config['DTE_PROVEEDOR'] ?? '') === 'haulmer' ? 'selected' : '' ?>>Haulmer API (Boleta Electrónica)</option>
        </select>
        <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
          Determina con qué proveedor o canal se emitirán las boletas, facturas y notas de crédito del local.
        </p>
      </div>

      <!-- Configuración OpenFactura -->
      <div id="divConfigOpenFactura" style="display: none; flex-direction: column; gap: 1rem; border: 1px dashed rgba(129, 140, 248, 0.3); padding: 1rem; border-radius: 8px;">
        <h3 style="font-size: 0.9rem; font-weight: bold; color: #818cf8; margin: 0;">Parámetros OpenFactura (Facturacion.cl)</h3>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">OPENFACTURA API KEY</label>
          <input type="password" name="config[OPENFACTURA_API_KEY]" value="<?= htmlspecialchars($config['OPENFACTURA_API_KEY'] ?? '') ?>" class="form-control" placeholder="Clave de API de OpenFactura">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">AMBIENTE DE EJECUCIÓN (OPENFACTURA)</label>
          <select name="config[OPENFACTURA_AMBIENTE]" class="form-control">
            <option value="dev" <?= ($config['OPENFACTURA_AMBIENTE'] ?? 'dev') === 'dev' ? 'selected' : '' ?>>Desarrollo / Certificación (dev)</option>
            <option value="prod" <?= ($config['OPENFACTURA_AMBIENTE'] ?? '') === 'prod' ? 'selected' : '' ?>>Producción Real (prod)</option>
          </select>
        </div>
      </div>

      <!-- Configuración Haulmer -->
      <div id="divConfigHaulmer" style="display: none; flex-direction: column; gap: 1rem; border: 1px dashed rgba(251, 191, 36, 0.3); padding: 1rem; border-radius: 8px;">
        <h3 style="font-size: 0.9rem; font-weight: bold; color: #fbbf24; margin: 0;">Parámetros Haulmer API</h3>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">HAULMER API KEY</label>
          <input type="password" name="config[HAULMER_API_KEY]" value="<?= htmlspecialchars($config['HAULMER_API_KEY'] ?? '') ?>" class="form-control" placeholder="Clave API entregada por Haulmer">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">AMBIENTE DE EJECUCIÓN (HAULMER)</label>
          <select name="config[HAULMER_AMBIENTE]" class="form-control">
            <option value="dev" <?= ($config['HAULMER_AMBIENTE'] ?? 'dev') === 'dev' ? 'selected' : '' ?>>Desarrollo / Certificación (dev)</option>
            <option value="prod" <?= ($config['HAULMER_AMBIENTE'] ?? '') === 'prod' ? 'selected' : '' ?>>Producción Real (prod)</option>
          </select>
        </div>
      </div>

      <!-- Parámetros Generales SII -->
      <div id="divConfigSIICommon" style="display: none; flex-direction: column; gap: 1rem;">
        <h3 style="font-size: 0.9rem; font-weight: bold; color: var(--text-muted); margin: 0;">Datos del Contribuyente (SII)</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div>
            <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CÓDIGO ACTIVIDAD ECONÓMICA SII (ACTECO)</label>
            <input type="text" name="config[MINIMARKET_ACTECO]" value="<?= htmlspecialchars($config['MINIMARKET_ACTECO'] ?? '471100') ?>" class="form-control" placeholder="Ej: 471100">
          </div>
          <div>
            <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">COMUNA ORIGEN SII</label>
            <input type="text" name="config[MINIMARKET_COMUNA]" value="<?= htmlspecialchars($config['MINIMARKET_COMUNA'] ?? 'Santiago') ?>" class="form-control" placeholder="Ej: Santiago">
          </div>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">FORMATO DE IMPRESIÓN DTE</label>
          <select name="config[HAULMER_FORMATO_PDF]" class="form-control">
            <option value="80mm" <?= ($config['HAULMER_FORMATO_PDF'] ?? '80mm') === '80mm' ? 'selected' : '' ?>>Ticket 80mm</option>
            <option value="57mm" <?= ($config['HAULMER_FORMATO_PDF'] ?? '') === '57mm' ? 'selected' : '' ?>>Ticket 57mm</option>
          </select>
        </div>
      </div>

      <!-- Mensaje informativo para simulador o ninguno -->
      <div id="divConfigMsg" style="background: rgba(129, 140, 248, 0.1); border: 1px solid rgba(129, 140, 248, 0.2); padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.85rem; color: #94a3b8; line-height: 1.4;">
        <i class="fa-solid fa-circle-info" style="color: #818cf8; margin-right: 0.4rem;"></i>
        <span id="txtDteHelp">Selecciona un proveedor de DTE para comenzar.</span>
      </div>

      <button type="submit" class="btn btn-primary" style="padding: 0.85rem; font-weight: bold;">
        <i class="fa-solid fa-floppy-disk"></i> Guardar Configuración DTE
      </button>
    </form>
  </div>
</div>

<script>
function switchTab(tabId) {
  document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active', 'btn-primary'));
  
  const selectedPane = document.getElementById(tabId);
  const selectedBtn = document.getElementById('btn-' + tabId);
  
  if (selectedPane) selectedPane.style.display = 'block';
  if (selectedBtn) selectedBtn.classList.add('active', 'btn-primary');
}

function toggleDteFields() {
  const provider = document.getElementById('dteProveedorSelect').value;
  const divOF = document.getElementById('divConfigOpenFactura');
  const divHaulmer = document.getElementById('divConfigHaulmer');
  const divSII = document.getElementById('divConfigSIICommon');
  const divMsg = document.getElementById('divConfigMsg');
  const txtHelp = document.getElementById('txtDteHelp');

  divOF.style.display = 'none';
  divHaulmer.style.display = 'none';
  divSII.style.display = 'none';
  divMsg.style.display = 'block';

  if (provider === 'ninguno') {
    txtHelp.innerHTML = "<strong>Modo Comprobante Local Activo:</strong> El sistema NO enviará información al SII. Solo se generarán los comprobantes de venta internos del local. Ideal para operar sin boleta electrónica.";
  } else if (provider === 'mock') {
    txtHelp.innerHTML = "<strong>Modo Simulador (Mock) Activo:</strong> El sistema simulará la emisión de DTEs (boletas, facturas, notas de crédito) de forma instantánea. Generará folios ficticios de prueba. Útil para verificar la interfaz y base de datos sin incurrir en costos ni conexión real.";
  } else if (provider === 'openfactura') {
    divOF.style.display = 'flex';
    divSII.style.display = 'flex';
    divMsg.style.display = 'none';
  } else if (provider === 'haulmer') {
    divHaulmer.style.display = 'flex';
    divSII.style.display = 'flex';
    divMsg.style.display = 'none';
  }
}

// Ejecutar al cargar la página
document.addEventListener("DOMContentLoaded", () => {
  toggleDteFields();
});
</script>
