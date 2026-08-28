// Lógica del Punto de Venta (POS) en JavaScript Vanilla con Modal Avanzado FormPagoPOS
let cart = [];
let productosCache = [];
let metodoSeleccionadoModal = 'Efectivo';
let valeAplicado = null; // { codigo, disponible }

function playBeep() {
  try {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(800, audioCtx.currentTime);
    gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    osc.start();
    osc.stop(audioCtx.currentTime + 0.08);
  } catch (e) {}
}

document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('posSearch');
  const btnVaciar = document.getElementById('btnVaciar');

  cargarProductos('');

  let debounceTimer;
  searchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      cargarProductos(e.target.value.trim());
    }, 250);
  });

  searchInput.addEventListener('keydown', async (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const code = searchInput.value.trim();
      if (!code) return;

      const res = await fetch(`api/buscar_producto.php?q=${encodeURIComponent(code)}`);
      const data = await res.json();

      if (data.success && data.productos.length > 0) {
        const match = data.productos.find(p => p.CodigoBarras === code) || data.productos[0];
        agregarAlCarrito(match);
        searchInput.value = '';
        cargarProductos('');
      } else {
        alert('Producto no encontrado');
      }
    }
  });

  btnVaciar.addEventListener('click', () => {
    if (cart.length > 0 && confirm('¿Deseas vaciar el carrito?')) {
      cart = [];
      renderCart();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'F12') {
      e.preventDefault();
      abrirModalPago();
    }
  });
});

async function cargarProductos(query) {
  const grid = document.getElementById('productGrid');
  try {
    const res = await fetch(`api/buscar_producto.php?q=${encodeURIComponent(query)}`);
    const data = await res.json();

    if (!data.success) throw new Error(data.error);

    productosCache = data.productos;

    if (productosCache.length === 0) {
      grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 2rem;">No se encontraron productos.</div>`;
      return;
    }

    grid.innerHTML = productosCache.map(p => `
      <div class="product-card" onclick="agregarAlCarritoId(${p.ProductoID})">
        <div class="product-name">${escapeHtml(p.Nombre)}</div>
        <div style="font-size: 0.75rem; color: var(--text-muted);">${p.CodigoBarras ? '#' + p.CodigoBarras : 'Sin código'}</div>
        <div class="product-meta">
          <div class="product-price">$${formatNumber(p.PrecioVenta)}</div>
          <div class="product-stock">Stock: ${p.Stock}</div>
        </div>
      </div>
    `).join('');

  } catch (err) {
    grid.innerHTML = `<div style="grid-column: 1/-1; color: var(--danger); text-align: center; padding: 2rem;">Error al cargar productos: ${err.message}</div>`;
  }
}

function agregarAlCarritoId(id) {
  const p = productosCache.find(prod => prod.ProductoID == id);
  if (p) agregarAlCarrito(p);
}

function agregarAlCarrito(producto) {
  const existIndex = cart.findIndex(item => item.ProductoID == producto.ProductoID);
  
  if (existIndex > -1) {
    if (cart[existIndex].cantidad + 1 > producto.Stock) {
      alert(`No hay suficiente stock. Disponible: ${producto.Stock}`);
      return;
    }
    cart[existIndex].cantidad += 1;
  } else {
    if (producto.Stock < 1) {
      alert(`Sin stock disponible.`);
      return;
    }
    cart.push({
      ProductoID: producto.ProductoID,
      Nombre: producto.Nombre,
      PrecioVenta: parseInt(producto.PrecioVenta),
      Stock: parseFloat(producto.Stock),
      cantidad: 1,
      PromocionID: producto.PromocionID || null,
      PromoTipo: producto.PromoTipo || null,
      PromoCantMin: parseFloat(producto.PromoCantMin) || 0,
      PromoDescPorc: parseFloat(producto.PromoDescPorc) || 0,
      PromoPrecioOf: parseInt(producto.PromoPrecioOf) || 0
    });
  }

  playBeep();
  renderCart();
}

function cambiarCantidad(index, delta) {
  const item = cart[index];
  if (!item) return;

  const nuevaCant = item.cantidad + delta;
  if (nuevaCant <= 0) {
    cart.splice(index, 1);
  } else {
    if (nuevaCant > item.Stock) {
      alert(`Stock máximo disponible: ${item.Stock}`);
      return;
    }
    item.cantidad = nuevaCant;
  }
  renderCart();
}

