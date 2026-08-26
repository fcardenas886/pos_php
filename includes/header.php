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

    <!-- Turnos / Caja -->
    <li class="nav-item">
      <a href="caja.php" class="nav-link <?= $currentPage == 'caja.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-cash-register"></i> Turnos / Arqueo
      </a>
    </li>

    <!-- Grupo: Operaciones -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['compras.php', 'notaspedido.php', 'devoluciones.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-truck-ramp-box"></i> Operaciones <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu">
        <?php if ($esSupervisorNav): ?>
        <li><a href="notaspedido.php" class="dropdown-item"><i class="fa-solid fa-file-signature"></i> Notas de Pedido</a></li>
        <li><a href="compras.php" class="dropdown-item"><i class="fa-solid fa-truck-arrow-right"></i> Recepción de Compras</a></li>
        <?php endif; ?>
        <li><a href="devoluciones.php" class="dropdown-item"><i class="fa-solid fa-rotate-left"></i> Devoluciones y Vales</a></li>
        <li><a href="ventas.php" class="dropdown-item"><i class="fa-solid fa-receipt"></i> Ventas / Anulaciones</a></li>
      </ul>
    </li>

    <!-- Grupo: Inventario -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['kardex.php', 'ajustes.php', 'alertas_stock.php', 'consulta_precio.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-boxes-stacked"></i> Inventario <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu">
        <li><a href="consulta_precio.php" class="dropdown-item"><i class="fa-solid fa-barcode"></i> Consulta de Precios</a></li>
        <li><a href="kardex.php" class="dropdown-item"><i class="fa-solid fa-receipt"></i> Kardex de Movimientos</a></li>
        <?php if ($esSupervisorNav): ?>
        <li><a href="ajustes.php" class="dropdown-item"><i class="fa-solid fa-sliders"></i> Ajustes de Stock</a></li>
        <?php endif; ?>
        <li><a href="alertas_stock.php" class="dropdown-item"><i class="fa-solid fa-triangle-exclamation"></i> Alertas y Sugerencias</a></li>
      </ul>
    </li>

    <!-- Grupo: Maestros (Catálogos) -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['productos.php', 'categorias.php', 'clientes.php', 'proveedores.php', 'promociones.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-folder-open"></i> Maestros <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu">
        <?php if ($esSupervisorNav): ?>
        <li><a href="productos.php" class="dropdown-item"><i class="fa-solid fa-box"></i> Productos y Precios</a></li>
        <li><a href="categorias.php" class="dropdown-item"><i class="fa-solid fa-folder"></i> Categorías</a></li>
        <?php endif; ?>
        <li><a href="clientes.php" class="dropdown-item"><i class="fa-solid fa-users"></i> Clientes y Fiado</a></li>
        <?php if ($esSupervisorNav): ?>
        <li><a href="proveedores.php" class="dropdown-item"><i class="fa-solid fa-truck"></i> Proveedores</a></li>
        <li><a href="promociones.php" class="dropdown-item"><i class="fa-solid fa-tags"></i> Ofertas y Promociones</a></li>
        <?php endif; ?>
      </ul>
    </li>

    <!-- Grupo: Reportes -->
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['reporte_utilidades.php', 'reportes_z.php', 'ventas.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-chart-line"></i> Reportes <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu">
        <?php if ($esSupervisorNav): ?>
        <li><a href="reporte_utilidades.php" class="dropdown-item"><i class="fa-solid fa-sack-dollar"></i> Utilidades y Márgenes</a></li>
        <li><a href="reportes_z.php" class="dropdown-item"><i class="fa-solid fa-file-invoice-dollar"></i> Cierre Z Fiscal</a></li>
        <?php endif; ?>
        <li><a href="ventas.php" class="dropdown-item"><i class="fa-solid fa-receipt"></i> Historial de Ventas</a></li>
      </ul>
    </li>

    <!-- Grupo: Administración -->
    <?php if (in_array($user['rol'], ['Administrador', 'Supervisor'])): ?>
    <li class="nav-item">
      <div class="nav-link <?= in_array($currentPage, ['usuarios.php', 'cajas.php', 'configuracion.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-shield-halved"></i> Admin <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
      </div>
      <ul class="dropdown-menu">
        <li><a href="usuarios.php" class="dropdown-item"><i class="fa-solid fa-user-gear"></i> Usuarios y Roles</a></li>
        <li><a href="cajas.php" class="dropdown-item"><i class="fa-solid fa-cash-register"></i> Cajas Físicas</a></li>
        <li><a href="configuracion.php" class="dropdown-item"><i class="fa-solid fa-sliders"></i> Configuraciones Generales</a></li>
      </ul>
    </li>
    <?php endif; ?>

    <!-- Ayuda -->
    <li class="nav-item">
      <a href="ayuda.php" class="nav-link <?= $currentPage == 'ayuda.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-circle-question"></i> Ayuda
      </a>
    </li>
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
