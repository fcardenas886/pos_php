<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Cotizaciones</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Presupuestos y ventas pausadas. Cárgalas en la caja para cobrarlas.</p>
  </div>
  <button onclick="abrirNuevaCotizacion()" class="btn btn-primary">
    <i class="fa-solid fa-file-circle-plus"></i> Nueva cotización
  </button>
</div>

<div class="table-card">
  <div class="table-header">
    <div style="display: flex; align-items: center; gap: 0.6rem;">
      <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">ESTADO</label>
      <select id="filtroEstado" class="form-control" style="width: auto; padding: 0.45rem 0.75rem;" onchange="cargarCotizaciones()">
        <option value="todas">Todas</option>
        <option value="Pendiente" selected>Pendientes</option>
        <option value="Restaurada">Restauradas</option>
        <option value="Convertida">Convertidas</option>
        <option value="Anulada">Anuladas</option>
      </select>
    </div>
    <div id="cotizTotalInfo" style="color: var(--text-muted); font-size: 0.85rem;"></div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>N°</th>
        <th>Fecha</th>
        <th>Cliente</th>
        <th style="text-align: center;">Ítems</th>
        <th style="text-align: right;">Total</th>
        <th>Estado</th>
        <th>Usuario</th>
        <th style="text-align: center; width: 230px;">Acciones</th>
      </tr>
    </thead>
    <tbody id="cotizTbody">
      <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">Cargando&hellip;</td></tr>
    </tbody>
  </table>
</div>

<!-- Modal Nueva Cotización -->
<div id="nuevaCotizModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 1.5rem 1rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 680px; max-width: 100%; padding: 1.75rem; box-shadow: var(--shadow-lg); margin: 1.5rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #818cf8;"><i class="fa-solid fa-file-circle-plus"></i> Nueva cotización</h2>
      <button onclick="cerrarNuevaCotiz()" class="btn btn-secondary" style="padding: 0.3rem 0.6rem;">&times;</button>
    </div>

    <div style="display: flex; flex-direction: column; gap: 1.1rem;">
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">CLIENTE (opcional)</label>
        <select id="cotizClienteSelect" class="form-control">
          <option value="">Sin cliente</option>
          <?php foreach ($clientes as $cl): ?>
            <option value="<?= $cl['ClienteID'] ?>"><?= htmlspecialchars($cl['Nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border-dark); border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <div style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);">AGREGAR PRODUCTO</div>
        <div style="display: grid; grid-template-columns: 1fr 90px 110px auto; gap: 0.6rem; align-items: end;">
          <div>
            <label style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">PRODUCTO</label>
            <select id="cotizAddProducto" class="form-control">
              <option value="">-- Selecciona --</option>
              <?php foreach ($productosList as $p): ?>
                <option value="<?= $p['ProductoID'] ?>" data-nombre="<?= htmlspecialchars($p['Nombre']) ?>" data-precio="<?= (int)$p['PrecioVenta'] ?>">
                  <?= htmlspecialchars($p['Nombre']) ?> ($<?= number_format($p['PrecioVenta'], 0, ',', '.') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">CANT.</label>
            <input type="number" id="cotizAddCant" class="form-control" value="1" min="0.001" step="0.001">
          </div>
          <div>
            <label style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">PRECIO $</label>
            <input type="number" id="cotizAddPrecio" class="form-control" value="0" min="0">
          </div>
          <button type="button" onclick="cotizAgregarItem()" class="btn btn-secondary" style="padding: 0.6rem 0.9rem;"><i class="fa-solid fa-plus"></i></button>
        </div>
      </div>

      <table class="table" style="font-size: 0.9rem;">
        <thead>
          <tr>
            <th>Producto</th>
            <th style="text-align: center; width: 70px;">Cant.</th>
            <th style="text-align: right; width: 100px;">Precio</th>
            <th style="text-align: right; width: 110px;">Subtotal</th>
            <th style="width: 40px;"></th>
          </tr>
        </thead>
        <tbody id="cotizItemsBody">
          <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 1rem;">Sin productos aún.</td></tr>
        </tbody>
      </table>

      <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--border-dark); padding-top: 0.75rem;">
        <span style="font-size: 1.1rem; font-weight: 700;">TOTAL</span>
        <span id="cotizTotalNuevo" style="font-size: 1.25rem; font-weight: 800; color: var(--success);">$0</span>
      </div>

      <button type="button" id="btnGuardarCotiz" onclick="guardarNuevaCotizacion()" class="btn btn-primary btn-block" style="padding: 0.85rem;">
        <i class="fa-solid fa-floppy-disk"></i> Guardar cotización
      </button>
    </div>
  </div>
</div>

<!-- Modal Ver Cotización -->
<div id="verCotizModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 1.5rem 1rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 560px; max-width: 100%; padding: 1.75rem; box-shadow: var(--shadow-lg); margin: 1.5rem auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h2 id="verCotizTitulo" style="font-size: 1.2rem; font-weight: 700;">Cotización</h2>
      <button onclick="document.getElementById('verCotizModal').style.display='none'" class="btn btn-secondary" style="padding: 0.3rem 0.6rem;">&times;</button>
    </div>
    <div id="verCotizBody"></div>
  </div>
</div>

<script>
  window.CSRF_TOKEN = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]').content;
</script>
<script src="assets/js/cotizaciones.js?v=<?= APP_VERSION ?>"></script>
