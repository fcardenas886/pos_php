<div style="max-width: 800px; margin: 2rem auto; text-align: center;">
  <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; color: #818cf8;">
    <i class="fa-solid fa-magnifying-glass-dollar"></i> Verificador y Consulta de Precios
  </h1>
  <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 2rem;">
    Escanea un código de barras o ingresa el nombre para verificar precio, stock y ofertas al instante.
  </p>

  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 20px; padding: 2rem; box-shadow: var(--shadow-lg);">
    <div style="position: relative; margin-bottom: 2rem;">
      <i class="fa-solid fa-barcode" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); font-size: 1.5rem; color: var(--primary);"></i>
      <input type="text" id="precioSearchInput" class="form-control" placeholder="Escanear producto..." style="padding-left: 3.5rem; font-size: 1.35rem; height: 60px; border-radius: 12px;" autofocus autocomplete="off">
    </div>

    <!-- Card de Resultado Grande -->
    <div id="resultadoPrecioCard" style="display: none; background: rgba(15,23,42,0.8); border: 2px solid var(--primary); border-radius: 16px; padding: 2rem;">
      <div id="resNombre" style="font-size: 1.75rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;"></div>
      <div id="resCodigo" style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem;"></div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; background: rgba(0,0,0,0.3); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem;">
        <div>
          <span style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">PRECIO DE VENTA</span>
          <div id="resPrecio" style="font-size: 2.5rem; font-weight: 800; color: var(--success); font-family: monospace;"></div>
        </div>
        <div>
          <span style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">STOCK DISPONIBLE</span>
          <div id="resStock" style="font-size: 2.5rem; font-weight: 800; color: #fff; font-family: monospace;"></div>
        </div>
      </div>

      <div id="resBadgeStock"></div>
      
      <!-- Contenedor de Promociones -->
      <div id="resPromoContainer" style="margin-top: 1rem;"></div>
    </div>

    <div id="noResultado" style="display: none; color: var(--text-muted); padding: 2rem;">
      <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.4;"></i>
      <p style="font-size: 1.1rem;">Escanea o busca un producto arriba</p>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('precioSearchInput');

  input.addEventListener('keydown', async (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const code = input.value.trim();
      if (!code) return;

      const res = await fetch(`api/buscar_producto.php?q=${encodeURIComponent(code)}`);
      const data = await res.json();

      if (data.success && data.productos.length > 0) {
        const p = data.productos[0];
        document.getElementById('resNombre').textContent = p.Nombre;
        document.getElementById('resCodigo').textContent = p.CodigoBarras ? 'CÓDIGO: ' + p.CodigoBarras : 'SIN CÓDIGO BARRAS';
        document.getElementById('resPrecio').textContent = `$${new Intl.NumberFormat('es-CL').format(p.PrecioVenta)}`;
        document.getElementById('resStock').textContent = `${p.Stock} ${p.UnidadMedida}`;
        
        const badge = document.getElementById('resBadgeStock');
        if (parseFloat(p.Stock) <= 0) {
          badge.innerHTML = '<span class="badge badge-danger" style="font-size: 1rem; padding: 0.4rem 1rem;">SIN STOCK</span>';
        } else if (parseFloat(p.Stock) <= parseFloat(p.StockMinimo)) {
          badge.innerHTML = '<span class="badge badge-warning" style="font-size: 1rem; padding: 0.4rem 1rem;">STOCK CRÍTICO</span>';
        } else {
          badge.innerHTML = '<span class="badge badge-success" style="font-size: 1rem; padding: 0.4rem 1rem;">DISPONIBLE</span>';
        }

        const promoContainer = document.getElementById('resPromoContainer');
        if (p.PromoTipo) {
          if (p.PromoTipo === 'DESCUENTO_UNIT') {
            promoContainer.innerHTML = `
              <div style="background: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; color: #34d399; font-size: 1.15rem; padding: 0.75rem 1.25rem; border-radius: 12px; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: bold; margin-top: 0.5rem;">
                <i class="fa-solid fa-tags"></i> OFERTA ACTIVA: -${p.PromoDescPorc}% de Descuento
              </div>
            `;
          } else if (p.PromoTipo === 'MULTIBUY') {
            const precioPackFmt = '$' + new Intl.NumberFormat('es-CL').format(p.PromoPrecioOf);
            promoContainer.innerHTML = `
              <div style="background: rgba(245, 158, 11, 0.15); border: 2px solid #f59e0b; color: #fbbf24; font-size: 1.15rem; padding: 0.75rem 1.25rem; border-radius: 12px; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: bold; margin-top: 0.5rem;">
                <i class="fa-solid fa-layer-group"></i> PROMOCIÓN: Lleva ${p.PromoCantMin} por ${precioPackFmt}
              </div>
            `;
          }
        } else {
          promoContainer.innerHTML = '';
        }

        document.getElementById('resultadoPrecioCard').style.display = 'block';
        document.getElementById('noResultado').style.display = 'none';
        input.value = '';
      } else {
        alert('Producto no encontrado');
      }
    }
  });
});
</script>
