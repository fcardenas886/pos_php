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
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
  <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken()) ?>">
</head>
<body>

<!-- Pantalla de Carga / Actualización del Sistema -->
<div id="updateOverlay" style="display: none; position: fixed; inset: 0; background: #0f172a; z-index: 9999; justify-content: center; align-items: center; flex-direction: column; color: #fff; font-family: sans-serif;">
  <div style="text-align: center; max-width: 400px; padding: 2rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
    
    <!-- Animación de carga -->
    <div class="update-loader" style="position: relative; width: 80px; height: 80px;">
      <div style="position: absolute; border: 4px solid rgba(129, 140, 248, 0.1); border-left-color: #818cf8; border-radius: 50%; width: 100%; height: 100%; animation: spin 1s linear infinite;"></div>
      <i class="fa-solid fa-cloud-arrow-down" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem; color: #818cf8; animation: pulse 1.5s infinite;"></i>
    </div>

    <div>
      <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0 0 0.5rem; letter-spacing: -0.025em; color: #fff;">Actualizando Sistema</h2>
      <p style="color: #94a3b8; font-size: 0.95rem; margin: 0; line-height: 1.5;">Instalando las optimizaciones de la versión <strong style="color: #818cf8;"><?= APP_VERSION ?></strong>. Esto tomará solo unos segundos...</p>
    </div>

    <!-- Barra de Progreso Simulada -->
    <div style="width: 100%; height: 6px; background: #1e293b; border-radius: 10px; overflow: hidden; margin-top: 0.5rem;">
      <div id="updateProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #818cf8, #34d399); border-radius: 10px; transition: width 0.1s ease-out;"></div>
    </div>
    
    <span id="updateProgressText" style="font-size: 0.8rem; font-weight: 700; color: #64748b; font-family: monospace;">Descargando paquetes: 0%</span>
  </div>
</div>

<style>
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 0.6; transform: translate(-50%, -50%) scale(0.9); }
  }
</style>

<script>
  (function() {
    const CURRENT_VERSION = '<?= APP_VERSION ?>';
    const lastSeen = localStorage.getItem('last_seen_version');
    
    if (lastSeen !== CURRENT_VERSION) {
      // Es la primera vez que inicia esta versión
      const overlay = document.getElementById('updateOverlay');
      overlay.style.display = 'flex';
      
      let progress = 0;
      const bar = document.getElementById('updateProgressBar');
      const txt = document.getElementById('updateProgressText');
      
      const steps = [
        "Optimizando base de datos...",
        "Actualizando módulos de caja...",
        "Limpiando caché de navegación...",
        "Cargando catálogos...",
        "¡Sistema actualizado con éxito!"
      ];
      
      const interval = setInterval(() => {
        progress += Math.floor(Math.random() * 8) + 4;
        if (progress > 100) progress = 100;
        
        bar.style.width = progress + '%';
        
        // Elegir texto del paso basado en el porcentaje
        let stepIdx = Math.floor((progress / 100) * steps.length);
        if (stepIdx >= steps.length) stepIdx = steps.length - 1;
        txt.textContent = steps[stepIdx] + ' (' + progress + '%)';
        
        if (progress === 100) {
          clearInterval(interval);
          setTimeout(() => {
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.4s ease-out';
            setTimeout(() => {
              overlay.style.display = 'none';
              localStorage.setItem('last_seen_version', CURRENT_VERSION);
            }, 400);
          }, 600);
        }
      }, 120);
    }
  })();
</script>

<script>window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;</script>

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
        <i class="fa-solid fa-cash-register"></i> Caja y Ventas <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu" style="min-width: 230px;">
        <!-- Sección: Caja -->
        <li style="padding: 0.4rem 1rem 0.25rem; font-size: 0.72rem; font-weight: 800; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-wallet" style="color: #f59e0b; width: auto; font-size: 0.75rem;"></i> Operaciones de Caja
        </li>
        <li><a href="caja.php" class="dropdown-item"><i class="fa-solid fa-wallet"></i> Turnos y Arqueo</a></li>
        
        <li style="border-top: 1px solid var(--border-dark); margin: 0.4rem 0;"></li>

        <!-- Sección: Ventas -->
        <li style="padding: 0.4rem 1rem 0.25rem; font-size: 0.72rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-receipt" style="color: #3b82f6; width: auto; font-size: 0.75rem;"></i> Ventas y Devoluciones
        </li>
        <li><a href="ventas.php" class="dropdown-item"><i class="fa-solid fa-file-invoice"></i> Ventas y Anulaciones</a></li>
        <li><a href="devoluciones.php" class="dropdown-item"><i class="fa-solid fa-rotate-left"></i> Devoluciones y Vales</a></li>
      </ul>
    </li>

    <!-- Grupo: Mantenedores -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['productos.php', 'categorias.php', 'clientes.php', 'promociones.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-folder-open"></i> Mantenedores <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
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
        <i class="fa-solid fa-boxes-stacked"></i> Stock y Compras <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu" style="min-width: 250px;">
        <!-- Sección: Inventario -->
        <li style="padding: 0.4rem 1rem 0.25rem; font-size: 0.72rem; font-weight: 800; color: #818cf8; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-warehouse" style="color: #818cf8; width: auto; font-size: 0.75rem;"></i> Control de Stock
        </li>
        <li><a href="kardex.php" class="dropdown-item"><i class="fa-solid fa-arrow-right-arrow-left"></i> Kardex de Movimientos</a></li>
        <?php if ($esSupervisorNav): ?>
        <li><a href="ajustes.php" class="dropdown-item"><i class="fa-solid fa-sliders"></i> Ajustes de Stock</a></li>
        <?php endif; ?>
        <li><a href="alertas_stock.php" class="dropdown-item"><i class="fa-solid fa-triangle-exclamation"></i> Alertas de Stock</a></li>

        <!-- Separador y Compras (solo para Supervisor) -->
        <?php if ($esSupervisorNav): ?>
        <li style="border-top: 1px solid var(--border-dark); margin: 0.4rem 0;"></li>
        
        <li style="padding: 0.4rem 1rem 0.25rem; font-size: 0.72rem; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-truck-ramp-box" style="color: #10b981; width: auto; font-size: 0.75rem;"></i> Compras y Proveedores
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
        <i class="fa-solid fa-chart-line"></i> Reportes <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
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
        <i class="fa-solid fa-shield-halved"></i> Admin <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
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
