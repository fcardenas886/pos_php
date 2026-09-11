<div style="margin-bottom: 2rem;">
  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <?php if (!empty($infoEmpresa['MINIMARKET_LOGO_URL'])): ?>
          <img src="<?= htmlspecialchars($infoEmpresa['MINIMARKET_LOGO_URL']) ?>" alt="Logo" style="max-height: 48px; max-width: 140px; object-fit: contain;">
        <?php else: ?>
          <div class="brand-icon" style="width: 44px; height: 44px; font-size: 1.3rem;">
            <i class="fa-solid fa-store"></i>
          </div>
        <?php endif; ?>
        <div>
          <h1 style="font-size: 1.6rem; font-weight: 700; color: var(--text-main); margin: 0;">
            <?= htmlspecialchars($infoEmpresa['MINIMARKET_NOMBRE'] ?? 'Minimarket POS') ?>
          </h1>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">Sistema Integral de Punto de Venta, Control de Stock y Gestión de Retail</p>
        </div>
      </div>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <a href="pos.php" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;">
        <i class="fa-solid fa-cash-register"></i> Ir a Caja POS
      </a>
      <a href="configuracion.php" class="btn btn-secondary" style="padding: 0.6rem 1.25rem; font-size: 0.9rem;">
        <i class="fa-solid fa-sliders"></i> Ajustes
      </a>
    </div>
  </div>
</div>

<!-- Ficha Técnica del Sistema -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
  <div class="table-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: var(--success); font-size: 1.4rem;">
      <i class="fa-solid fa-code-branch"></i>
    </div>
    <div>
      <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Versión Actual</div>
      <div style="font-size: 1.25rem; font-weight: 800; color: var(--success); font-family: monospace;">
        <?= APP_VERSION ?>
      </div>
    </div>
  </div>

  <div class="table-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(79, 70, 229, 0.15); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.4rem;">
      <i class="fa-brands fa-php"></i>
    </div>
    <div>
      <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Motor de Backend</div>
      <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
        PHP <?= htmlspecialchars($phpVersion) ?>
      </div>
    </div>
  </div>

  <div class="table-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: var(--warning); font-size: 1.4rem;">
      <i class="fa-solid fa-database"></i>
    </div>
    <div>
      <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Base de Datos</div>
      <div style="font-size: 1.15rem; font-weight: 700; color: var(--text-main);">
        MySQL <?= htmlspecialchars(explode('-', $mysqlVersion)[0]) ?>
      </div>
    </div>
  </div>

  <div class="table-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(6, 182, 212, 0.15); display: flex; align-items: center; justify-content: center; color: #06b6d4; font-size: 1.4rem;">
      <i class="fa-solid fa-boxes-stacked"></i>
    </div>
    <div>
      <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Productos Activos</div>
      <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-main);">
        <?= number_format($totalProductos, 0, ',', '.') ?>
      </div>
    </div>
  </div>

  <div class="table-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; color: var(--danger); font-size: 1.4rem;">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div>
      <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Ventas Procesadas</div>
      <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-main);">
        <?= number_format($totalVentas, 0, ',', '.') ?>
      </div>
    </div>
  </div>
</div>

<!-- Sección: Evolución y Cronología del Sistema -->
<div style="background: var(--card-bg); border: 1px solid var(--border-dark); border-radius: 16px; padding: 2rem; box-shadow: var(--shadow-lg);">
  <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-dark); padding-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
      <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">
        <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Historial de Evolución del Sistema
      </h2>
      <p style="color: var(--text-muted); font-size: 0.85rem;">Cronología completa de versiones, módulos y mejoras incorporadas desde el inicio del proyecto.</p>
    </div>
    <div>
      <span style="font-size: 0.8rem; background: rgba(255,255,255,0.06); padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid var(--border-dark); color: var(--text-muted);">
        <strong><?= count(APP_CHANGELOG) ?></strong> entregas registradas
      </span>
    </div>
  </div>

  <div class="timeline">
    <?php foreach (APP_CHANGELOG as $version => $novedades): ?>
      <?php $esActual = ($version === APP_VERSION); ?>
      <div class="timeline-item <?= $esActual ? 'active' : '' ?>">
        <div class="timeline-marker">
          <?php if ($esActual): ?>
            <i class="fa-solid fa-check"></i>
          <?php else: ?>
            <i class="fa-solid fa-circle"></i>
          <?php endif; ?>
        </div>

        <div class="timeline-content">
          <div class="timeline-header">
            <div class="timeline-version">
              <span><?= htmlspecialchars($version) ?></span>
              <?php if ($esActual): ?>
                <span style="font-size: 0.72rem; background: var(--success); color: #fff; padding: 0.15rem 0.55rem; border-radius: 20px; font-weight: 700; letter-spacing: 0.03em;">
                  ACTUALMENTE ACTIVA
                </span>
              <?php endif; ?>
            </div>
          </div>

          <ul class="timeline-bullets">
            <?php foreach ($novedades as $bullet): ?>
              <li>
                <i class="fa-solid fa-circle-check"></i>
                <span><?= htmlspecialchars($bullet) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>