<div class="report-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
  <div>
    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
      <i class="fa-solid fa-chart-line" style="color: var(--primary);"></i> Centro de Reportes y Finanzas
    </h1>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0 0 0;">
      Inteligencia de negocios, auditoría de ventas, rotación de mercadería y control de márgenes.
    </p>
  </div>

  <div class="report-actions" style="display: flex; gap: 0.6rem; align-items: center;">
    <!-- Botón Exportar a Excel -->
    <a href="reportes.php?tab=<?= urlencode($tab) ?>&inicio=<?= urlencode($fechaInicio) ?>&fin=<?= urlencode($fechaFin) ?><?= !empty($catId) ? '&categoria_id=' . (int)$catId : '' ?>&export=csv" 
       class="btn btn-secondary" style="padding: 0.6rem 1rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.45rem; border-color: rgba(34, 197, 94, 0.3); color: #22c55e;"
       title="Descargar este reporte formateado para Microsoft Excel">
      <i class="fa-solid fa-file-excel"></i> Exportar a Excel (CSV)
    </a>

    <!-- Botón Imprimir -->
    <button type="button" onclick="window.print()" class="btn btn-secondary" style="padding: 0.6rem 1rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.45rem;">
      <i class="fa-solid fa-print"></i> Imprimir
    </button>
  </div>
</div>

<!-- Barra de Pestañas Principales -->
<div class="report-tabs-bar" style="display: flex; gap: 0.4rem; overflow-x: auto; border-bottom: 1px solid var(--border-dark); margin-bottom: 1.5rem; padding-bottom: 0.5rem;">
  <a href="reportes.php?tab=ventas&inicio=<?= urlencode($fechaInicio) ?>&fin=<?= urlencode($fechaFin) ?>" 
     class="report-tab-btn <?= $tab === 'ventas' ? 'active' : '' ?>">
    <i class="fa-solid fa-cash-register"></i> Ventas y Medios
  </a>
  <a href="reportes.php?tab=productos&inicio=<?= urlencode($fechaInicio) ?>&fin=<?= urlencode($fechaFin) ?>" 
     class="report-tab-btn <?= $tab === 'productos' ? 'active' : '' ?>">
    <i class="fa-solid fa-cubes-stacked"></i> Ranking y Rotación
  </a>
  <a href="reportes.php?tab=inventario" 
     class="report-tab-btn <?= $tab === 'inventario' ? 'active' : '' ?>">
    <i class="fa-solid fa-warehouse"></i> Valorización Inventario
  </a>
  <a href="reportes.php?tab=creditos&inicio=<?= urlencode($fechaInicio) ?>&fin=<?= urlencode($fechaFin) ?>" 
     class="report-tab-btn <?= $tab === 'creditos' ? 'active' : '' ?>">
    <i class="fa-solid fa-handshake"></i> Cartera y Fiados
  </a>
  <a href="reportes.php?tab=compras&inicio=<?= urlencode($fechaInicio) ?>&fin=<?= urlencode($fechaFin) ?>" 
     class="report-tab-btn <?= $tab === 'compras' ? 'active' : '' ?>">
    <i class="fa-solid fa-truck-ramp-box"></i> Compras y Proveedores
  </a>
  <a href="reportes.php?tab=cajeros&inicio=<?= urlencode($fechaInicio) ?>&fin=<?= urlencode($fechaFin) ?>" 
     class="report-tab-btn <?= $tab === 'cajeros' ? 'active' : '' ?>">
    <i class="fa-solid fa-users-gear"></i> Rendimiento Cajeros
  </a>
  <a href="reportes.php?tab=utilidades&inicio=<?= urlencode($fechaInicio) ?>&fin=<?= urlencode($fechaFin) ?>" 
     class="report-tab-btn <?= $tab === 'utilidades' ? 'active' : '' ?>">
    <i class="fa-solid fa-sack-dollar"></i> Utilidades y Márgenes
  </a>
</div>

<!-- Filtros de Período (Visible en pestañas con rango de tiempo) -->
<?php if ($tab !== 'inventario'): ?>
<div class="report-filters-card" style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 14px; padding: 1rem 1.25rem; margin-bottom: 1.75rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
  
  <!-- Presets Rápidos -->
  <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
    <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-right: 0.4rem;">
      <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i> Período:
    </span>
    <a href="reportes.php?tab=<?= $tab ?>&preset=hoy" class="report-preset-chip <?= $preset === 'hoy' || ($fechaInicio === $hoy && $fechaFin === $hoy && empty($_GET['inicio'])) ? 'active' : '' ?>">Hoy</a>
    <a href="reportes.php?tab=<?= $tab ?>&preset=ayer" class="report-preset-chip <?= $preset === 'ayer' ? 'active' : '' ?>">Ayer</a>
    <a href="reportes.php?tab=<?= $tab ?>&preset=semana" class="report-preset-chip <?= $preset === 'semana' ? 'active' : '' ?>">Últimos 7 Días</a>
    <a href="reportes.php?tab=<?= $tab ?>&preset=mes" class="report-preset-chip <?= $preset === 'mes' || (empty($preset) && empty($_GET['inicio']) && $fechaInicio === date('Y-m-01')) ? 'active' : '' ?>">Este Mes</a>
    <a href="reportes.php?tab=<?= $tab ?>&preset=mes_anterior" class="report-preset-chip <?= $preset === 'mes_anterior' ? 'active' : '' ?>">Mes Anterior</a>
    <a href="reportes.php?tab=<?= $tab ?>&preset=ano" class="report-preset-chip <?= $preset === 'ano' ? 'active' : '' ?>">Año <?= date('Y') ?></a>
  </div>

  <!-- Formulario de Rango Libre -->
  <form method="GET" action="reportes.php" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
    <?php if (!empty($catId)): ?>
      <input type="hidden" name="categoria_id" value="<?= (int)$catId ?>">
    <?php endif; ?>

    <div style="display: flex; align-items: center; gap: 0.4rem;">
      <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Desde</label>
      <input type="date" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>" class="form-control" style="padding: 0.35rem 0.6rem; font-size: 0.85rem; width: auto;" required>
    </div>
    <div style="display: flex; align-items: center; gap: 0.4rem;">
      <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Hasta</label>
      <input type="date" name="fin" value="<?= htmlspecialchars($fechaFin) ?>" class="form-control" style="padding: 0.35rem 0.6rem; font-size: 0.85rem; width: auto;" required>
    </div>
    <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.85rem; font-size: 0.85rem;">
      <i class="fa-solid fa-filter"></i> Aplicar
    </button>
  </form>

