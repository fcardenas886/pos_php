<?php if (!$turnoActivo): ?>
<div style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); z-index: 2000; display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; padding: 2rem;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); padding: 3.5rem 3rem; border-radius: 16px; box-shadow: var(--shadow-lg); max-width: 500px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
    <i class="fa-solid fa-cash-register" style="font-size: 4rem; color: var(--warning); margin-bottom: 1.5rem;"></i>
    <h2 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 1rem;">Caja Registradora Cerrada</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2rem; line-height: 1.5;">
      Debes iniciar o abrir tu turno de caja registradora antes de poder procesar ventas en el sistema.
    </p>
    <a href="caja.php" class="btn btn-success" style="padding: 0.85rem 2rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: bold; border-radius: 8px; text-decoration: none;">
      <i class="fa-solid fa-key"></i> Ir a Abrir Turno / Caja
    </a>
  </div>
</div>
<?php endif; ?>

<div class="pos-container">
  
  <!-- Columna Izquierda: Catálogo y Búsqueda -->
  <div class="pos-catalog">
    
    <div class="pos-search-bar" style="display: flex; gap: 0.75rem; align-items: center;">
      <div style="position: relative; flex: 1;">
        <i class="fa-solid fa-barcode" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.2rem;"></i>
        <input type="text" id="posSearch" class="form-control" placeholder="Escanear código de barras o buscar por nombre..." style="padding-left: 2.8rem; font-size: 1.1rem;" autofocus autocomplete="off">
      </div>
      <button type="button" id="btnCotizaciones" onclick="abrirModalCotizaciones()" class="btn btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.9rem;" title="Ventas Pausadas / Cotizaciones">
        <i class="fa-solid fa-clock-rotate-left"></i> Pendientes
      </button>
      <button type="button" id="btnMovimientoCaja" onclick="abrirModalMovimiento()" class="btn btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.9rem;" title="Registrar Ingreso o Retiro de Caja">
        <i class="fa-solid fa-cash-register"></i> Retiro / Ingreso
      </button>
    </div>

    <!-- Grilla de Productos -->
    <div id="productGrid" class="product-grid">
      <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3rem;">
        <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
        <p>Cargando productos...</p>
      </div>
    </div>

  </div>

  <!-- Columna Derecha: Carrito y Cobro -->
  <div class="pos-cart">
    
    <div class="cart-header">
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-cart-shopping" style="color: var(--primary);"></i>
        <h2 style="font-size: 1.1rem; font-weight: 600;">Carrito de Compra</h2>
      </div>
      <div style="display: flex; gap: 0.4rem;">
        <button type="button" onclick="guardarCotizacion()" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.8rem; color: #fbbf24;">
          <i class="fa-solid fa-pause"></i> Pausar
        </button>
        <button type="button" id="btnVaciar" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.8rem; color: var(--danger);">
          <i class="fa-solid fa-trash-can"></i> Vaciar
        </button>
      </div>
    </div>

    <!-- Lista de Items en Carrito -->
    <div id="cartItems" class="cart-items">
      <div style="text-align: center; color: var(--text-muted); margin: auto; padding: 2rem;">
        <i class="fa-solid fa-basket-shopping" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 0.5rem;"></i>
        <p>El carrito está vacío</p>
        <span style="font-size: 0.8rem;">Escanea o haz clic en un producto</span>
      </div>
    </div>

    <!-- Panel de Resumen y Cobro -->
    <div class="cart-footer">
      
      <!-- Selector de Cliente con indicador de puntos -->
      <div>
        <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">CLIENTE ASIGNADO</label>
        <select id="clienteSelect" class="form-control" style="padding: 0.4rem 0.6rem; font-size: 0.85rem;">
          <option value="" data-puntos="0">Cliente Genérico (Público General)</option>
          <?php foreach ($clientes as $cl): ?>
            <option value="<?= $cl['ClienteID'] ?>" data-puntos="<?= $cl['PuntosAcumulados'] ?>">
              <?= htmlspecialchars($cl['Nombre']) ?> (<?= number_format($cl['PuntosAcumulados'], 0, ',', '.') ?> pts)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">DOCUMENTO</label>
          <select id="tipoDocumento" class="form-control" style="padding: 0.4rem 0.6rem; font-size: 0.85rem;">
            <option value="Boleta">Boleta</option>
            <option value="Factura">Factura</option>
            <option value="Sin Documento">Sin Documento</option>
          </select>
        </div>
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">DESCUENTO ($)</label>
          <input type="number" id="descuentoGlobal" class="form-control" placeholder="0" style="padding: 0.4rem 0.6rem; font-size: 0.85rem;" oninput="renderCart()">
        </div>
      </div>

      <div class="summary-row total">
        <span>TOTAL A PAGAR:</span>
        <span id="cartTotal" style="color: var(--success);">$0</span>
      </div>

      <button type="button" onclick="abrirModalPago()" class="btn btn-success btn-block" style="padding: 0.9rem; font-size: 1.1rem;">
        <i class="fa-solid fa-credit-card"></i> COBRAR VENTA (F12)
      </button>

    </div>

  </div>

