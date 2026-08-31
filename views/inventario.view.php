<!-- ===================== LISTA ===================== -->
<div id="invListaView">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h1 style="font-size: 1.5rem; font-weight: 700;">Toma de Inventario</h1>
      <p style="color: var(--text-muted); font-size: 0.9rem;">Cuenta el stock físico, compáralo con el sistema y ajusta las diferencias de una vez.</p>
    </div>
    <button onclick="abrirNuevaToma()" class="btn btn-primary">
      <i class="fa-solid fa-clipboard-list"></i> Nueva toma
    </button>
  </div>

  <div class="table-card">
    <div class="table-header"><h2 style="font-size: 1.1rem; font-weight: 600;">Tomas de inventario</h2></div>
    <table class="table">
      <thead>
        <tr>
          <th>N°</th><th>Nombre</th><th>Fecha</th><th>Estado</th>
          <th style="text-align:center;">Contados</th>
          <th style="text-align:center;">Dif. total (u.)</th>
          <th>Usuario</th>
          <th style="text-align:center; width: 170px;">Acciones</th>
        </tr>
      </thead>
      <tbody id="invTbody">
        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:2rem;">Cargando&hellip;</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ===================== CONTEO ===================== -->
<div id="invConteoView" style="display: none;">
  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
      <a href="inventario.php" style="color: var(--text-muted); font-size: 0.85rem; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Volver a la lista</a>
      <h1 id="invConteoTitulo" style="font-size: 1.4rem; font-weight: 700; margin-top: 0.3rem;">Conteo</h1>
      <p id="invConteoMeta" style="color: var(--text-muted); font-size: 0.85rem;"></p>
    </div>
    <div id="invConteoAcciones" style="display: flex; gap: 0.5rem; flex-wrap: wrap;"></div>
  </div>

  <div class="table-card">
    <div class="table-header" style="gap: 0.75rem; flex-wrap: wrap;">
      <div style="display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
        <select id="invFiltroCat" class="form-control" style="width: auto; padding: 0.45rem 0.75rem;" onchange="renderConteo()">
          <option value="">Todas las categorías</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['CategoriaID'] ?>"><?= htmlspecialchars($c['Nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" id="invBuscar" class="form-control" placeholder="Buscar producto&hellip;" style="width: 220px; padding: 0.45rem 0.75rem;" oninput="renderConteo()">
        <label style="font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.35rem;">
          <input type="checkbox" id="invSoloPendientes" onchange="renderConteo()"> Solo sin contar
        </label>
      </div>
      <div id="invConteoResumen" style="color: var(--text-muted); font-size: 0.85rem;"></div>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Categoría</th>
          <th style="text-align:center;">Stock sistema</th>
          <th style="text-align:center; width: 130px;">Cantidad física</th>
          <th style="text-align:center;">Diferencia</th>
        </tr>
      </thead>
      <tbody id="invConteoBody"></tbody>
    </table>
  </div>
</div>

<!-- Modal Nueva toma -->
<div id="nuevaTomaModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; padding: 1.5rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 440px; max-width: 100%; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
      <h2 style="font-size: 1.2rem; font-weight: 700; color: #818cf8;"><i class="fa-solid fa-clipboard-list"></i> Nueva toma de inventario</h2>
      <button onclick="document.getElementById('nuevaTomaModal').style.display='none'" class="btn btn-secondary" style="padding: 0.3rem 0.6rem;">&times;</button>
    </div>
    <div style="display: flex; flex-direction: column; gap: 1rem;">
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">NOMBRE / REFERENCIA</label>
        <input type="text" id="tomaNombre" class="form-control" placeholder="Ej: Inventario mensual octubre">
      </div>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">FECHA Y HORA DEL CONTEO</label>
        <input type="datetime-local" id="tomaFecha" class="form-control">
      </div>
      <button type="button" id="btnCrearToma" onclick="crearToma()" class="btn btn-primary btn-block" style="padding: 0.75rem;">
        <i class="fa-solid fa-play"></i> Crear y empezar a contar
      </button>
    </div>
  </div>
</div>

<script>
  window.CSRF_TOKEN = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]').content;
  window.INVENTARIO_ID = <?= $inventarioID > 0 ? $inventarioID : 'null' ?>;
</script>
<script src="assets/js/inventario.js?v=<?= APP_VERSION ?>"></script>