</div>
<?php endif; ?>

<!-- ============================================================== -->
<!-- PESTAÑA 1: VENTAS Y MEDIOS DE PAGO -->
<!-- ============================================================== -->
<?php if ($tab === 'ventas'): ?>

  <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
    <a href="ventas.php?desde=<?= urlencode($fechaInicio) ?>&hasta=<?= urlencode($fechaFin) ?>" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem;">
      <i class="fa-solid fa-receipt" style="color: var(--primary);"></i> Ver Historial, Detalle y Reimpresión de Boletas
    </a>
  </div>

  <!-- Resumen Ejecutivo en Tarjetas -->
  <div class="grid-stats" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
      <div class="stat-info">
        <h3>Total Ventas Brutas</h3>
        <div class="stat-value" style="color: var(--success);"><?= formatCLP($kpiVentas['TotalVentas']) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Ingresos totales en el período</small>
      </div>
      <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
        <i class="fa-solid fa-money-bill-wave"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Venta Neta / IVA (19%)</h3>
        <div class="stat-value" style="font-size: 1.35rem;"><?= formatCLP($kpiVentas['TotalNeto']) ?></div>
        <small style="color: #fbbf24; font-size: 0.75rem;">IVA Fiscal: <?= formatCLP($kpiVentas['TotalIva']) ?></small>
      </div>
      <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--warning);">
        <i class="fa-solid fa-calculator"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Transacciones / Boletas</h3>
        <div class="stat-value"><?= number_format($kpiVentas['TotalTransacciones'], 0, ',', '.') ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Ventas completadas con éxito</small>
      </div>
      <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;">
        <i class="fa-solid fa-receipt"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Ticket Promedio</h3>
        <div class="stat-value" style="color: #38bdf8;"><?= formatCLP($ticketPromedio) ?></div>
        <small style="color: var(--danger); font-size: 0.75rem;">Dctos: -<?= formatCLP($kpiVentas['TotalDescuentos']) ?></small>
      </div>
      <div class="stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
        <i class="fa-solid fa-chart-pie"></i>
      </div>
    </div>
  </div>

  <!-- Cuadrícula de 2 Columnas: Medios de Pago y Horas Peak -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
    
    <!-- Tarjeta: Medios de Pago -->
    <div class="table-card">
      <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
          <i class="fa-solid fa-credit-card" style="color: var(--primary);"></i> Distribución por Medio de Pago
        </h2>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Total: <?= formatCLP($kpiVentas['TotalVentas']) ?></span>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>Medio de Pago</th>
            <th style="text-align: center;">Operaciones</th>
            <th style="text-align: right;">Total Recaudado</th>
            <th style="text-align: right;">Participación</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pagosMetodos)): ?>
            <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">Sin pagos registrados en este período.</td></tr>
          <?php else: ?>
            <?php foreach ($pagosMetodos as $p): ?>
              <?php 
                $pct = $kpiVentas['TotalVentas'] > 0 ? round(($p['TotalRecaudado'] / $kpiVentas['TotalVentas']) * 100, 1) : 0;
                $icon = 'fa-money-bill';
                $iconColor = '#10b981';
                if (stripos($p['MetodoPago'], 'debito') !== false) { $icon = 'fa-credit-card'; $iconColor = '#3b82f6'; }
                elseif (stripos($p['MetodoPago'], 'credito') !== false) { $icon = 'fa-credit-card'; $iconColor = '#8b5cf6'; }
                elseif (stripos($p['MetodoPago'], 'transfer') !== false) { $icon = 'fa-building-columns'; $iconColor = '#06b6d4'; }
                elseif (stripos($p['MetodoPago'], 'interno') !== false || stripos($p['MetodoPago'], 'fiado') !== false) { $icon = 'fa-handshake'; $iconColor = '#f59e0b'; }
                elseif (stripos($p['MetodoPago'], 'vale') !== false) { $icon = 'fa-ticket'; $iconColor = '#ec4899'; }
              ?>
              <tr>
                <td>
                  <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="fa-solid <?= $icon ?>" style="color: <?= $iconColor ?>; font-size: 1rem; width: 18px; text-align: center;"></i>
                    <strong style="color: var(--text-main);"><?= htmlspecialchars(str_replace('_', ' ', $p['MetodoPago'])) ?></strong>
                  </div>
                </td>
                <td style="text-align: center; font-weight: 600; color: var(--text-muted);">
                  <?= number_format($p['Transacciones'], 0, ',', '.') ?>
                </td>
                <td style="text-align: right; font-weight: 700; color: var(--text-main); font-family: monospace;">
                  <?= formatCLP($p['TotalRecaudado']) ?>
                </td>
                <td style="text-align: right;">
                  <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.4rem;">
                    <div style="width: 50px; background: rgba(255,255,255,0.08); height: 6px; border-radius: 3px; overflow: hidden;">
                      <div style="width: <?= min(100, $pct) ?>%; background: <?= $iconColor ?>; height: 100%;"></div>
                    </div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); min-width: 40px; text-align: right;"><?= $pct ?>%</span>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Tarjeta: Horas Peak y Comprobantes -->
    <div class="table-card">
      <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.45rem;">
          <i class="fa-solid fa-clock" style="color: #fbbf24;"></i> Horas Peak de Mayor Venta
        </h2>
        <span style="font-size: 0.75rem; color: var(--text-muted);">Distribución 24h</span>
      </div>

      <div style="padding: 1.25rem;">
        <?php if (empty($ventasPorHora)): ?>
          <p style="text-align: center; color: var(--text-muted); margin: 2rem 0;">No hay datos horarios para este período.</p>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <?php 
              // Mostrar las horas con ventas ordenadas por hora
              foreach ($ventasPorHora as $h => $vh): 
                $porcBar = $maxMontoHora > 0 ? round(($vh['MontoTotal'] / $maxMontoHora) * 100) : 0;
                $horaLabel = sprintf("%02d:00 - %02d:59", $h, $h);
            ?>
              <div style="display: grid; grid-template-columns: 110px 1fr 100px; align-items: center; gap: 0.75rem; font-size: 0.82rem;">
                <span style="color: var(--text-muted); font-family: monospace; font-weight: 600;"><?= $horaLabel ?></span>
                <div style="background: rgba(255,255,255,0.06); height: 18px; border-radius: 6px; overflow: hidden; position: relative;">
                  <div style="background: linear-gradient(90deg, var(--primary), #818cf8); width: <?= max(4, $porcBar) ?>%; height: 100%; border-radius: 6px;"></div>
                  <span style="position: absolute; left: 8px; top: 1px; font-size: 0.72rem; color: #fff; font-weight: 700;">
                    <?= $vh['TotalVentas'] ?> venta<?= $vh['TotalVentas'] > 1 ? 's' : '' ?>
                  </span>
                </div>
                <span style="text-align: right; font-weight: 700; color: var(--text-main); font-family: monospace;">
                  <?= formatCLP($vh['MontoTotal']) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Desglose por Comprobante -->
        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-dark);">
          <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.6rem;">
            Por Tipo de Comprobante:
          </div>
          <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <?php foreach ($docsDesglose as $doc): ?>
              <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-dark); border-radius: 8px; padding: 0.5rem 0.85rem; flex: 1; min-width: 110px;">
                <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;"><?= htmlspecialchars($doc['TipoDocumento']) ?></div>
                <div style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-top: 0.15rem;"><?= formatCLP($doc['TotalMonto']) ?></div>
                <small style="font-size: 0.72rem; color: var(--text-muted);"><?= $doc['Cantidad'] ?> emitidas</small>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Tabla: Detalle de Ventas en el Período -->
  <div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h2 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Últimas Ventas en el Período Seleccionado
      </h2>
      <span style="font-size: 0.8rem; color: var(--text-muted);">Mostrando hasta 100 registros</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>N° Venta</th>
          <th>Fecha y Hora</th>
          <th>Documento</th>
          <th>Cajero</th>
          <th>Cliente</th>
          <th style="text-align: right;">Total</th>
          <th style="text-align: center;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($ventasList)): ?>
          <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay ventas registradas en el período.</td></tr>
        <?php else: ?>
          <?php foreach ($ventasList as $v): ?>
            <tr>
              <td><strong style="color: #818cf8;">#<?= $v['VentaID'] ?></strong></td>
              <td style="color: var(--text-muted); font-size: 0.85rem;"><?= date('d/m/Y H:i', strtotime($v['FechaVenta'])) ?></td>
              <td><span class="badge badge-secondary"><?= htmlspecialchars($v['TipoDocumento']) ?></span></td>
              <td style="font-weight: 600;"><?= htmlspecialchars($v['Cajero']) ?></td>
              <td style="color: var(--text-muted);"><?= htmlspecialchars($v['Cliente'] ?: 'Cliente Ocasional') ?></td>
              <td style="text-align: right; font-weight: 700; color: var(--success); font-family: monospace; font-size: 0.95rem;">
                <?= formatCLP($v['MontoTotal']) ?>
              </td>
              <td style="text-align: center;">
                <a href="ventas.php?fecha=<?= substr($v['FechaVenta'], 0, 10) ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="Ver en módulo ventas">
                  <i class="fa-solid fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- ============================================================== -->