</div>

<!-- Modal Interactivo de Pago Avanzado (FormPagoPOS) -->
<div id="pagoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 20px; width: 560px; padding: 2rem; box-shadow: 0 25px 50px rgba(0,0,0,0.6);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
      <h2 style="font-size: 1.35rem; font-weight: 700;">Finalizar y Procesar Pago</h2>
      <button onclick="cerrarModalPago()" class="btn btn-secondary" style="padding: 0.3rem 0.6rem;">&times;</button>
    </div>

    <!-- Muestra Total a Pagar Grande -->
    <div style="background: rgba(16,185,129,0.1); border: 1px solid var(--success); padding: 1rem; border-radius: 12px; text-align: center; margin-bottom: 1.25rem;">
      <span style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">TOTAL A COBRAR</span>
      <div id="modalMontoTotal" style="font-size: 2.5rem; font-weight: 800; color: var(--success); font-family: monospace;">$0</div>
    </div>

    <!-- Botones Selección Método de Pago -->
    <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.5rem;">SELECCIONA FORMA DE PAGO</label>
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-bottom: 1.25rem;">
      <button type="button" class="btn btn-secondary btn-metodo active" data-metodo="Efectivo" onclick="setFormaPago('Efectivo', this)">
        <i class="fa-solid fa-money-bill-wave"></i> Efectivo
      </button>
      <button type="button" class="btn btn-secondary btn-metodo" data-metodo="Tarjeta Debito" onclick="setFormaPago('Tarjeta Debito', this)">
        <i class="fa-solid fa-credit-card"></i> Tarjeta
      </button>
      <button type="button" class="btn btn-secondary btn-metodo" data-metodo="Transferencia" onclick="setFormaPago('Transferencia', this)">
        <i class="fa-solid fa-building-columns"></i> Transferencia
      </button>
      <button type="button" class="btn btn-secondary btn-metodo" data-metodo="Credito" onclick="setFormaPago('Credito', this)">
        <i class="fa-solid fa-handshake"></i> Fiado / Crédito
      </button>
      <button type="button" class="btn btn-secondary btn-metodo" data-metodo="Puntos" onclick="setFormaPago('Puntos', this)">
        <i class="fa-solid fa-star"></i> Puntos
      </button>
      <button type="button" class="btn btn-secondary btn-metodo" data-metodo="Mixto" onclick="setFormaPago('Mixto', this)">
        <i class="fa-solid fa-layer-group"></i> Pago Mixto
      </button>
      <button type="button" class="btn btn-secondary btn-metodo" data-metodo="Vale" onclick="setFormaPago('Vale', this)">
        <i class="fa-solid fa-ticket"></i> Vale Devolución
      </button>
    </div>

    <!-- Panel Dinámico Efectivo / Botones Rápido -->
    <div id="panelEfectivoModal" style="margin-bottom: 1.25rem;">
      <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">BOTONES DE EFECTIVO RÁPIDO</label>
      <div style="display: flex; gap: 0.4rem; margin-bottom: 0.75rem;">
        <button type="button" onclick="setMontoQuick('exacto')" class="btn btn-secondary" style="flex:1; padding:0.5rem;">$ Exacto</button>
        <button type="button" onclick="setMontoQuick(2000)" class="btn btn-secondary" style="flex:1; padding:0.5rem;">$2.000</button>
        <button type="button" onclick="setMontoQuick(5000)" class="btn btn-secondary" style="flex:1; padding:0.5rem;">$5.000</button>
        <button type="button" onclick="setMontoQuick(10000)" class="btn btn-secondary" style="flex:1; padding:0.5rem;">$10.000</button>
        <button type="button" onclick="setMontoQuick(20000)" class="btn btn-secondary" style="flex:1; padding:0.5rem;">$20.000</button>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MONTO RECIBIDO ($)</label>
          <input type="number" id="montoRecibidoModal" class="form-control" placeholder="0" style="font-size: 1.2rem; font-weight: bold;" oninput="calcularVueltoModal()">
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">VUELTO A ENTREGAR ($)</label>
          <input type="text" id="vueltoModal" class="form-control" value="$0" readonly style="font-size: 1.2rem; font-weight: bold; color: var(--success); background: rgba(0,0,0,0.3);">
        </div>
      </div>
    </div>

    <!-- Panel Dinámico Pago Mixto -->
    <div id="panelMixtoModal" style="display: none; background: rgba(15,23,42,0.6); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-dark); margin-bottom: 1.25rem;">
      <label style="font-size: 0.8rem; color: #818cf8; font-weight: 700; display: block; margin-bottom: 0.5rem;">DIVIDIR PAGO EN Varios MÉTODOS</label>
      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted);">EFECTIVO ($)</label>
          <input type="number" id="mixtoEfectivo" class="form-control" placeholder="0">
        </div>
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted);">TARJETA ($)</label>
          <input type="number" id="mixtoTarjeta" class="form-control" placeholder="0">
        </div>
        <div>
          <label style="font-size: 0.75rem; color: var(--text-muted);">TRANSFERENCIA ($)</label>
          <input type="number" id="mixtoTransf" class="form-control" placeholder="0">
        </div>
      </div>
    </div>

    <!-- Panel Dinámico Vale de Devolución -->
    <div id="panelValeModal" style="display: none; background: rgba(15,23,42,0.6); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-dark); margin-bottom: 1.25rem;">
      <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 0.4rem;">CÓDIGO DEL VALE</label>
      <input type="text" id="valeCodigoModal" class="form-control" placeholder="Ej: VALE-A1B2C3D4" style="text-transform: uppercase; font-weight: bold;">
    </div>

    <button type="button" id="btnConfirmarPagoModal" onclick="confirmarPagoModal()" class="btn btn-success btn-block" style="padding: 1rem; font-size: 1.2rem;">
      <i class="fa-solid fa-check-double"></i> CONFIRMAR E IMPRIMIR VENTA
    </button>
  </div>