function calcularDescuentoItem(item) {
  if (!item.PromocionID || !item.PromoTipo) return 0;
  
  if (item.PromoTipo === 'DESCUENTO_UNIT') {
    const descUnit = Math.round(item.PrecioVenta * (item.PromoDescPorc / 100));
    return Math.round(item.cantidad * descUnit);
  } else if (item.PromoTipo === 'MULTIBUY') {
    const cantMin = item.PromoCantMin;
    const precioOf = item.PromoPrecioOf;
    
    if (item.cantidad >= cantMin) {
      const packs = Math.floor(item.cantidad / cantMin);
      const resto = item.cantidad % cantMin;
      const subtotalConPromo = (packs * precioOf) + (resto * item.PrecioVenta);
      const subtotalNormal = item.cantidad * item.PrecioVenta;
      return Math.max(0, subtotalNormal - subtotalConPromo);
    }
  }
  return 0;
}

function renderCart() {
  const container = document.getElementById('cartItems');
  const totalEl = document.getElementById('cartTotal');

  if (cart.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; color: var(--text-muted); margin: auto; padding: 2rem;">
        <i class="fa-solid fa-basket-shopping" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 0.5rem;"></i>
        <p>El carrito está vacío</p>
        <span style="font-size: 0.8rem;">Escanea o haz clic en un producto</span>
      </div>
    `;
    totalEl.textContent = '$0';
    return;
  }

  let totalFinal = getCartTotal();

  container.innerHTML = cart.map((item, idx) => {
    const subtotalNormal = item.cantidad * item.PrecioVenta;
    const desc = calcularDescuentoItem(item);
    const subtotalFinal = subtotalNormal - desc;

    let promoBadgeHtml = '';
    let subtotalHtml = `$${formatNumber(subtotalFinal)}`;

    if (desc > 0) {
      if (item.PromoTipo === 'DESCUENTO_UNIT') {
        promoBadgeHtml = `<span style="background: var(--success); color: #fff; font-size: 0.7rem; font-weight: bold; padding: 0.1rem 0.3rem; border-radius: 4px; margin-left: 0.5rem;">-${item.PromoDescPorc}% Dcto</span>`;
      } else if (item.PromoTipo === 'MULTIBUY') {
        promoBadgeHtml = `<span style="background: var(--warning); color: #000; font-size: 0.7rem; font-weight: bold; padding: 0.1rem 0.3rem; border-radius: 4px; margin-left: 0.5rem;">Promo Pack</span>`;
      }
      subtotalHtml = `<span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.8rem; margin-right: 0.4rem;">$${formatNumber(subtotalNormal)}</span> $${formatNumber(subtotalFinal)}`;
    }

    return `
      <div class="cart-item">
        <div style="flex: 1;">
          <div class="cart-item-title">${escapeHtml(item.Nombre)} ${promoBadgeHtml}</div>
          <div style="font-size: 0.8rem; color: var(--text-muted);">$${formatNumber(item.PrecioVenta)} c/u</div>
        </div>

        <div class="qty-controls">
          <button class="btn-qty" onclick="cambiarCantidad(${idx}, -1)">-</button>
          <span style="font-weight: 600; width: 24px; text-align: center;">${item.cantidad}</span>
          <button class="btn-qty" onclick="cambiarCantidad(${idx}, 1)">+</button>
        </div>

        <div style="font-weight: 700; color: var(--success); text-align: right;">
          ${subtotalHtml}
        </div>
      </div>
    `;
  }).join('');

  totalEl.textContent = `$${formatNumber(totalFinal)}`;

  // Actualizar el infoEl dinámicamente según el estado del carrito
  const infoEl = document.getElementById('valeAplicadoInfo');
  if (valeAplicado && infoEl) {
    const descGlobal = parseInt(document.getElementById('descuentoGlobal').value) || 0;
    const subtotalTrasDescuento = Math.max(0, getCartSubtotal() - descGlobal);
    if (valeAplicado.codigo.startsWith('TC-') && subtotalTrasDescuento < valeAplicado.disponible) {
      infoEl.style.display = 'block';
      infoEl.style.color = 'var(--danger)';
      infoEl.textContent = `Cambio inválido: el total de la compra ($${formatNumber(subtotalTrasDescuento)}) debe ser igual o mayor al Ticket de Cambio ($${formatNumber(valeAplicado.disponible)}).`;
    } else {
      infoEl.style.display = 'block';
      infoEl.style.color = 'var(--success)';
      infoEl.textContent = `Vale válido: se aplican $${formatNumber(getValeMontoAplicado())} de $${formatNumber(valeAplicado.disponible)} disponibles.`;
    }
  }
}