<!-- PESTAÑA 2: RANKING Y ROTACIÓN DE PRODUCTOS -->
<!-- ============================================================== -->
<?php if ($tab === 'productos'): ?>

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
      <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--text-main);">
        <i class="fa-solid fa-cubes-stacked" style="color: var(--primary);"></i> Desempeño y Rotación de Mercadería
      </h2>
      <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
        Conoce cuáles son tus artículos estrella y cuáles no se han movido para liberar capital.
      </p>
    </div>

    <!-- Filtro por Categoría -->
    <form method="GET" action="reportes.php" style="display: flex; align-items: center; gap: 0.5rem;">
      <input type="hidden" name="tab" value="productos">
      <input type="hidden" name="inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
      <input type="hidden" name="fin" value="<?= htmlspecialchars($fechaFin) ?>">
      <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Filtrar Categoría:</label>
      <select name="categoria_id" class="form-control" onchange="this.form.submit()" style="padding: 0.35rem 0.65rem; font-size: 0.85rem; width: auto;">
        <option value="0">Todas las Categorías</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['CategoriaID'] ?>" <?= $catId == $c['CategoriaID'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['Nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <!-- Tarjeta 1: Top 30 Productos Más Vendidos -->
  <div class="table-card" style="margin-bottom: 2rem;">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        🏆 Top Productos Más Vendidos (Líderes en Unidades y Recaudación)
      </h3>
      <span style="font-size: 0.8rem; color: var(--text-muted);"><?= count($rankingProductos) ?> productos listados</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 50px; text-align: center;">#</th>
          <th>Producto</th>
          <th>Categoría</th>
          <th style="text-align: center;">Unidades Vendidas</th>
          <th style="text-align: right;">Total Venta ($)</th>
          <th style="text-align: right;">Utilidad Aportada</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rankingProductos)): ?>
          <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay ventas registradas para este filtro y período.</td></tr>
        <?php else: ?>
          <?php $i = 1; foreach ($rankingProductos as $rp): ?>
            <?php 
              $rankBadge = 'background: rgba(255,255,255,0.06); color: var(--text-muted);';
              if ($i === 1) $rankBadge = 'background: #f59e0b; color: #000; font-weight: 800;';
              elseif ($i === 2) $rankBadge = 'background: #94a3b8; color: #000; font-weight: 800;';
              elseif ($i === 3) $rankBadge = 'background: #b45309; color: #fff; font-weight: 800;';
            ?>
            <tr>
              <td style="text-align: center;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; font-size: 0.8rem; <?= $rankBadge ?>">
                  <?= $i++ ?>
                </span>
              </td>
              <td>
                <div style="font-weight: 700; color: #fff;"><?= htmlspecialchars($rp['Nombre']) ?></div>
                <small style="color: var(--text-muted); font-family: monospace; font-size: 0.75rem;">
                  <?= $rp['CodigoBarras'] ? '#' . $rp['CodigoBarras'] : 'Sin código' ?>
                </small>
              </td>
              <td><?= htmlspecialchars($rp['Categoria'] ?: 'General') ?></td>
              <td style="text-align: center;">
                <strong style="color: #38bdf8; font-size: 0.95rem;"><?= number_format($rp['UnidadesVendidas'], 0, ',', '.') ?></strong>
                <span style="font-size: 0.75rem; color: var(--text-muted);">u</span>
              </td>
              <td style="text-align: right; font-weight: 700; font-family: monospace; color: var(--text-main);">
                <?= formatCLP($rp['MontoTotalVentas']) ?>
              </td>
              <td style="text-align: right; font-weight: 700; font-family: monospace; color: var(--success);">
                <?= formatCLP($rp['UtilidadTotal']) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Tarjeta 2: Productos Sin Rotación (Huesos / Capital Estancado) -->
  <div class="table-card" style="border-color: rgba(239, 68, 68, 0.3);">
    <div class="table-header" style="background: rgba(239, 68, 68, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
      <div>
        <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: #ef4444; display: flex; align-items: center; gap: 0.45rem;">
          <i class="fa-solid fa-triangle-exclamation"></i> Productos Sin Rotación (Stock Estancado / "Huesos")
        </h3>
        <p style="font-size: 0.78rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
          Productos con stock físico disponible pero que <strong>NO tuvieron ventas</strong> en el período seleccionado.
        </p>
      </div>
      <div style="text-align: right;">
        <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Capital Inmovilizado Estimado</span>
        <strong style="font-size: 1.1rem; color: #ef4444; font-family: monospace;"><?= formatCLP($totalCapitalEstancado) ?></strong>
      </div>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Categoría</th>
          <th style="text-align: center;">Stock Parado</th>
          <th style="text-align: right;">Costo Compra</th>
          <th style="text-align: right;">Precio Venta</th>
          <th style="text-align: right;">Capital Inmovilizado</th>
          <th style="text-align: center;">Sugerencia</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($productosSinRotacion)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--success); padding: 2rem;">
              <i class="fa-solid fa-circle-check" style="font-size: 1.5rem; display: block; margin-bottom: 0.4rem;"></i>
              ¡Excelente! Toda tu mercadería activa ha registrado ventas en este período.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($productosSinRotacion as $h): ?>
            <tr>
              <td>
                <strong style="color: var(--text-main);"><?= htmlspecialchars($h['Nombre']) ?></strong>
                <small style="display: block; color: var(--text-muted); font-family: monospace; font-size: 0.72rem;">
                  <?= $h['CodigoBarras'] ?: 'Sin código' ?>
                </small>
              </td>
              <td><?= htmlspecialchars($h['Categoria'] ?: 'General') ?></td>
              <td style="text-align: center;">
                <span class="badge badge-warning"><?= (float)$h['Stock'] ?> u</span>
              </td>
              <td style="text-align: right; color: var(--text-muted); font-family: monospace;"><?= formatCLP($h['CostoCompra']) ?></td>
              <td style="text-align: right; color: var(--text-main); font-family: monospace;"><?= formatCLP($h['PrecioVenta']) ?></td>
              <td style="text-align: right; font-weight: 700; color: #ef4444; font-family: monospace; font-size: 0.95rem;">
                <?= formatCLP($h['CapitalInmovilizado']) ?>
              </td>
              <td style="text-align: center;">
                <a href="promociones.php" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.72rem;" title="Crear oferta o pack con descuento">
                  <i class="fa-solid fa-tag"></i> Armar Promo
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- ============================================================== -->
<!-- PESTAÑA 3: VALORIZACIÓN DE INVENTARIO -->
<!-- ============================================================== -->
<?php if ($tab === 'inventario'): ?>

  <div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--text-main);">
      <i class="fa-solid fa-warehouse" style="color: var(--primary);"></i> Valorización del Inventario Físico en Bodega
    </h2>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
      Cálculo del capital total invertido a costo de adquisición y el retorno proyectado a precio de venta al público.
    </p>
  </div>

  <!-- KPIs Globales de Inventario -->
  <div class="grid-stats" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
      <div class="stat-info">
        <h3>Capital en Bodega (Costo)</h3>
        <div class="stat-value" style="color: #fbbf24;"><?= formatCLP($kpiInventario['ValorTotalCosto']) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Total invertido en mercadería</small>
      </div>
      <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--warning);">
        <i class="fa-solid fa-boxes-packing"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Valor Total a Venta</h3>
        <div class="stat-value" style="color: var(--success);"><?= formatCLP($kpiInventario['ValorTotalVenta']) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Retorno estimado al vender todo</small>
      </div>
      <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
        <i class="fa-solid fa-shop"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Margen Potencial Global</h3>
        <div class="stat-value" style="color: #818cf8;"><?= formatCLP($margenPotencialTotal) ?></div>
        <small style="color: #818cf8; font-size: 0.75rem;">Rentabilidad teórica: <strong><?= $margenPotencialPorc ?>%</strong></small>
      </div>
      <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;">
        <i class="fa-solid fa-percent"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Unidades Físicas en Stock</h3>
        <div class="stat-value"><?= number_format($kpiInventario['TotalUnidadesFisicas'], 0, ',', '.') ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;"><?= $kpiInventario['TotalProductos'] ?> artículos activos</small>
      </div>
      <div class="stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
        <i class="fa-solid fa-barcode"></i>
      </div>
    </div>
  </div>

  <!-- Alerta de Salud del Inventario -->
  <div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <i class="fa-solid fa-shield-heart" style="font-size: 1.5rem; color: var(--primary);"></i>
      <div>
        <strong style="color: var(--text-main); font-size: 0.95rem;">Estado de Disponibilidad de Catálogo</strong>
        <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Monitoreo preventivo de quiebres de stock</p>
      </div>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <span class="badge badge-success" style="padding: 0.4rem 0.75rem; font-size: 0.82rem;">
        <i class="fa-solid fa-check-circle"></i> <?= $kpiInventario['ProductosConStock'] ?> Con Stock OK
      </span>
      <span class="badge badge-warning" style="padding: 0.4rem 0.75rem; font-size: 0.82rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= $kpiInventario['ProductosBajoStock'] ?> Bajo Mínimo
      </span>
      <span class="badge badge-danger" style="padding: 0.4rem 0.75rem; font-size: 0.82rem;">
        <i class="fa-solid fa-circle-xmark"></i> <?= $kpiInventario['ProductosAgotados'] ?> Agotados
      </span>
    </div>
  </div>

  <!-- Tabla: Desglose y Valorización por Categoría -->
  <div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-folder-tree" style="color: var(--primary);"></i> Desglose y Capital por Categoría
      </h3>
      <span style="font-size: 0.8rem; color: var(--text-muted);"><?= count($categoriasInventario) ?> categorías</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Categoría</th>
          <th style="text-align: center;">N° Artículos</th>
          <th style="text-align: center;">Stock Físico</th>
          <th style="text-align: right;">Valor Costo ($)</th>
          <th style="text-align: right;">Valor Venta ($)</th>
          <th style="text-align: right;">Margen Estimado ($)</th>
          <th style="text-align: right;">Margen %</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categoriasInventario as $ci): ?>
          <?php 
            $margenCat = $ci['ValorVenta'] - $ci['ValorCosto'];
            $pctCat = $ci['ValorVenta'] > 0 ? round(($margenCat / $ci['ValorVenta']) * 100, 1) : 0;
          ?>
          <tr>
            <td><strong style="color: var(--text-main);"><?= htmlspecialchars($ci['Categoria']) ?></strong></td>
            <td style="text-align: center;"><?= $ci['TotalItems'] ?></td>
            <td style="text-align: center; font-weight: 600;"><?= number_format($ci['StockTotal'], 0, ',', '.') ?> u</td>
            <td style="text-align: right; color: #fbbf24; font-family: monospace; font-weight: 600;"><?= formatCLP($ci['ValorCosto']) ?></td>
            <td style="text-align: right; color: var(--text-main); font-family: monospace; font-weight: 600;"><?= formatCLP($ci['ValorVenta']) ?></td>
            <td style="text-align: right; color: var(--success); font-family: monospace; font-weight: 700;"><?= formatCLP($margenCat) ?></td>
            <td style="text-align: right;"><span class="badge badge-success"><?= $pctCat ?>%</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- ============================================================== -->