</div>

<!-- Modal Cotizaciones / Pendientes -->
<div id="cotizacionesModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 500px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h2 style="font-size: 1.2rem; font-weight: 700;">Ventas Pausadas / Cotizaciones</h2>
      <button onclick="cerrarModalCotizaciones()" class="btn btn-secondary" style="padding: 0.3rem 0.6rem;">&times;</button>
    </div>
    <div id="cotizacionesLista" style="max-height: 350px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem;">
      <p style="text-align: center; color: var(--text-muted);">Cargando pendientes...</p>
    </div>
  </div>
</div>

<!-- Modal Movimiento de Caja (Ingreso / Retiro) -->
<div id="movimientoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; width: 400px; padding: 1.75rem; box-shadow: var(--shadow-lg);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
      <h2 style="font-size: 1.2rem; font-weight: 700;">Ingreso / Retiro de Caja</h2>
      <button onclick="cerrarModalMovimiento()" class="btn btn-secondary" style="padding: 0.3rem 0.6rem;">&times;</button>
    </div>
    <div style="display: flex; flex-direction: column; gap: 1rem;">
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">TIPO</label>
        <select id="posMovTipo" class="form-control">
          <option value="RETIRO">Retiro (-)</option>
          <option value="INGRESO">Ingreso (+)</option>
        </select>
      </div>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">MONTO ($)</label>
        <input type="number" id="posMovMonto" class="form-control" placeholder="10000" style="font-size: 1.1rem; font-weight: bold;">
      </div>
      <div>
        <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">CONCEPTO / MOTIVO</label>
        <input type="text" id="posMovConcepto" class="form-control" placeholder="Ej: Pago de panadería / Retiro parcial">
      </div>
      <button type="button" id="btnRegistrarMovimientoPos" onclick="registrarMovimientoPos()" class="btn btn-primary btn-block" style="padding: 0.75rem;">
        <i class="fa-solid fa-floppy-disk"></i> Registrar
      </button>
    </div>
  </div>
</div>

<!-- Modal de Ticket / Comprobante -->
<div id="ticketModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #fff; color: #000; width: 340px; border-radius: 12px; padding: 1.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.5); font-family: monospace;">
    <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
      <h2 style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.2rem;">MINIMARKET</h2>
      <p style="font-size: 0.8rem;">Comprobante de Venta</p>
      <p id="ticketFecha" style="font-size: 0.75rem; color: #555;"></p>
    </div>

    <div id="ticketDetalle" style="font-size: 0.85rem; margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.3rem;">
    </div>

    <div style="border-top: 1px dashed #000; padding-top: 0.5rem; font-size: 0.95rem; font-weight: bold; display: flex; justify-content: space-between;">
      <span>TOTAL:</span>
      <span id="ticketTotal"></span>
    </div>

    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-top: 0.25rem;">
      <span>PAGADO:</span>
      <span id="ticketPagado"></span>
    </div>
    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-top: 0.1rem;">
      <span>VUELTO:</span>
      <span id="ticketVuelto"></span>
    </div>

    <div style="margin-top: 1.25rem; display: flex; gap: 0.5rem;">
      <button onclick="window.print()" class="btn btn-primary btn-block" style="font-size: 0.85rem; padding: 0.5rem;">
        <i class="fa-solid fa-print"></i> Imprimir
      </button>
      <button onclick="cerrarTicket()" class="btn btn-secondary btn-block" style="font-size: 0.85rem; padding: 0.5rem; background: #eee; color: #000;">
        Cerrar
      </button>
    </div>
  </div>
</div>

<script src="assets/js/pos.js"></script>