function getCartSubtotal() {
  return cart.reduce((sum, item) => sum + (item.cantidad * item.PrecioVenta) - calcularDescuentoItem(item), 0);
}

function getValeMontoAplicado() {
  if (!valeAplicado) return 0;
  const descGlobal = parseInt(document.getElementById('descuentoGlobal').value) || 0;
  const subtotalTrasDescuento = Math.max(0, getCartSubtotal() - descGlobal);
  
  // Si es un Ticket de Cambio (TC-), el subtotal debe ser igual o mayor al saldo del vale
  if (valeAplicado.codigo.startsWith('TC-') && subtotalTrasDescuento < valeAplicado.disponible) {
    return 0;
  }
  
  return Math.min(valeAplicado.disponible, subtotalTrasDescuento);
}

function getCartTotal() {
  const descGlobal = parseInt(document.getElementById('descuentoGlobal').value) || 0;
  const subtotal = Math.max(0, getCartSubtotal() - descGlobal);
  return Math.max(0, subtotal - getValeMontoAplicado());
}

async function aplicarValeCarrito() {
  const codigo = document.getElementById('valeCodigoInput').value.trim().toUpperCase();
  const infoEl = document.getElementById('valeAplicadoInfo');

  if (!codigo) {
    valeAplicado = null;
    infoEl.style.display = 'none';
    renderCart();
    return;
  }

  try {
    const res = await fetch(`api/verificar_vale.php?codigo=${encodeURIComponent(codigo)}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    // Auto-llenar el código del vale real por si se buscó por número de boleta
    valeAplicado = { codigo: data.codigo, disponible: data.disponible };
    document.getElementById('valeCodigoInput').value = data.codigo;
    
    // Validar restricción inmediata de Ticket de Cambio
    const descGlobal = parseInt(document.getElementById('descuentoGlobal').value) || 0;
    const subtotalTrasDescuento = Math.max(0, getCartSubtotal() - descGlobal);
    if (data.codigo.startsWith('TC-') && subtotalTrasDescuento < data.disponible) {
      valeAplicado = null;
      document.getElementById('valeCodigoInput').value = '';
      throw new Error(`Para cambios de mercadería, el total de la compra ($${formatNumber(subtotalTrasDescuento)}) debe ser igual o mayor al valor del Ticket de Cambio ($${formatNumber(data.disponible)}).`);
    }

    renderCart();
    infoEl.style.display = 'block';
    infoEl.style.color = 'var(--success)';
    infoEl.textContent = `Vale válido: se aplican $${formatNumber(getValeMontoAplicado())} de $${formatNumber(data.disponible)} disponibles.`;
  } catch (err) {
    valeAplicado = null;
    renderCart();
    infoEl.style.display = 'block';
    infoEl.style.color = 'var(--danger)';
    infoEl.textContent = err.message;
  }
}

/* Modal FormPagoPOS */
function abrirModalPago() {
  if (cart.length === 0) {
    alert('Agrega productos al carrito antes de cobrar.');
    return;
  }

  const total = getCartTotal();
  document.getElementById('modalMontoTotal').textContent = `$${formatNumber(total)}`;
  document.getElementById('montoRecibidoModal').value = total;
  calcularVueltoModal();

  const modal = document.getElementById('pagoModal');
  modal.style.display = 'flex';
}

function cerrarModalPago() {
  document.getElementById('pagoModal').style.display = 'none';
}

function setFormaPago(metodo, btn) {
  metodoSeleccionadoModal = metodo;

  document.querySelectorAll('.btn-metodo').forEach(b => b.classList.remove('active', 'btn-primary'));
  if (btn) btn.classList.add('active', 'btn-primary');

  const pnlEfec = document.getElementById('panelEfectivoModal');
  const pnlMix = document.getElementById('panelMixtoModal');
  const pnlVale = document.getElementById('panelValeModal');

  pnlEfec.style.display = metodo === 'Efectivo' ? 'block' : 'none';
  pnlMix.style.display = metodo === 'Mixto' ? 'block' : 'none';
  pnlVale.style.display = metodo === 'Vale' ? 'block' : 'none';
}

function setMontoQuick(val) {
  const total = getCartTotal();
  const input = document.getElementById('montoRecibidoModal');
  if (val === 'exacto') {
    input.value = total;
  } else {
    input.value = val;
  }
  calcularVueltoModal();
}

function calcularVueltoModal() {
  const total = getCartTotal();
  const recibido = parseInt(document.getElementById('montoRecibidoModal').value) || 0;
  const vueltoEl = document.getElementById('vueltoModal');

  if (recibido >= total) {
    vueltoEl.value = `$${formatNumber(recibido - total)}`;
  } else {
    vueltoEl.value = '$0';
  }
}

async function confirmarPagoModal() {
  // Validar restricciones de Ticket de Cambio antes de proceder
  if (valeAplicado && valeAplicado.codigo.startsWith('TC-')) {
    const descGlobal = parseInt(document.getElementById('descuentoGlobal').value) || 0;
    const subtotalTrasDescuento = Math.max(0, getCartSubtotal() - descGlobal);
    if (subtotalTrasDescuento < valeAplicado.disponible) {
      alert(`No se puede completar la venta. Para cambios de mercadería, el total de la compra ($${formatNumber(subtotalTrasDescuento)}) debe ser igual o mayor al valor del Ticket de Cambio ($${formatNumber(valeAplicado.disponible)}).`);
      return;
    }
  }

  const total = getCartTotal();
  const tipoDoc = document.getElementById('tipoDocumento').value;
  const clienteID = document.getElementById('clienteSelect').value;
  const descGlobal = parseInt(document.getElementById('descuentoGlobal').value) || 0;

  let pagos = [];
  let recibido = total;
  let vuelto = 0;

  if (metodoSeleccionadoModal === 'Efectivo') {
    recibido = parseInt(document.getElementById('montoRecibidoModal').value) || total;
    if (recibido < total) {
      alert(`El monto recibido ($${formatNumber(recibido)}) es menor al total ($${formatNumber(total)}).`);
      return;
    }
    vuelto = Math.max(0, recibido - total);
    pagos.push({ metodo: 'Efectivo', monto: total });
  } else if (metodoSeleccionadoModal === 'Mixto') {
    const efec = parseInt(document.getElementById('mixtoEfectivo').value) || 0;
    const tarj = parseInt(document.getElementById('mixtoTarjeta').value) || 0;
    const transf = parseInt(document.getElementById('mixtoTransf').value) || 0;
    const sumaMixto = efec + tarj + transf;

    if (sumaMixto < total) {
      alert(`La suma del pago mixto ($${formatNumber(sumaMixto)}) no cubre el total de la venta ($${formatNumber(total)}).`);
      return;
    }

    if (efec > 0) pagos.push({ metodo: 'Efectivo', monto: efec });
    if (tarj > 0) pagos.push({ metodo: 'Tarjeta Debito', monto: tarj });
    if (transf > 0) pagos.push({ metodo: 'Transferencia', monto: transf });
  } else if (metodoSeleccionadoModal === 'Credito' || metodoSeleccionadoModal === 'Puntos') {
    if (!clienteID) {
      alert('Debes seleccionar un Cliente para pagos a Crédito / Fiado o Puntos.');
      return;
    }
    const metodoDb = metodoSeleccionadoModal === 'Credito' ? 'Credito Interno' : 'Puntos';
    pagos.push({ metodo: metodoDb, monto: total });
  } else {
    pagos.push({ metodo: metodoSeleccionadoModal, monto: total });
  }

  const btn = document.getElementById('btnConfirmarPagoModal');
  btn.disabled = true;
  btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Registrando Venta...`;

  try {
    const res = await fetch('api/registrar_venta.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({
        items: cart.map(i => ({ producto_id: i.ProductoID, cantidad: i.cantidad })),
        tipo_documento: tipoDoc,
        cliente_id: clienteID,
        descuento_global: descGlobal,
        vale_codigo: valeAplicado ? valeAplicado.codigo : null,
        pagos: pagos,
        monto_pagado: recibido,
        vuelto: vuelto
      })
    });

    const data = await res.json();

    if (!data.success) throw new Error(data.error);

    cerrarModalPago();
    mostrarTicket(data, cart, total, recibido, vuelto);

    cart = [];
    document.getElementById('descuentoGlobal').value = '';
    valeAplicado = null;
    document.getElementById('valeCodigoInput').value = '';
    document.getElementById('valeAplicadoInfo').style.display = 'none';
    renderCart();
    cargarProductos('');

  } catch (err) {
    alert('Error al registrar venta: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<i class="fa-solid fa-check-double"></i> CONFIRMAR E IMPRIMIR VENTA`;
  }
}

