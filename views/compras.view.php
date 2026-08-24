<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700;">Recepción de Compras e Ingreso de Stock</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Registro de facturas/guías de proveedores e incremento automático en Kardex</p>
  </div>
  <button onclick="abrirModalCompra()" class="btn btn-primary">
    <i class="fa-solid fa-truck-ramp-box"></i> Ingresar Mercadería
  </button>
</div>

<!-- Tabla de Historial de Compras -->
<div class="table-card">
  <div class="table-header">
    <h2 style="font-size: 1.1rem; font-weight: 600;">Historial de Compras Recientes</h2>
  </div>
  <table class="table">
    <thead>
      <tr>
        <th>N° Compra</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th>N° Doc / Factura</th>
        <th>Productos Ingresados</th>
        <th>Total Compra</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($historialCompras)): ?>
        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No se han registrado ingresos de mercadería.</td></tr>
      <?php else: ?>
        <?php foreach ($historialCompras as $c): ?>
          <tr>
            <td>#<?= $c['CompraID'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($c['FechaCompra'])) ?></td>
            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($c['Proveedor']) ?></td>
            <td><?= htmlspecialchars($c['NumeroDocumento'] ?: 'Sin N° Doc') ?></td>
            <td style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($c['Items'] ?: 'Detalle de compra') ?></td>
            <td style="font-weight: 700; color: var(--success);"><?= formatCLP($c['MontoTotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Principal de Compra e Ingreso (Wizard 2 Fases) -->
<div id="compraModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: flex-start; overflow-y: auto; padding: 1.5rem 1rem;">
  <div id="compraModalCard" style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 780px; padding: 1.75rem; box-shadow: var(--shadow-lg); margin: 1.5rem auto; transition: width 0.3s ease;">
    
    <!-- FASE 1: Formulario de Recepción y Carga de Grilla -->
    <div id="compraFase1">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #818cf8; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-file-invoice"></i> Recepción de Mercadería (Fase 1 de 2)
      </h2>
      
      <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">PROVEEDOR *</label>
          <select id="compraProveedor" class="form-control" style="font-size: 0.9rem;">
            <?php foreach ($proveedores as $prov): ?>
              <option value="<?= $prov['ProveedorID'] ?>"><?= htmlspecialchars($prov['RazonSocial']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">N° FACTURA O GUÍA</label>
          <input type="text" id="compraNumeroDoc" class="form-control" placeholder="Ej: F-12049" style="font-size: 0.9rem;">
        </div>
      </div>

      <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border-dark); border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
        <label style="font-size: 0.75rem; color: #818cf8; font-weight: 700; display: block; margin-bottom: 0.75rem; text-transform: uppercase;">Añadir Producto a la Factura</label>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1.2fr auto; gap: 0.75rem; align-items: end;">
          <div>
            <label style="font-size: 0.7rem; color: var(--text-muted);">PRODUCTO *</label>
            <select id="compraProductoSelect" class="form-control" style="font-size: 0.85rem; padding: 0.35rem 0.5rem;" onchange="actualizarSugerenciaCosto()">
              <option value="">-- Selecciona --</option>
              <?php foreach ($productos as $prod): ?>
                <option value="<?= $prod['ProductoID'] ?>" data-costo="<?= $prod['CostoCompra'] ?>" data-precio="<?= $prod['PrecioVenta'] ?>">
                  <?= htmlspecialchars($prod['Nombre']) ?> (Stock: <?= $prod['Stock'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="font-size: 0.7rem; color: var(--text-muted);">CANTIDAD *</label>
            <input type="number" id="compraCantidad" step="0.001" min="0.001" class="form-control" placeholder="Ej: 24" style="font-size: 0.85rem; padding: 0.35rem 0.5rem;">
          </div>
          <div>
            <label style="font-size: 0.7rem; color: var(--text-muted);">COSTO UNITARIO ($) *</label>
            <input type="number" id="compraCosto" min="0" class="form-control" placeholder="Ej: 850" style="font-size: 0.85rem; padding: 0.35rem 0.5rem;">
          </div>
          <button type="button" onclick="agregarProductoAGrilla()" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; height: 35px;">
            <i class="fa-solid fa-plus"></i> Agregar
          </button>
        </div>
      </div>

      <!-- Grilla Temporal de Productos -->
      <div style="max-height: 220px; overflow-y: auto; border: 1px solid var(--border-dark); border-radius: 8px; margin-bottom: 1.25rem;">
        <table class="table" style="margin: 0; font-size: 0.85rem;">
          <thead style="background: rgba(0,0,0,0.3); position: sticky; top: 0; z-index: 10;">
            <tr>
              <th>Producto</th>
              <th style="text-align: right; width: 90px;">Cantidad</th>
              <th style="text-align: right; width: 110px;">Costo Unit.</th>
              <th style="text-align: right; width: 120px;">Subtotal</th>
              <th style="text-align: center; width: 70px;">Acciones</th>
            </tr>
          </thead>
          <tbody id="grillaItems">
            <tr>
              <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay productos añadidos a esta recepción.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totales y Acciones -->
      <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-dark); padding-top: 1.25rem;">
        <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
          <div>Neto: <strong id="lblNeto" style="color: #fff;">$0</strong></div>
          <div>IVA (19%): <strong id="lblIva" style="color: #fff;">$0</strong></div>
          <div style="font-size: 1rem; color: var(--success); font-weight: bold;">TOTAL: <span id="lblTotal">$0</span></div>
        </div>
        <div style="display: flex; gap: 0.75rem;">
          <button type="button" onclick="guardarRecepcion()" class="btn btn-success" style="padding: 0.6rem 1.5rem;">
            <i class="fa-solid fa-floppy-disk"></i> Guardar Recepción
          </button>
          <button type="button" onclick="cerrarModalCompra()" class="btn btn-secondary">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- FASE 2: Ajuste de Precios de Venta (Estilo C#) -->
    <div id="compraFase2" style="display: none;">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #34d399; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-tags"></i> Ajustar Precios de Venta (Fase 2 de 2)
      </h2>
      <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem;">
        Revisa los costos nuevos y ajusta el **Precio de Venta** al público. El margen de utilidad (%) se recalculará dinámicamente en tiempo real al tipear.
      </p>

      <div style="max-height: 280px; overflow-y: auto; border: 1px solid var(--border-dark); border-radius: 8px; margin-bottom: 1.5rem;">
        <table class="table" style="margin: 0; font-size: 0.85rem;">
          <thead style="background: rgba(0,0,0,0.3); position: sticky; top: 0; z-index: 10;">
            <tr>
              <th>Producto</th>
              <th style="text-align: right; width: 110px;">Nuevo Costo</th>
              <th style="text-align: right; width: 120px;">Precio Venta Act.</th>
              <th style="text-align: right; width: 140px;">Margen de Utilidad</th>
              <th style="text-align: right; width: 130px;">Nuevo Precio Venta</th>
            </tr>
          </thead>
          <tbody id="grillaPrecios">
            <!-- Se llena por JavaScript tras guardar Fase 1 -->
          </tbody>
        </table>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border-dark); padding-top: 1.25rem;">
        <button type="button" onclick="actualizarPreciosVenta()" class="btn btn-success" style="padding: 0.6rem 1.5rem;">
          <i class="fa-solid fa-check-circle"></i> Actualizar Precios y Finalizar
        </button>
        <button type="button" onclick="saltarActualizacionPrecios()" class="btn btn-secondary">Saltar / Mantener Precios</button>
      </div>
    </div>

  </div>
</div>

<script>
// Estado de la compra en la grilla de Fase 1
let itemsFactura = [];

function abrirModalCompra() {
  itemsFactura = [];
  document.getElementById('compraProveedor').disabled = false;
  document.getElementById('compraNumeroDoc').value = '';
  document.getElementById('compraProductoSelect').value = '';
  document.getElementById('compraCantidad').value = '';
  document.getElementById('compraCosto').value = '';
  renderGrillaFase1();
  
  document.getElementById('compraFase1').style.display = 'block';
  document.getElementById('compraFase2').style.display = 'none';
  document.getElementById('compraModalCard').style.width = '780px';
  document.getElementById('compraModal').style.display = 'flex';
}

function cerrarModalCompra() {
  document.getElementById('compraModal').style.display = 'none';
}

function actualizarSugerenciaCosto() {
  const sel = document.getElementById('compraProductoSelect');
  const opt = sel.options[sel.selectedIndex];
  if (opt && opt.value !== '') {
    const costo = opt.dataset.costo || '0';
    document.getElementById('compraCosto').value = costo;
  } else {
    document.getElementById('compraCosto').value = '';
  }
}

function fmt(val) {
  return '$' + new Intl.NumberFormat('es-CL').format(val);
}

function agregarProductoAGrilla() {
  const selectProd = document.getElementById('compraProductoSelect');
  const pid = parseInt(selectProd.value) || 0;
  const opt = selectProd.options[selectProd.selectedIndex];
  
  const cant = parseFloat(document.getElementById('compraCantidad').value) || 0;
  const costo = parseInt(document.getElementById('compraCosto').value) || 0;

  if (pid <= 0 || cant <= 0 || costo < 0) {
    alert('Por favor selecciona un producto, cantidad válida mayor a 0 y costo unitario.');
    return;
  }

  // Verificar si el producto ya está en la grilla
  const indexExistente = itemsFactura.findIndex(item => item.producto_id === pid);
  if (indexExistente !== -1) {
    if (confirm(`El producto '${opt.text.split('(')[0].trim()}' ya está en la factura. ¿Deseas reemplazar su cantidad y costo por los nuevos ingresados?`)) {
      itemsFactura[indexExistente].cantidad = cant;
      itemsFactura[indexExistente].costo_unitario = costo;
      itemsFactura[indexExistente].subtotal = Math.round(cant * costo);
    }
  } else {
    itemsFactura.push({
      producto_id: pid,
      nombre: opt.text.split('(')[0].trim(),
      cantidad: cant,
      costo_unitario: costo,
      subtotal: Math.round(cant * costo)
    });
  }

  // Limpiar campos de producto
  selectProd.value = '';
  document.getElementById('compraCantidad').value = '';
  document.getElementById('compraCosto').value = '';

  renderGrillaFase1();
}

function eliminarDeGrilla(index) {
  itemsFactura.splice(index, 1);
  renderGrillaFase1();
}

function renderGrillaFase1() {
  const tbody = document.getElementById('grillaItems');
  if (itemsFactura.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay productos añadidos a esta recepción.</td>
      </tr>
    `;
    document.getElementById('lblNeto').textContent = '$0';
    document.getElementById('lblIva').textContent = '$0';
    document.getElementById('lblTotal').textContent = '$0';
    return;
  }

  let total = 0;
  let html = '';
  
  itemsFactura.forEach((item, index) => {
    total += item.subtotal;
    html += `
      <tr>
        <td style="font-weight: 600; color: #fff;">${escapeHtml(item.nombre)}</td>
        <td style="text-align: right;">${item.cantidad}</td>
        <td style="text-align: right;">${fmt(item.costo_unitario)}</td>
        <td style="text-align: right; font-weight: bold; color: var(--success);">${fmt(item.subtotal)}</td>
        <td style="text-align: center;">
          <button type="button" onclick="eliminarDeGrilla(${index})" class="btn btn-secondary" style="padding: 0.15rem 0.4rem; font-size: 0.75rem; color: var(--danger);">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;

  const neto = Math.round(total / 1.19);
  const iva = total - neto;

  document.getElementById('lblNeto').textContent = fmt(neto);
  document.getElementById('lblIva').textContent = fmt(iva);
  document.getElementById('lblTotal').textContent = fmt(total);
}

// Función auxiliar para escapar caracteres HTML
function escapeHtml(text) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Guardar Fase 1 y pasar a Fase 2
let productosCompraFase2 = [];

async function guardarRecepcion() {
  if (itemsFactura.length === 0) {
    alert('Debes ingresar al menos un producto a la factura antes de guardar.');
    return;
  }

  const provId = parseInt(document.getElementById('compraProveedor').value) || 0;
  const numDoc = document.getElementById('compraNumeroDoc').value.trim();

  try {
    const res = await fetch('api/registrar_compra.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        proveedor_id: provId,
        numero_documento: numDoc,
        items: itemsFactura
      })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    // Recepción exitosa, guardamos datos para Fase 2
    productosCompraFase2 = data.productos;
    cargarGrillaFase2();
  } catch (err) {
    alert('Error al registrar la compra: ' + err.message);
  }
}

function cargarGrillaFase2() {
  // Cambiar visual del modal a Fase 2
  document.getElementById('compraFase1').style.display = 'none';
  document.getElementById('compraFase2').style.display = 'block';
  document.getElementById('compraModalCard').style.width = '820px';

  const tbody = document.getElementById('grillaPrecios');
  let html = '';

  productosCompraFase2.forEach(prod => {
    // Calcular el margen inicial con el precio actual
    const margin = prod.PrecioVentaActual > 0 ? Math.round(((prod.PrecioVentaActual - prod.CostoCompra) / prod.PrecioVentaActual) * 100) : 0;
    const badgeColor = margin >= 20 ? '#10b981' : (margin >= 5 ? '#f59e0b' : '#ef4444');

    html += `
      <tr data-pid="${prod.ProductoID}" data-costo="${prod.CostoCompra}">
        <td style="font-weight: 600; color: #fff;">${escapeHtml(prod.Nombre)}</td>
        <td style="text-align: right; font-weight: bold;">${fmt(prod.CostoCompra)}</td>
        <td style="text-align: right; color: var(--text-muted);">${fmt(prod.PrecioVentaActual)}</td>
        <td style="text-align: right;">
          <span class="badge" id="lblMargin_${prod.ProductoID}" style="background: ${badgeColor}; color:#fff; font-size:0.8rem; font-weight:bold; padding: 0.25rem 0.5rem;">
            ${margin}%
          </span>
        </td>
        <td style="text-align: right;">
          <input type="number" id="newPrice_${prod.ProductoID}" class="form-control val-precio-nuevo" 
                 style="width: 110px; font-weight: bold; text-align: right; padding: 0.25rem; font-size: 0.9rem;" 
                 value="${prod.PrecioVentaActual}" 
                 oninput="recalcularMargenItem(${prod.ProductoID}, ${prod.CostoCompra})">
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
}

function recalcularMargenItem(pid, costo) {
  const input = document.getElementById(`newPrice_${pid}`);
  const label = document.getElementById(`lblMargin_${pid}`);
  
  const pv = parseInt(input.value) || 0;
  const margin = pv > 0 ? Math.round(((pv - costo) / pv) * 100) : -100;
  
  label.textContent = margin + '%';
  const badgeColor = margin >= 20 ? '#10b981' : (margin >= 5 ? '#f59e0b' : '#ef4444');
  label.style.background = badgeColor;
}

async function actualizarPreciosVenta() {
  const preciosPayload = [];
  const inputs = document.querySelectorAll('.val-precio-nuevo');
  
  inputs.forEach(input => {
    const pid = parseInt(input.id.replace('newPrice_', ''));
    const pv = parseInt(input.value) || 0;
    
    preciosPayload.push({
      producto_id: pid,
      precio_venta: pv
    });
  });

  if (preciosPayload.length === 0) {
    saltarActualizacionPrecios();
    return;
  }

  try {
    const res = await fetch('api/actualizar_precios_compra.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        precios: preciosPayload
      })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    alert('Precios actualizados e ingreso de mercadería completado.');
    cerrarModalCompra();
    location.reload();
  } catch (err) {
    alert('Error al actualizar precios: ' + err.message);
  }
}

function saltarActualizacionPrecios() {
  alert('Recepción de mercadería ingresada con éxito. Se mantuvieron los precios de venta actuales.');
  cerrarModalCompra();
  location.reload();
}
</script>
