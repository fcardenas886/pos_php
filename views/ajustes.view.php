<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Ajustes Manuales de Stock</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Corrección de diferencias, mermas, pérdidas o ingresos manuales de inventario</p>
  </div>
  <button onclick="abrirAjusteModal()" class="btn btn-primary">
    <i class="fa-solid fa-sliders"></i> Nuevo Ajuste de Stock
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
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Ajustes de Stock</h2>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th>N° Ajuste</th>
        <th>Fecha / Hora</th>
        <th>Usuario</th>
        <th>Tipo Ajuste</th>
        <th>Referencia / N.C.</th>
        <th style="width: 320px;">Detalles del Ajuste (Producto, Tipo, Cant.)</th>
        <th>Motivo</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($historialAjustes)): ?>
        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay ajustes registrados.</td></tr>
      <?php else: ?>
        <?php foreach ($historialAjustes as $aj): ?>
          <?php 
            $esDev = !empty($aj['Proveedor']);
            $tipoLabel = $esDev ? 'Devolución Proveedor' : 'Ajuste Interno';
            $badgeClass = $esDev ? 'badge-warning' : 'badge-success';
            $proveedorText = $esDev ? '<div style="font-size:0.75rem; color:var(--text-muted); font-weight:bold; margin-top:0.2rem;">Prov: ' . htmlspecialchars($aj['Proveedor']) . '</div>' : '';
          ?>
          <tr>
            <td>#<?= $aj['AjusteStockID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($aj['FechaAjuste'])) ?></td>
            <td><?= htmlspecialchars($aj['Usuario']) ?></td>
            <td>
              <span class="badge <?= $badgeClass ?>"><?= $tipoLabel ?></span>
              <?= $proveedorText ?>
            </td>
            <td><code><?= htmlspecialchars($aj['DocReferencia'] ?: '-') ?></code></td>
            <td style="font-size: 0.85rem; color: #fff; font-weight: 500; line-height: 1.4; word-wrap: break-word; white-space: normal;">
              <?= htmlspecialchars($aj['DetallesProductos'] ?: 'Sin productos') ?>
            </td>
            <td style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($aj['Motivo']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nuevo Ajuste -->
<div id="ajusteModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 1.5rem 1rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 620px; padding: 1.75rem; box-shadow: var(--shadow-lg); margin: 1.5rem auto;">
    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: #818cf8;"><i class="fa-solid fa-sliders"></i> Realizar Ajuste de Stock</h2>
    
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
      
      <!-- Checkbox Devolución Proveedor -->
      <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(15,23,42,0.3); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-dark);">
        <input type="checkbox" id="ajusteEsDevolucion" onchange="toggleAjusteEsDevolucion()" style="width: 18px; height: 18px; cursor: pointer;">
        <label for="ajusteEsDevolucion" style="font-size: 0.85rem; font-weight: 600; cursor: pointer; user-select: none; color: #fbbf24;">Es Devolución a Proveedor</label>
      </div>

      <!-- Datos del Proveedor (Ocultos por defecto) -->
      <div id="ajusteProveedorRow" style="display: none; grid-template-columns: 1.2fr 1fr; gap: 0.75rem; background: rgba(15,23,42,0.3); padding: 1rem; border-radius: 10px; border: 1px dashed var(--warning);">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">PROVEEDOR *</label>
          <select id="ajusteProveedorSelect" class="form-control">
            <option value="">-- Selecciona Proveedor --</option>
            <?php foreach ($proveedoresList as $prov): ?>
              <option value="<?= $prov['ProveedorID'] ?>"><?= htmlspecialchars($prov['RazonSocial']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">REF / NOTA DE CRÉDITO</label>
          <input type="text" id="ajusteDocRef" class="form-control" placeholder="Ej: NC-123">
        </div>
      </div>

      <!-- Area de Entrada Rápida de Producto -->
      <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border-dark); border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <div style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);">AGREGAR PRODUCTO AL AJUSTE:</div>
        
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.3rem;">PRODUCTO *</label>
          <select id="ajusteAddProducto" class="form-control">
            <option value="">-- Selecciona Producto --</option>
            <?php foreach ($productosList as $p): ?>
              <option value="<?= $p['ProductoID'] ?>" data-nombre="<?= htmlspecialchars($p['Nombre']) ?>" data-stock="<?= $p['Stock'] ?>">
                <?= htmlspecialchars($p['Nombre']) ?> (Stock: <?= $p['Stock'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 90px; gap: 0.75rem; align-items: flex-end;">
          <div>
            <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.3rem;">TIPO MOVIMIENTO *</label>
            <select id="ajusteAddTipo" class="form-control">
              <option value="ENTRADA">Entrada (+)</option>
              <option value="SALIDA">Salida (- Merma/Pérdida)</option>
            </select>
            <!-- Label estático para devoluciones -->
            <input type="text" id="ajusteAddTipoFijo" class="form-control" value="Salida (-)" readonly style="display: none; background: rgba(239, 68, 68, 0.15); border-color: var(--danger); color: #f87171; font-weight: bold; padding: 0.75rem 1rem; border-radius: 8px;">
          </div>
          <div>
            <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.3rem;">CANTIDAD *</label>
            <input type="number" step="0.001" id="ajusteAddCantidad" class="form-control" placeholder="Ej: 5">
          </div>
          <button type="button" onclick="agregarProductoAAjuste()" class="btn btn-secondary" style="padding: 0.75rem; width: 100%; border-radius: 8px;"><i class="fa-solid fa-plus"></i> Agregar</button>
        </div>
      </div>

      <!-- Tabla Grilla de items agregados -->
      <div style="border: 1px solid var(--border-dark); border-radius: 12px; overflow: hidden; background: rgba(15,23,42,0.2);">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
          <thead>
            <tr style="background: rgba(0,0,0,0.3); border-bottom: 1px solid var(--border-dark);">
              <th style="padding: 0.65rem 1rem; color: var(--text-muted); font-weight: bold;">Producto</th>
              <th style="padding: 0.65rem 1rem; color: var(--text-muted); font-weight: bold; width: 120px;">Movimiento</th>
              <th style="padding: 0.65rem 1rem; color: var(--text-muted); font-weight: bold; text-align: right; width: 100px;">Cantidad</th>
              <th style="padding: 0.65rem 1rem; color: var(--text-muted); font-weight: bold; text-align: center; width: 70px;">Quitar</th>
            </tr>
          </thead>
          <tbody id="ajusteItemsGrilla">
            <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">Aún no agregas productos a este ajuste.</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Motivo -->
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">MOTIVO DEL AJUSTE *</label>
        <input type="text" id="ajusteMotivo" class="form-control" required placeholder="Ej: Merma por vencimiento, Devolución a proveedor">
      </div>

      <!-- Acciones de guardar / cerrar -->
      <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
        <button type="button" id="btnConfirmarAjuste" onclick="confirmarAjuste()" class="btn btn-primary btn-block" style="padding: 0.7rem;"><i class="fa-solid fa-floppy-disk"></i> Aplicar Ajuste</button>
        <button type="button" onclick="cerrarAjusteModal()" class="btn btn-secondary btn-block" style="padding: 0.7rem;">Cancelar</button>
      </div>

    </div>
  </div>
</div>

<script>
let itemsAjuste = [];

function abrirAjusteModal() {
  itemsAjuste = [];
  document.getElementById('ajusteEsDevolucion').checked = false;
  document.getElementById('ajusteProveedorSelect').value = '';
  document.getElementById('ajusteDocRef').value = '';
  document.getElementById('ajusteAddProducto').selectedIndex = 0;
  document.getElementById('ajusteAddCantidad').value = '';
  document.getElementById('ajusteMotivo').value = '';
  
  toggleAjusteEsDevolucion();
  renderItemsAjuste();
  document.getElementById('ajusteModal').style.display = 'flex';
}

function cerrarAjusteModal() {
  document.getElementById('ajusteModal').style.display = 'none';
}

function toggleAjusteEsDevolucion() {
  const esDev = document.getElementById('ajusteEsDevolucion').checked;
  const provRow = document.getElementById('ajusteProveedorRow');
  const typeSelect = document.getElementById('ajusteAddTipo');
  const typeFijo = document.getElementById('ajusteAddTipoFijo');

  if (esDev) {
    provRow.style.display = 'grid';
    typeSelect.style.display = 'none';
    typeFijo.style.display = 'block';
  } else {
    provRow.style.display = 'none';
    typeSelect.style.display = 'block';
    typeFijo.style.display = 'none';
  }
  
  // Limpiar e invalidar items si cambia el tipo principal para no mezclar entradas con devoluciones
  if (itemsAjuste.length > 0) {
    itemsAjuste = [];
    renderItemsAjuste();
  }
}

function agregarProductoAAjuste() {
  const select = document.getElementById('ajusteAddProducto');
  const option = select.options[select.selectedIndex];
  const cantidad = parseFloat(document.getElementById('ajusteAddCantidad').value) || 0;
  
  if (!option || option.value === "") {
    alert("Selecciona un producto válido.");
    return;
  }
  if (cantidad <= 0) {
    alert("Ingresa una cantidad mayor a 0.");
    return;
  }

  const esDev = document.getElementById('ajusteEsDevolucion').checked;
  const tipo = esDev ? 'SALIDA' : document.getElementById('ajusteAddTipo').value;
  const productoID = option.value;
  const nombre = option.getAttribute('data-nombre');

  // Comprobar si ya está en la lista
  const yaExiste = itemsAjuste.find(i => i.producto_id == productoID && i.tipo_movimiento == tipo);
  if (yaExiste) {
    yaExiste.cantidad += cantidad;
  } else {
    itemsAjuste.push({ producto_id: productoID, nombre, tipo_movimiento: tipo, cantidad });
  }

  // Reset inputs
  select.selectedIndex = 0;
  document.getElementById('ajusteAddCantidad').value = '';

  renderItemsAjuste();
}

function quitarItemAjuste(index) {
  itemsAjuste.splice(index, 1);
  renderItemsAjuste();
}

function renderItemsAjuste() {
  const tbody = document.getElementById('ajusteItemsGrilla');
  if (itemsAjuste.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">Aún no agregas productos a este ajuste.</td></tr>`;
    return;
  }

  tbody.innerHTML = itemsAjuste.map((item, idx) => {
    const isEntrada = item.tipo_movimiento === 'ENTRADA';
    const badgeStyle = isEntrada 
      ? 'background: rgba(16,185,129,0.15); border:1px solid var(--success); color:#34d399; padding: 0.15rem 0.4rem; border-radius:4px; font-size:0.75rem; font-weight:bold;'
      : 'background: rgba(239,68,68,0.15); border:1px solid var(--danger); color:#f87171; padding: 0.15rem 0.4rem; border-radius:4px; font-size:0.75rem; font-weight:bold;';
    
    return `
      <tr style="border-bottom: 1px solid var(--border-dark);">
        <td style="padding: 0.65rem 1rem; font-weight: 600; color: #fff;">${item.nombre}</td>
        <td style="padding: 0.65rem 1rem;"><span style="${badgeStyle}">${item.tipo_movimiento}</span></td>
        <td style="padding: 0.65rem 1rem; text-align: right; font-weight: bold; font-family: monospace;">${item.cantidad}</td>
        <td style="padding: 0.65rem 1rem; text-align: center;">
          <button type="button" onclick="quitarItemAjuste(${idx})" class="btn btn-secondary" style="padding: 0.15rem 0.4rem; font-size: 0.75rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

async function confirmarAjuste() {
  if (itemsAjuste.length === 0) {
    alert("Debes agregar al menos un producto.");
    return;
  }

  const esDev = document.getElementById('ajusteEsDevolucion').checked;
  const proveedorID = document.getElementById('ajusteProveedorSelect').value;
  const docRef = document.getElementById('ajusteDocRef').value.trim();
  const motivo = document.getElementById('ajusteMotivo').value.trim();

  if (esDev && !proveedorID) {
    alert("Por favor, selecciona un proveedor para la devolución.");
    return;
  }
  if (!motivo) {
    alert("Ingresa el motivo del ajuste.");
    return;
  }

  const btn = document.getElementById('btnConfirmarAjuste');
  btn.disabled = true;

  try {
    const res = await fetch('api/registrar_ajuste.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({
        motivo,
        es_devolucion: esDev,
        proveedor_id: esDev ? proveedorID : null,
        doc_referencia: esDev ? docRef : null,
        items: itemsAjuste
      })
    });

    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    alert("Ajuste de stock registrado exitosamente.");
    cerrarAjusteModal();
    location.reload();

  } catch (err) {
    alert("Error al registrar el ajuste: " + err.message);
  } finally {
    btn.disabled = false;
  }
}
</script>