<!-- PESTAÑA 4: RENDIMIENTO DE CAJEROS Y CUADRATURAS -->
<!-- ============================================================== -->
<?php if ($tab === 'cajeros'): ?>

  <div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--text-main);">
      <i class="fa-solid fa-users-gear" style="color: var(--primary);"></i> Rendimiento de Personal y Cuadraturas de Turno
    </h2>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
      Auditoría de ventas por operador y control de diferencias en arqueos de caja (sobrantes y faltantes).
    </p>
  </div>

  <!-- Resumen de Cuadratura en Tarjetas -->
  <div class="grid-stats" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
      <div class="stat-info">
        <h3>Cajeros Activos en Turno</h3>
        <div class="stat-value"><?= count($cajerosRendimiento) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Personal con movimientos en el período</small>
      </div>
      <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;">
        <i class="fa-solid fa-user-check"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Total Faltantes de Caja</h3>
        <div class="stat-value" style="color: var(--danger);">-<?= formatCLP($totalFaltantes) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Descuadres negativos en cierres</small>
      </div>
      <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: var(--danger);">
        <i class="fa-solid fa-circle-minus"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Total Sobrantes de Caja</h3>
        <div class="stat-value" style="color: var(--success);">+<?= formatCLP($totalSobrantes) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Excedentes declarados en cierres</small>
      </div>
      <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
        <i class="fa-solid fa-circle-plus"></i>
      </div>
    </div>
  </div>

  <!-- Tabla 1: Desempeño por Cajero -->
  <div class="table-card" style="margin-bottom: 2rem;">
    <div class="table-header">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-user-tie" style="color: var(--primary);"></i> Resumen de Ventas por Cajero
      </h3>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Cajero / Usuario</th>
          <th>Rol</th>
          <th style="text-align: center;">Turnos Atendidos</th>
          <th style="text-align: center;">Ventas Realizadas</th>
          <th style="text-align: right;">Total Recaudado</th>
          <th style="text-align: right;">Ticket Promedio</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($cajerosRendimiento)): ?>
          <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay actividad de cajeros en el período.</td></tr>
        <?php else: ?>
          <?php foreach ($cajerosRendimiento as $c): ?>
            <?php 
              $avgTicketCajero = $c['TotalVentas'] > 0 ? round($c['MontoTotalVentas'] / $c['TotalVentas']) : 0;
            ?>
            <tr>
              <td>
                <strong style="color: #fff;"><?= htmlspecialchars($c['Cajero']) ?></strong>
                <small style="display: block; color: var(--text-muted);">@<?= htmlspecialchars($c['Usuario']) ?></small>
              </td>
              <td><span class="badge badge-secondary"><?= htmlspecialchars($c['Rol']) ?></span></td>
              <td style="text-align: center; font-weight: 600;"><?= $c['TurnosRealizados'] ?></td>
              <td style="text-align: center; font-weight: 600;"><?= $c['TotalVentas'] ?></td>
              <td style="text-align: right; font-weight: 700; color: var(--success); font-family: monospace; font-size: 0.95rem;">
                <?= formatCLP($c['MontoTotalVentas']) ?>
              </td>
              <td style="text-align: right; font-family: monospace; color: var(--text-muted);"><?= formatCLP($avgTicketCajero) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Tabla 2: Historial de Cuadraturas de Turnos -->
  <div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-scale-balanced" style="color: var(--primary);"></i> Historial de Arqueos y Cuadraturas de Turno
      </h3>
      <span style="font-size: 0.8rem; color: var(--text-muted);">Últimos 50 turnos cerrados</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>N° Turno</th>
          <th>Cajero</th>
          <th>Apertura</th>
          <th>Cierre</th>
          <th style="text-align: right;">Esperado Sistema</th>
          <th style="text-align: right;">Declarado Físico</th>
          <th style="text-align: right;">Diferencia</th>
          <th style="text-align: center;">Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($cuadraturasTurnos)): ?>
          <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay turnos cerrados registrados en este período.</td></tr>
        <?php else: ?>
          <?php foreach ($cuadraturasTurnos as $ct): ?>
            <?php 
              $dif = (int)$ct['Diferencia'];
              $difColor = 'var(--text-muted)';
              $difBadge = '<span class="badge badge-success">Cuadrado</span>';
              if ($dif < 0) {
                $difColor = 'var(--danger)';
                $difBadge = '<span class="badge badge-danger">Faltante</span>';
              } elseif ($dif > 0) {
                $difColor = 'var(--warning)';
                $difBadge = '<span class="badge badge-warning">Sobrante</span>';
              }
            ?>
            <tr>
              <td><strong style="color: #818cf8;">#<?= $ct['TurnoID'] ?></strong></td>
              <td><?= htmlspecialchars($ct['Cajero']) ?></td>
              <td style="font-size: 0.8rem; color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($ct['FechaApertura'])) ?></td>
              <td style="font-size: 0.8rem; color: var(--text-muted);"><?= $ct['FechaCierre'] ? date('d/m/Y H:i', strtotime($ct['FechaCierre'])) : '—' ?></td>
              <td style="text-align: right; font-family: monospace;"><?= formatCLP($ct['MontoCierreSistema']) ?></td>
              <td style="text-align: right; font-family: monospace; font-weight: 600; color: var(--text-main);"><?= formatCLP($ct['TotalDeclarado']) ?></td>
              <td style="text-align: right; font-family: monospace; font-weight: 700; color: <?= $difColor ?>;">
                <?= ($dif > 0 ? '+' : '') . formatCLP($dif) ?>
              </td>
              <td style="text-align: center;"><?= $difBadge ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- ============================================================== -->
