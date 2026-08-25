<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Devoluciones y Notas de Crédito</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Reembolso de productos a clientes y reintegración de stock a Kardex</p>
  </div>
  <button onclick="abrirModalDevolucion()" class="btn btn-primary">
    <i class="fa-solid fa-rotate-left"></i> Registrar Devolución
  </button>
</div>

<?php if (!empty($message)): ?>
  <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #34d399; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem;">
    <i class="fa-solid fa-circle-check"></i> <?= $message ?>
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
            <td><span class="badge badge-warning"><?= htmlspecialchars($d['MetodoDevolucion']) ?></span></td>
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
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 480px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #818cf8;">Procesar Devolución</h2>
    
    <!-- Paso 1: Buscar Boleta -->
    <div style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 1.25rem;">
      <div style="flex: 1;">
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">N° BOLETA / ID VENTA *</label>
        <input type="number" id="devVentaIdInput" class="form-control" placeholder="Ej: 60" style="font-size: 1.1rem; font-weight: bold;">
      </div>
      <button type="button" onclick="buscarBoletaDevolucion()" class="btn btn-primary" style="padding: 0.65rem 1.25rem;"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
    </div>

    <!-- Mensaje de error dinámico -->
    <div id="devErrorMsg" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #f87171; padding: 0.65rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.25rem;">
    </div>

    <!-- Formulario de Devolución (Paso 2 y 3: Se muestra al encontrar boleta) -->
    <form method="POST" action="devoluciones.php" id="detallesDevolucionForm" style="display: none; flex-direction: column; gap: 1.25rem; border-top: 1px solid var(--border-dark); padding-top: 1.25rem;">
      <?= csrfField() ?>
      <input type="hidden" name="venta_id" id="hiddenVentaId">

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">SELECCIONAR PRODUCTO A DEVOLVER *</label>
        <select name="producto_id" id="devProductoSelect" class="form-control" required onchange="actualizarInfoProductoDev()">
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">CANTIDAD *</label>
          <input type="number" step="0.001" name="cantidad" id="devCantidadInput" class="form-control" required value="1" min="0.001" style="font-weight: 700;">
          <span style="font-size: 0.75rem; color: #818cf8; font-weight: 600;" id="devMaxCantLabel"></span>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">PROCESO / REEMBOLSO *</label>
          <select name="metodo_devolucion" class="form-control" required>
            <option value="Efectivo">Efectivo (Sacar de Caja)</option>
            <option value="Tarjeta">Tarjeta Bancaria</option>
            <option value="Nota de Credito">Nota de Crédito (Generar Vale)</option>
          </select>
        </div>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">MOTIVO DE LA DEVOLUCIÓN *</label>
        <input type="text" name="motivo" class="form-control" required placeholder="Ej: Producto en mal estado / Cambio de producto">
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Confirmar Devolución</button>
        <button type="button" onclick="cerrarDevolucionModal()" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Vale de Devolución Generado (Copia Cliente) -->
<?php if (isset($_SESSION['ultimo_vale'])): ?>
  <div id="valeModal" style="display: flex; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1100; align-items: center; justify-content: center;">
    <div style="background: #fff; color: #000; width: 340px; border-radius: 12px; padding: 1.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: monospace; text-align: center;">
      <div style="border-bottom: 1px dashed #000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.2rem;">MINIMARKET</h2>
        <p style="font-size: 0.8rem;">Vale de Devolución</p>
        <p style="font-size: 0.75rem; color: #555;"><?= $_SESSION['ultimo_vale']['fecha'] ?></p>
      </div>
      <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">Código de Canje:</div>
      <div style="font-size: 1.6rem; font-weight: bold; background: #eee; padding: 0.5rem; border-radius: 6px; letter-spacing: 2px; margin-bottom: 1rem; color: #111;">
        <?= $_SESSION['ultimo_vale']['codigo'] ?>
      </div>
      <div style="font-size: 1rem; margin-bottom: 1.5rem;">
        Monto Disponible: <strong style="font-size: 1.3rem;"><?= formatCLP($_SESSION['ultimo_vale']['monto']) ?></strong>
      </div>
      <div style="display: flex; gap: 0.5rem;">
        <button onclick="window.print()" class="btn btn-primary btn-block" style="font-size: 0.85rem; padding: 0.5rem;">
          <i class="fa-solid fa-print"></i> Imprimir Vale
        </button>
        <button onclick="document.getElementById('valeModal').style.display='none'" class="btn btn-secondary btn-block" style="font-size: 0.85rem; padding: 0.5rem; background: #eee; color: #000; border: 1px solid #ccc;">
          Cerrar
        </button>
      </div>
    </div>
  </div>
  <?php unset($_SESSION['ultimo_vale']); ?>
<?php endif; ?>

<script>
function abrirModalDevolucion() {
  document.getElementById('devVentaIdInput').value = '';
  document.getElementById('devErrorMsg').style.display = 'none';
  document.getElementById('detallesDevolucionForm').style.display = 'none';
  document.getElementById('devModal').style.display = 'flex';
}

function cerrarDevolucionModal() {
  document.getElementById('devModal').style.display = 'none';
}

async function buscarBoletaDevolucion() {
  const ventaId = document.getElementById('devVentaIdInput').value.trim();
  const errorEl = document.getElementById('devErrorMsg');
  const formEl = document.getElementById('detallesDevolucionForm');
  const selectEl = document.getElementById('devProductoSelect');
  const hiddenVId = document.getElementById('hiddenVentaId');

  errorEl.style.display = 'none';
  formEl.style.display = 'none';

  if (!ventaId) {
    errorEl.textContent = 'Ingresa un número de boleta válido.';
    errorEl.style.display = 'block';
    return;
  }

  try {
    const res = await fetch(`api/ver_venta.php?id=${ventaId}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    if (data.venta.estado === 'Anulada') {
      throw new Error('La boleta ingresada ya se encuentra anulada.');
    }

    if (data.detalles.length === 0) {
      throw new Error('La boleta no registra ningún producto.');
    }

    // Poblar select
    selectEl.innerHTML = data.detalles.map(i => `
      <option value="${i.producto_id}" data-cant="${i.cantidad}" data-precio="${i.precio}">
        ${i.nombre} (Comprado: ${i.cantidad} - Precio: $${new Intl.NumberFormat('es-CL').format(i.precio)})
      </option>
    `).join('');

    hiddenVId.value = ventaId;
    formEl.style.display = 'flex';
    actualizarInfoProductoDev();

  } catch (err) {
    errorEl.textContent = err.message;
    errorEl.style.display = 'block';
  }
}

function actualizarInfoProductoDev() {
  const select = document.getElementById('devProductoSelect');
  const option = select.options[select.selectedIndex];
  if (!option) return;

  const maxCant = option.getAttribute('data-cant');
  document.getElementById('devCantidadInput').max = maxCant;
  document.getElementById('devCantidadInput').value = maxCant;
  document.getElementById('devMaxCantLabel').textContent = `Máx. disponible: ${maxCant}`;
}
</script>
