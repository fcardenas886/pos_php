<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
$esSupervisorNav = in_array($user['rol'], ['Administrador', 'Supervisor'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Minimarket POS - Sistema Web Completo</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
  <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken()) ?>">
</head>
<body>

<?php
$changelogActual = APP_CHANGELOG[APP_VERSION] ?? [];
?>
<!-- Pantalla de bienvenida tras actualizar: barra de progreso + novedades reales -->
<div id="updateOverlay" class="update-overlay" style="display: none;">
  <div class="update-card">

    <div class="update-loader">
      <div class="update-loader__ring"></div>
      <i class="fa-solid fa-cloud-arrow-down update-loader__icon"></i>
    </div>

    <div>
      <h2 class="update-title">Actualizando Sistema</h2>
      <p class="update-subtitle">Aplicando la versión <strong><?= htmlspecialchars(APP_VERSION) ?></strong>. Esto tomará solo unos segundos&hellip;</p>
    </div>

    <div class="update-progress">
      <div id="updateProgressBar" class="update-progress__bar"></div>
    </div>
    <span id="updateProgressText" class="update-progress__text">0%</span>

    <?php if ($changelogActual): ?>
    <div class="update-changelog">
      <div class="update-changelog__label">Novedades de esta versión</div>
      <ul>
        <?php foreach ($changelogActual as $item): ?>
          <li><i class="fa-solid fa-circle-check"></i><span><?= htmlspecialchars($item) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  (function() {
    const CURRENT_VERSION = '<?= APP_VERSION ?>';
    let lastSeen = null;
    try { lastSeen = localStorage.getItem('last_seen_version'); } catch (e) {}
    if (lastSeen === CURRENT_VERSION) return;

    const overlay = document.getElementById('updateOverlay');
    const bar = document.getElementById('updateProgressBar');
    const txt = document.getElementById('updateProgressText');
    const bullets = Array.from(document.querySelectorAll('.update-changelog li'));
    overlay.style.display = 'flex';

    let progress = 0;
    const interval = setInterval(() => {
      progress = Math.min(100, progress + Math.floor(Math.random() * 8) + 4);
      bar.style.width = progress + '%';
      txt.textContent = progress + '%';

      // Revelar las novedades a medida que avanza la barra
      const revelar = Math.floor((progress / 100) * bullets.length);
      bullets.forEach((li, i) => { if (i < revelar) li.classList.add('is-visible'); });

      if (progress === 100) {
        clearInterval(interval);
        bullets.forEach(li => li.classList.add('is-visible'));
        setTimeout(() => {
          overlay.classList.add('is-hiding');
          setTimeout(() => {
            overlay.style.display = 'none';
            try { localStorage.setItem('last_seen_version', CURRENT_VERSION); } catch (e) {}
          }, 400);
        }, 900);
      }
    }, 120);
  })();
</script>

<script>window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;</script>
<script src="assets/js/ui.js?v=<?= APP_VERSION ?>"></script>

<nav class="navbar">
  <a href="index.php" class="brand">
    <div class="brand-icon">
      <i class="fa-solid fa-store"></i>
    </div>
    <span>Minimarket POS</span>
  </a>

  <ul class="nav-links">
    <!-- Inicio -->
    <li class="nav-item">
      <a href="index.php" class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-pie"></i> Inicio
      </a>
    </li>

    <!-- Caja POS -->
    <li class="nav-item">
      <a href="pos.php" class="nav-link <?= $currentPage == 'pos.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-cart-shopping"></i> Caja POS
      </a>
    </li>

    <!-- Grupo: Caja y Ventas -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['caja.php', 'ventas.php', 'devoluciones.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-cash-register"></i> Caja y Ventas <i class="fa-solid fa-chevron-down nav-caret"></i>
      </div>
      <ul class="dropdown-menu" style="min-width: 230px;">
        <!-- Sección: Caja -->
        <li class="dropdown-section" style="--section-color: #f59e0b;">
          <i class="fa-solid fa-wallet"></i> Operaciones de Caja
        </li>
        <li><a href="caja.php" class="dropdown-item"><i class="fa-solid fa-wallet"></i> Turnos y Arqueo</a></li>
        
        <li class="dropdown-divider"></li>

        <!-- Sección: Ventas -->
        <li class="dropdown-section" style="--section-color: #3b82f6;">
          <i class="fa-solid fa-receipt"></i> Ventas y Devoluciones
        </li>
        <li><a href="ventas.php" class="dropdown-item"><i class="fa-solid fa-file-invoice"></i> Ventas y Anulaciones</a></li>
        <li><a href="devoluciones.php" class="dropdown-item"><i class="fa-solid fa-rotate-left"></i> Devoluciones y Vales</a></li>
      </ul>
    </li>

    <!-- Grupo: Mantenedores -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['productos.php', 'categorias.php', 'clientes.php', 'promociones.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-folder-open"></i> Mantenedores <i class="fa-solid fa-chevron-down nav-caret"></i>
      </div>
      <ul class="dropdown-menu">
        <?php if ($esSupervisorNav): ?>
        <li><a href="productos.php" class="dropdown-item"><i class="fa-solid fa-box"></i> Productos y Precios</a></li>
        <li><a href="categorias.php" class="dropdown-item"><i class="fa-solid fa-folder"></i> Categorías</a></li>
        <?php endif; ?>
        <li><a href="clientes.php" class="dropdown-item"><i class="fa-solid fa-users"></i> Clientes y Fiado</a></li>
        <?php if ($esSupervisorNav): ?>
        <li><a href="promociones.php" class="dropdown-item"><i class="fa-solid fa-tags"></i> Ofertas y Promociones</a></li>
        <?php endif; ?>
      </ul>
    </li>

    <!-- Grupo: Stock y Compras -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['kardex.php', 'ajustes.php', 'alertas_stock.php', 'notaspedido.php', 'compras.php', 'proveedores.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-boxes-stacked"></i> Stock y Compras <i class="fa-solid fa-chevron-down nav-caret"></i>
      </div>
      <ul class="dropdown-menu" style="min-width: 250px;">
        <!-- Sección: Inventario -->
        <li class="dropdown-section" style="--section-color: #818cf8;">
          <i class="fa-solid fa-warehouse"></i> Control de Stock
        </li>
        <li><a href="kardex.php" class="dropdown-item"><i class="fa-solid fa-arrow-right-arrow-left"></i> Kardex de Movimientos</a></li>
        <?php if ($esSupervisorNav): ?>
        <li><a href="ajustes.php" class="dropdown-item"><i class="fa-solid fa-sliders"></i> Ajustes de Stock</a></li>
        <?php endif; ?>
        <li><a href="alertas_stock.php" class="dropdown-item"><i class="fa-solid fa-triangle-exclamation"></i> Alertas de Stock</a></li>

        <!-- Separador y Compras (solo para Supervisor) -->
        <?php if ($esSupervisorNav): ?>
        <li class="dropdown-divider"></li>
        
        <li class="dropdown-section" style="--section-color: #10b981;">
          <i class="fa-solid fa-truck-ramp-box"></i> Compras y Proveedores
        </li>
        <li><a href="notaspedido.php" class="dropdown-item"><i class="fa-solid fa-file-signature"></i> Notas de Pedido</a></li>
        <li><a href="compras.php" class="dropdown-item"><i class="fa-solid fa-truck-arrow-right"></i> Recepción de Compras</a></li>
        <li><a href="proveedores.php" class="dropdown-item"><i class="fa-solid fa-truck"></i> Proveedores</a></li>
        <?php endif; ?>
      </ul>
    </li>

    <!-- Grupo: Reportes -->
    <?php if ($esSupervisorNav): ?>
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['reporte_utilidades.php', 'reportes_z.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-line"></i> Reportes <i class="fa-solid fa-chevron-down nav-caret"></i>
      </div>
      <ul class="dropdown-menu">
        <li><a href="reporte_utilidades.php" class="dropdown-item"><i class="fa-solid fa-sack-dollar"></i> Utilidades y Márgenes</a></li>
        <li><a href="reportes_z.php" class="dropdown-item"><i class="fa-solid fa-file-invoice-dollar"></i> Cierre Z Fiscal</a></li>
      </ul>
    </li>
    <?php endif; ?>

    <!-- Grupo: Administración -->
    <?php if (in_array($user['rol'], ['Administrador', 'Supervisor'])): ?>
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['usuarios.php', 'cajas.php', 'configuracion.php', 'ayuda.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-shield-halved"></i> Admin <i class="fa-solid fa-chevron-down nav-caret"></i>
      </div>
      <ul class="dropdown-menu">
        <li><a href="usuarios.php" class="dropdown-item"><i class="fa-solid fa-user-gear"></i> Usuarios y Roles</a></li>
        <li><a href="cajas.php" class="dropdown-item"><i class="fa-solid fa-cash-register"></i> Cajas Físicas</a></li>
        <li><a href="configuracion.php" class="dropdown-item"><i class="fa-solid fa-sliders"></i> Ajustes Generales</a></li>
        <li><a href="ayuda.php" class="dropdown-item"><i class="fa-solid fa-circle-question"></i> Soporte / Ayuda</a></li>
      </ul>
    </li>
    <?php endif; ?>
  </ul>

  <div class="user-pill">
    <i class="fa-solid fa-user"></i>
    <span><?= htmlspecialchars($user['nombre']) ?></span>
    <span class="user-badge"><?= htmlspecialchars($user['rol']) ?></span>
    <a href="logout.php" title="Cerrar Sesión" style="color: var(--danger); margin-left: 0.5rem;">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</nav>

<main class="container">