<!-- PESTAÑA 5: UTILIDADES Y MÁRGENES (INTEGRADA) -->
<!-- ============================================================== -->
<?php if ($tab === 'utilidades'): ?>

  <div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--text-main);">
      <i class="fa-solid fa-sack-dollar" style="color: var(--primary);"></i> Márgenes de Ganancia Real y Rentabilidad
    </h2>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
      Cálculo de ganancia neta comparando el Precio de Venta facturado vs. Costo de Compra de cada artículo.
    </p>
  </div>

  <div class="grid-stats" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
      <div class="stat-info">
        <h3>Total Ingresos Venta</h3>
        <div class="stat-value"><?= formatCLP($totalVentasUtil) ?></div>
      </div>
      <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);"><i class="fa-solid fa-chart-line"></i></div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <h3>Total Costo Mercadería</h3>
        <div class="stat-value" style="color: var(--warning);"><?= formatCLP($totalCostoUtil) ?></div>
      </div>
      <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--warning);"><i class="fa-solid fa-boxes-stacked"></i></div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <h3>Utilidad Neta Estimada</h3>
        <div class="stat-value" style="color: #818cf8;"><?= formatCLP($totalUtilidadMonto) ?></div>
      </div>
      <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;"><i class="fa-solid fa-sack-dollar"></i></div>
    </div>
  </div>

  <div class="table-card">
    <div class="table-header">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">Desglose de Margen por Producto</h3>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Categoría</th>
          <th style="text-align: center;">Unidades</th>
          <th style="text-align: right;">Venta Total</th>
          <th style="text-align: right;">Costo Total</th>
          <th style="text-align: right;">Ganancia Neta</th>
          <th style="text-align: right;">Margen %</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reporteUtilidades)): ?>
          <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay ventas registradas en el período seleccionado.</td></tr>
        <?php else: ?>
          <?php foreach ($reporteUtilidades as $row): ?>
            <?php 
              $margen = $row['TotalVentas'] > 0 ? round(($row['UtilidadEstimada'] / $row['TotalVentas']) * 100, 1) : 0;
            ?>
            <tr>
              <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['Producto']) ?></td>
              <td><?= htmlspecialchars($row['Categoria'] ?: 'General') ?></td>
              <td style="text-align: center;"><strong><?= $row['CantidadVendida'] ?></strong> u</td>
              <td style="text-align: right; font-family: monospace;"><?= formatCLP($row['TotalVentas']) ?></td>
              <td style="text-align: right; color: var(--text-muted); font-family: monospace;"><?= formatCLP($row['TotalCosto']) ?></td>
              <td style="text-align: right; font-weight: 700; color: var(--success); font-family: monospace;"><?= formatCLP($row['UtilidadEstimada']) ?></td>
              <td style="text-align: right;"><span class="badge badge-success"><?= $margen ?>%</span></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- ============================================================== -->
