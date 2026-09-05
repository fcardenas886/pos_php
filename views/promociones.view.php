<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Promociones y Ofertas Especiales</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Configuración de descuentos por volumen, precios de oferta y fechas de vigencia</p>
  </div>
  <button onclick="document.getElementById('promoModal').style.display='flex'" class="btn btn-primary">
    <i class="fa-solid fa-tags"></i> Nueva Oferta
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Listado de Ofertas Vigentes</h2>
    <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($promociones) ?> promociones</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N°</th>
        <th>Producto</th>
        <th>Tipo Oferta</th>
        <th>Detalle de Oferta / Descuento</th>
        <th>Vigencia</th>
        <th>Estado</th>
        <th style="text-align: center; width: 100px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($promociones)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay promociones registradas.</td></tr>
      <?php else: ?>
        <?php foreach ($promociones as $pr): ?>
          <?php
            // Determinar descripción formateada en base al tipo de promoción
            if ($pr['Tipo'] === 'DESCUENTO_UNIT') {
                $tipoLabel = 'Descuento Unitario';
                $badgeClass = 'badge-success';
                $detalleHtml = '<strong style="color: var(--success);">' . number_format($pr['DescuentoPorcentaje'], 0) . '% de Descuento</strong>';
            } else { // MULTIBUY
                $tipoLabel = 'Volumen / Pack';
                $badgeClass = 'badge-warning';
                $detalleHtml = 'Llevar <strong style="color: #fff;">' . number_format($pr['CantidadMinima'], 0) . '</strong> por <strong style="color: var(--success);">' . formatCLP($pr['PrecioOferta']) . '</strong>';
            }
          ?>
          <tr>
            <td>#<?= $pr['PromocionID'] ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($pr['ProductoName']) ?></td>
            <td><span class="badge <?= $badgeClass ?>"><?= $tipoLabel ?></span></td>
            <td><?= $detalleHtml ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($pr['FechaInicio'])) ?> al <?= date('d/m/Y', strtotime($pr['FechaFin'])) ?></td>
            <td>
              <span class="badge <?= $pr['Activa'] ? 'badge-success' : 'badge-danger' ?>">
                <?= $pr['Activa'] ? 'Activa' : 'Inactiva' ?>
              </span>
            </td>
            <td style="text-align: center;">
              <form method="POST" action="promociones.php" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta promoción?')">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="promo_id" value="<?= $pr['PromocionID'] ?>">
                <button type="submit" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);" title="Eliminar Promoción">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nueva Oferta -->
<div id="promoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 450px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem; color: #818cf8;">Crear Promoción</h2>
    
    <form method="POST" action="promociones.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PRODUCTO EN OFERTA *</label>
        <select name="producto_id" class="form-control" required>
          <option value="">-- Selecciona Producto --</option>
          <?php foreach ($productosList as $p): ?>
            <option value="<?= $p['ProductoID'] ?>"><?= htmlspecialchars($p['Nombre']) ?> (Precio: <?= formatCLP($p['PrecioVenta']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TIPO DE OFERTA *</label>
        <select id="promoTipo" name="tipo" class="form-control" onchange="togglePromoFields()" required>
          <option value="DESCUENTO_UNIT">Descuento Unitario (Porcentaje %)</option>
          <option value="MULTIBUY">Promoción por Volumen (Pack / Llevar X por $Y)</option>
        </select>
      </div>

      <!-- Campo para Descuento Unitario (Porcentaje) -->
      <div id="divDescuentoUnit">
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PORCENTAJE DE DESCUENTO (%) *</label>
        <input type="number" step="0.1" name="descuento_porcentaje" id="inputDescPorc" class="form-control" placeholder="Ej: 15" value="0">
      </div>

      <!-- Campos para Multibuy (Volumen) -->
      <div id="divMultibuy" style="display: none; grid-template-columns: 1fr 1.2fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CANT. MÍNIMA *</label>
          <input type="number" step="1" name="cantidad_minima" id="inputCantMin" class="form-control" placeholder="Ej: 3" value="3">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PRECIO OFERTA ($) *</label>
          <input type="number" name="precio_oferta" id="inputPrecioOf" class="form-control" placeholder="Ej: 4000" value="0">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">FECHA INICIO</label>
          <input type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" class="form-control" required>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">FECHA FIN</label>
          <input type="date" name="fecha_fin" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" class="form-control" required>
        </div>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar Oferta</button>
        <button type="button" onclick="document.getElementById('promoModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function togglePromoFields() {
  const tipo = document.getElementById('promoTipo').value;
  const divUnit = document.getElementById('divDescuentoUnit');
  const divMulti = document.getElementById('divMultibuy');
  const inputPorc = document.getElementById('inputDescPorc');
  const inputCant = document.getElementById('inputCantMin');
  const inputPrice = document.getElementById('inputPrecioOf');

  if (tipo === 'DESCUENTO_UNIT') {
    divUnit.style.display = 'block';
    divMulti.style.display = 'none';
    inputPorc.required = true;
    inputCant.required = false;
    inputPrice.required = false;
  } else {
    divUnit.style.display = 'none';
    divMulti.style.display = 'grid';
    inputPorc.required = false;
    inputCant.required = true;
    inputPrice.required = true;
  }
}
// Ejecutar una vez al inicio
togglePromoFields();
</script>
