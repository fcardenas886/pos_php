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
        <th>Nombre Oferta</th>
        <th>Producto</th>
        <th>Tipo Oferta</th>
        <th>Descuento</th>
        <th>Vigencia</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($promociones)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay promociones registradas.</td></tr>
      <?php else: ?>
        <?php foreach ($promociones as $pr): ?>
          <tr>
            <td>#<?= $pr['PromocionID'] ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($pr['Nombre']) ?></td>
            <td><?= htmlspecialchars($pr['ProductoName']) ?></td>
            <td><span class="badge badge-warning"><?= htmlspecialchars($pr['TipoPromocion']) ?></span></td>
            <td style="font-weight: 700; color: var(--success);"><?= $pr['DescuentoValor'] ?>%</td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($pr['FechaInicio'])) ?> al <?= date('d/m/Y', strtotime($pr['FechaFin'])) ?></td>
            <td><span class="badge badge-success">Activa</span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nueva Oferta -->
<div id="promoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 440px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem;">Crear Promoción</h2>
    
    <form method="POST" action="promociones.php" style="display: flex; flex-direction: column; gap: 1rem;">
      <?= csrfField() ?>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE DE LA OFERTA *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Descuento 15% Fin de Semana">
      </div>

      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">PRODUCTO EN OFERTA *</label>
        <select name="producto_id" class="form-control" required>
          <option value="">-- Selecciona Producto --</option>
          <?php foreach ($productosList as $p): ?>
            <option value="<?= $p['ProductoID'] ?>"><?= htmlspecialchars($p['Nombre']) ?> (Precio: <?= formatCLP($p['PrecioVenta']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TIPO DE DESCUENTO</label>
          <select name="tipo_promo" class="form-control">
            <option value="PORCENTAJE">Porcentaje (%)</option>
            <option value="MONTO_FIJO">Monto Fijo ($)</option>
          </select>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">VALOR DESCUENTO *</label>
          <input type="number" step="0.01" name="descuento_valor" class="form-control" required placeholder="Ej: 15">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">FECHA INICIO</label>
          <input type="date" name="fecha_inicio" value="<?= date('Y-m-d') ?>" class="form-control">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">FECHA FIN</label>
          <input type="date" name="fecha_fin" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" class="form-control">
        </div>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Guardar Oferta</button>
        <button type="button" onclick="document.getElementById('promoModal').style.display='none'" class="btn btn-secondary btn-block">Cancelar</button>
      </div>
    </form>
  </div>
</div>