<!-- PESTAÑA 6: CARTERA DE CLIENTES Y FIADOS (CUENTAS POR COBRAR) -->
<!-- ============================================================== -->
<?php if ($tab === 'creditos'): ?>

  <div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--text-main);">
      <i class="fa-solid fa-handshake" style="color: var(--primary);"></i> Cartera de Clientes, Cuentas por Cobrar y Fiados
    </h2>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
      Auditoría de crédito interno, saldos pendientes de pago, control de cupos y flujo de cobranza.
    </p>
  </div>

  <!-- KPIs de Cartera y Cobranza -->
  <div class="grid-stats" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
      <div class="stat-info">
        <h3>Total Deuda por Cobrar</h3>
        <div class="stat-value" style="color: var(--danger);"><?= formatCLP($kpiCreditos['TotalDeudaGlobal']) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Capital de la tienda en la calle</small>
      </div>
      <div class="stat-icon" style="background: rgba(239, 68, 68, 0.15); color: var(--danger);">
        <i class="fa-solid fa-hand-holding-dollar"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Clientes Deudores</h3>
        <div class="stat-value" style="color: #fbbf24;"><?= number_format($kpiCreditos['ClientesDeudoresCount'], 0, ',', '.') ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Cupo Total: <?= formatCLP($kpiCreditos['TotalCupoGlobal']) ?></small>
      </div>
      <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--warning);">
        <i class="fa-solid fa-users"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Abonos Recaudados</h3>
        <div class="stat-value" style="color: var(--success);"><?= formatCLP($kpiAbonos['TotalAbonosPeriodo']) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;"><?= $kpiAbonos['CantidadAbonos'] ?> pagos recibidos en el período</small>
      </div>
      <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
        <i class="fa-solid fa-money-bill-trend-up"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Ventas Fiadas en Período</h3>
        <div class="stat-value" style="color: #818cf8;"><?= formatCLP($kpiFiado['TotalFiadoPeriodo']) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;"><?= $kpiFiado['TransaccionesFiado'] ?> compras a crédito</small>
      </div>
      <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;">
        <i class="fa-solid fa-file-invoice-dollar"></i>
      </div>
    </div>
  </div>

  <!-- Tabla 1: Cartera de Clientes Deudores (Ranking de Deuda) -->
  <div class="table-card" style="margin-bottom: 2rem;">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-user-clock" style="color: var(--primary);"></i> Cartera de Clientes con Deuda Activa (Ranking)
      </h3>
      <span style="font-size: 0.8rem; color: var(--text-muted);"><?= count($carteraDeudores) ?> clientes con saldo deudor</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Cliente</th>
          <th>RUT</th>
          <th>Teléfono</th>
          <th style="text-align: right;">Cupo Máximo ($)</th>
          <th style="text-align: right;">Deuda Pendiente ($)</th>
          <th style="width: 140px; text-align: center;">% Cupo Usado</th>
          <th style="text-align: center;">Estado</th>
          <th style="text-align: center;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($carteraDeudores)): ?>
          <tr>
            <td colspan="8" style="text-align: center; color: var(--success); padding: 2rem;">
              <i class="fa-solid fa-circle-check" style="font-size: 1.5rem; display: block; margin-bottom: 0.4rem;"></i>
              ¡Sin deudas pendientes! Todos los clientes están al día con sus pagos.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($carteraDeudores as $cd): ?>
            <?php 
              $limite = (int)$cd['LimiteCredito'];
              $deuda = (int)$cd['SaldoDeudor'];
              $pct = $limite > 0 ? round(($deuda / $limite) * 100, 1) : 100;
              
              $barColor = '#10b981';
              $badgeEstado = '<span class="badge badge-success">OK</span>';
              if ($pct >= 100) {
                $barColor = '#ef4444';
                $badgeEstado = '<span class="badge badge-danger">Bloqueado (100%)</span>';
              } elseif ($pct >= 75) {
                $barColor = '#f59e0b';
                $badgeEstado = '<span class="badge badge-warning">Cerca del Límite</span>';
              }
              $rutFmt = $cd['RutCuerpo'] ? number_format($cd['RutCuerpo'], 0, '', '.') . '-' . $cd['RutDv'] : 'Sin RUT';
            ?>
            <tr>
              <td>
                <strong style="color: var(--text-main);"><?= htmlspecialchars($cd['Nombre']) ?></strong>
                <?php if (!empty($cd['Email'])): ?>
                  <small style="display: block; color: var(--text-muted); font-size: 0.72rem;"><?= htmlspecialchars($cd['Email']) ?></small>
                <?php endif; ?>
              </td>
              <td style="font-family: monospace; color: var(--text-muted);"><?= $rutFmt ?></td>
              <td style="color: var(--text-muted);"><?= htmlspecialchars($cd['Telefono'] ?: '—') ?></td>
              <td style="text-align: right; font-family: monospace;"><?= formatCLP($limite) ?></td>
              <td style="text-align: right; font-weight: 700; color: var(--danger); font-family: monospace; font-size: 0.95rem;">
                <?= formatCLP($deuda) ?>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <div style="flex: 1; background: rgba(255,255,255,0.08); height: 7px; border-radius: 4px; overflow: hidden;">
                    <div style="width: <?= min(100, $pct) ?>%; background: <?= $barColor ?>; height: 100%; border-radius: 4px;"></div>
                  </div>
                  <span style="font-size: 0.78rem; font-weight: 700; color: <?= $barColor ?>; min-width: 40px; text-align: right;"><?= $pct ?>%</span>
                </div>
              </td>
              <td style="text-align: center;"><?= $badgeEstado ?></td>
              <td style="text-align: center;">
                <a href="clientes.php" class="btn btn-secondary" style="padding: 0.25rem 0.55rem; font-size: 0.75rem;" title="Registrar abono en módulo clientes">
                  <i class="fa-solid fa-hand-holding-dollar"></i> Abonar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Tabla 2: Historial de Abonos Recibidos en el Período -->
  <div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-receipt" style="color: var(--primary);"></i> Historial de Abonos y Recuperación de Cartera en el Período
      </h3>
      <span style="font-size: 0.8rem; color: var(--text-muted);">Total recuperado: <?= formatCLP($kpiAbonos['TotalAbonosPeriodo']) ?></span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>N° Abono</th>
          <th>Fecha y Hora</th>
          <th>Cliente</th>
          <th>Atendido Por</th>
          <th>Medio de Pago</th>
          <th style="text-align: right;">Monto Abonado</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($abonosList)): ?>
          <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No se han registrado abonos en el período seleccionado.</td></tr>
        <?php else: ?>
          <?php foreach ($abonosList as $ab): ?>
            <tr>
              <td><strong style="color: #818cf8;">#<?= $ab['AbonoID'] ?></strong></td>
              <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($ab['FechaAbono'])) ?></td>
              <td><strong style="color: var(--text-main);"><?= htmlspecialchars($ab['Cliente']) ?></strong></td>
              <td style="color: var(--text-muted);"><?= htmlspecialchars($ab['Cajero'] ?: 'Administración') ?></td>
              <td><span class="badge badge-secondary"><?= htmlspecialchars($ab['MetodoPago']) ?></span></td>
              <td style="text-align: right; font-weight: 700; color: var(--success); font-family: monospace; font-size: 0.95rem;">
                +<?= formatCLP($ab['Monto']) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- ============================================================== -->