async function guardarCotizacion() {
  if (cart.length === 0) {
    alert('El carrito está vacío.');
    return;
  }

  const clienteNombre = prompt('Ingrese el nombre del cliente o referencia para la venta pausada:', 'Cliente Cotización');
  if (!clienteNombre) return;

  try {
    const res = await fetch('api/cotizaciones.php?action=save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({
        cliente_nombre: clienteNombre,
        monto_total: getCartTotal(),
        items: cart
      })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    alert(`Venta pausada guardada con éxito (ID #${data.cotizacion_id}).`);
    cart = [];
    renderCart();
  } catch (err) {
    alert('Error al guardar cotización: ' + err.message);
  }
}

async function abrirModalCotizaciones() {
  const modal = document.getElementById('cotizacionesModal');
  const lista = document.getElementById('cotizacionesLista');
  modal.style.display = 'flex';
  lista.innerHTML = '<p style="text-align: center; color: var(--text-muted);">Cargando pendientes...</p>';

  try {
    const res = await fetch('api/cotizaciones.php?action=list');
    const data = await res.json();

    if (!data.success || data.cotizaciones.length === 0) {
      lista.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 1rem;">No hay ventas pausadas pendientes.</p>';
      return;
    }

    lista.innerHTML = data.cotizaciones.map(c => `
      <div style="background: rgba(15,23,42,0.6); border: 1px solid var(--border-dark); padding: 0.75rem 1rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <strong style="color: #fff;">#${c.CotizacionID} - ${escapeHtml(c.ClienteNombre)}</strong>
          <div style="font-size: 0.8rem; color: var(--text-muted);">$${formatNumber(c.MontoTotal)} • ${c.FechaCreacion}</div>
        </div>
        <button onclick="cargarCotizacion(${c.CotizacionID})" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.8rem;">
          <i class="fa-solid fa-arrow-rotate-left"></i> Restaurar
        </button>
      </div>
    `).join('');
  } catch (err) {
    lista.innerHTML = `<p style="color: var(--danger);">Error: ${err.message}</p>`;
  }
}