<!-- PESTAÑA 7: COMPRAS Y GASTOS POR PROVEEDOR (EGRESOS) -->
<!-- ============================================================== -->
<?php if ($tab === 'compras'): ?>

  <div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--text-main);">
      <i class="fa-solid fa-truck-ramp-box" style="color: var(--primary);"></i> Reporte de Compras, Proveedores y Abastecimiento
    </h2>
    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
      Auditoría de egresos en mercadería, volumen de compra por distribuidor y recepción de facturas.
    </p>
  </div>

  <!-- KPIs de Compras -->
  <div class="grid-stats" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
      <div class="stat-info">
        <h3>Total Egresos Compras</h3>
        <div class="stat-value" style="color: var(--warning);"><?= formatCLP($kpiCompras['TotalEgresosCompras']) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Gasto total facturado en mercadería</small>
      </div>
      <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--warning);">
        <i class="fa-solid fa-truck-arrow-right"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Neto / IVA Crédito Fiscal</h3>
        <div class="stat-value" style="font-size: 1.35rem;"><?= formatCLP($kpiCompras['TotalNetoCompras']) ?></div>
        <small style="color: #38bdf8; font-size: 0.75rem;">IVA Crédito: <?= formatCLP($kpiCompras['TotalIvaCompras']) ?></small>
      </div>
      <div class="stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
        <i class="fa-solid fa-file-invoice"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Documentos / Facturas</h3>
        <div class="stat-value"><?= number_format($kpiCompras['TotalCompras'], 0, ',', '.') ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;"><?= $kpiCompras['TotalProveedoresActivos'] ?> distribuidores activos</small>
      </div>
      <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #818cf8;">
        <i class="fa-solid fa-boxes-stacked"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <h3>Compra Promedio</h3>
        <div class="stat-value" style="color: #a78bfa;"><?= formatCLP($compraPromedio) ?></div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Promedio por factura recibida</small>
      </div>
      <div class="stat-icon" style="background: rgba(167, 139, 250, 0.15); color: #a78bfa;">
        <i class="fa-solid fa-calculator"></i>
      </div>
    </div>
  </div>

  <!-- Tabla 1: Resumen y Ranking de Compras por Proveedor -->
  <div class="table-card" style="margin-bottom: 2rem;">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-dolly" style="color: var(--primary);"></i> Compras por Proveedor (Ranking de Gasto)
      </h3>
      <span style="font-size: 0.8rem; color: var(--text-muted);"><?= count($rankingProveedores) ?> proveedores en el período</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Proveedor / Razón Social</th>
          <th>RUT</th>
          <th>Teléfono</th>
          <th style="text-align: center;">Facturas</th>
          <th style="text-align: right;">Monto Neto ($)</th>
          <th style="text-align: right;">IVA Crédito ($)</th>
          <th style="text-align: right;">Total Comprado ($)</th>
          <th style="text-align: right;">Participación %</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rankingProveedores)): ?>
          <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay compras registradas en el período seleccionado.</td></tr>
        <?php else: ?>
          <?php foreach ($rankingProveedores as $pr): ?>
            <?php 
              $rutProv = $pr['RutCuerpo'] ? number_format($pr['RutCuerpo'], 0, '', '.') . '-' . $pr['RutDv'] : '—';
              $pctGasto = $kpiCompras['TotalEgresosCompras'] > 0 ? round(($pr['TotalComprado'] / $kpiCompras['TotalEgresosCompras']) * 100, 1) : 0;
            ?>
            <tr>
              <td><strong style="color: var(--text-main);"><?= htmlspecialchars($pr['RazonSocial']) ?></strong></td>
              <td style="font-family: monospace; color: var(--text-muted);"><?= $rutProv ?></td>
              <td style="color: var(--text-muted);"><?= htmlspecialchars($pr['Telefono'] ?: '—') ?></td>
              <td style="text-align: center; font-weight: 600;"><?= $pr['CantidadFacturas'] ?></td>
              <td style="text-align: right; font-family: monospace; color: var(--text-muted);"><?= formatCLP($pr['MontoNeto']) ?></td>
              <td style="text-align: right; font-family: monospace; color: #38bdf8;"><?= formatCLP($pr['MontoIva']) ?></td>
              <td style="text-align: right; font-weight: 700; color: var(--warning); font-family: monospace; font-size: 0.95rem;">
                <?= formatCLP($pr['TotalComprado']) ?>
              </td>
              <td style="text-align: right;">
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.4rem;">
                  <div style="width: 45px; background: rgba(255,255,255,0.08); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="width: <?= min(100, $pctGasto) ?>%; background: var(--warning); height: 100%;"></div>
                  </div>
                  <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); min-width: 40px; text-align: right;"><?= $pctGasto ?>%</span>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Tabla 2: Historial de Recepciones de Compra -->
  <div class="table-card">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">
        <i class="fa-solid fa-clipboard-check" style="color: var(--primary);"></i> Historial de Recepciones de Mercadería
      </h3>
      <span style="font-size: 0.8rem; color: var(--text-muted);">Mostrando hasta 100 compras</span>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>N° Compra</th>
          <th>Fecha</th>
          <th>Proveedor</th>
          <th>N° Factura / Guía</th>
          <th>Recepcionado Por</th>
          <th style="text-align: right;">Neto ($)</th>
          <th style="text-align: right;">IVA ($)</th>
          <th style="text-align: right;">Total Factura</th>
          <th style="text-align: center;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($comprasList)): ?>
          <tr><td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay recepciones de compra en el período.</td></tr>
        <?php else: ?>
          <?php foreach ($comprasList as $comp): ?>
            <tr>
              <td><strong style="color: #818cf8;">#<?= $comp['CompraID'] ?></strong></td>
              <td style="color: var(--text-muted); font-size: 0.85rem;"><?= date('d/m/Y H:i', strtotime($comp['FechaCompra'])) ?></td>
              <td><strong style="color: var(--text-main);"><?= htmlspecialchars($comp['Proveedor']) ?></strong></td>
              <td><span class="badge badge-secondary"><?= htmlspecialchars($comp['NumeroDocumento'] ?: 'S/N') ?></span></td>
              <td style="color: var(--text-muted);"><?= htmlspecialchars($comp['Usuario']) ?></td>
              <td style="text-align: right; font-family: monospace; color: var(--text-muted);"><?= formatCLP($comp['MontoNeto']) ?></td>
              <td style="text-align: right; font-family: monospace; color: #38bdf8;"><?= formatCLP($comp['MontoIva']) ?></td>
              <td style="text-align: right; font-weight: 700; color: var(--warning); font-family: monospace; font-size: 0.95rem;">
                <?= formatCLP($comp['MontoTotal']) ?>
              </td>
              <td style="text-align: center;">
                <a href="compras.php" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;" title="Ver en módulo de compras">
                  <i class="fa-solid fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