async function cargarCotizacion(id) {
  try {
    const res = await fetch(`api/cotizaciones.php?action=get&id=${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    cart = data.detalles.map(d => ({
      ProductoID: d.ProductoID,
      Nombre: d.Nombre,
      PrecioVenta: parseInt(d.PrecioUnitario),
      Stock: parseFloat(d.Stock),
      cantidad: parseFloat(d.Cantidad),
      PromocionID: d.PromocionID || null,
      PromoTipo: d.PromoTipo || null,
      PromoCantMin: parseFloat(d.PromoCantMin) || 0,
      PromoDescPorc: parseFloat(d.PromoDescPorc) || 0,
      PromoPrecioOf: parseInt(d.PromoPrecioOf) || 0
    }));

    renderCart();
    cerrarModalCotizaciones();
  } catch (err) {
    alert('Error al restaurar cotización: ' + err.message);
  }
}

function cerrarModalCotizaciones() {
  document.getElementById('cotizacionesModal').style.display = 'none';
}

function abrirModalMovimiento() {
  document.getElementById('posMovMonto').value = '';
  document.getElementById('posMovConcepto').value = '';
  document.getElementById('movimientoModal').style.display = 'flex';
}

function cerrarModalMovimiento() {
  document.getElementById('movimientoModal').style.display = 'none';
}

async function registrarMovimientoPos() {
  const tipo = document.getElementById('posMovTipo').value;
  const monto = parseInt(document.getElementById('posMovMonto').value) || 0;
  const concepto = document.getElementById('posMovConcepto').value.trim();

  if (monto <= 0) {
    alert('Ingresa un monto válido mayor a 0.');
    return;
  }

  const btn = document.getElementById('btnRegistrarMovimientoPos');
  btn.disabled = true;

  try {
    const res = await fetch('api/movimiento_caja.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN },
      body: JSON.stringify({ tipo, monto, concepto })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error);

    alert(data.mensaje);
    cerrarModalMovimiento();
  } catch (err) {
    alert('Error al registrar movimiento: ' + err.message);
  } finally {
    btn.disabled = false;
  }
}

function mostrarTicket(data, items, total, pagado, vuelto) {
  document.getElementById('ticketFecha').textContent = data.fecha;
  
  const detalleEl = document.getElementById('ticketDetalle');
  detalleEl.innerHTML = items.map(i => `
    <div style="display: flex; justify-content: space-between;">
      <span>${i.cantidad}x ${escapeHtml(i.Nombre).substring(0, 18)}</span>
      <span>$${formatNumber(i.cantidad * i.PrecioVenta)}</span>
    </div>
  `).join('');

  document.getElementById('ticketTotal').textContent = `$${formatNumber(total)}`;
  document.getElementById('ticketPagado').textContent = `$${formatNumber(pagado)}`;
  document.getElementById('ticketVuelto').textContent = `$${formatNumber(vuelto)}`;

  // Mostrar u ocultar sección DTE según la respuesta
  const dteInfo = document.getElementById('ticketDteInfo');
  const dteFolio = document.getElementById('ticketDteFolio');
  const dtePdfBtn = document.getElementById('ticketDtePdfBtn');
  const localPrintBtn = document.getElementById('ticketLocalPrintBtn');
  const dtePrintBtn = document.getElementById('ticketDtePrintBtn');

  if (dteInfo && dteFolio && dtePdfBtn) {
    if (data.dte && data.dte.success) {
      dteFolio.textContent = `Folio: ${data.dte.folio}`;
      dtePdfBtn.href = data.dte.pdf_url;
      dteInfo.style.display = 'block';

      if (localPrintBtn) localPrintBtn.style.display = 'none';
      if (dtePrintBtn) {
        dtePrintBtn.style.display = 'block';
        dtePrintBtn.dataset.url = data.dte.pdf_url;
      }

      // Impresión Directa / Automática
      setTimeout(() => {
        imprimirPdfDirecto(data.dte.pdf_url);
      }, 300);
    } else {
      dteInfo.style.display = 'none';
      if (localPrintBtn) localPrintBtn.style.display = 'block';
      if (dtePrintBtn) dtePrintBtn.style.display = 'none';
    }
  }

  const modal = document.getElementById('ticketModal');
  modal.style.display = 'flex';
}

function imprimirPdfDirecto(pdfUrl) {
  const oldIframe = document.getElementById('printIframe');
  if (oldIframe) {
    oldIframe.parentNode.removeChild(oldIframe);
  }

  const iframe = document.createElement('iframe');
  iframe.id = 'printIframe';
  iframe.style.position = 'fixed';
  iframe.style.right = '0';
  iframe.style.bottom = '0';
  iframe.style.width = '0';
  iframe.style.height = '0';
  iframe.style.border = '0';
  iframe.src = pdfUrl;

  iframe.onload = function() {
    try {
      iframe.contentWindow.focus();
      iframe.contentWindow.print();
    } catch (e) {
      console.error("Error al imprimir el PDF:", e);
      window.open(pdfUrl, '_blank');
    }
  };

  document.body.appendChild(iframe);
}

function cerrarTicket() {
  document.getElementById('ticketModal').style.display = 'none';
  document.getElementById('posSearch').focus();
}

function formatNumber(num) {
  return new Intl.NumberFormat('es-CL').format(num);
}

function escapeHtml(str) {
  return str.replace(/[&<>"']/g, function(m) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
  });
}
