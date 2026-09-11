<!DOCTYPE html>
<html lang="es" data-theme="<?= htmlspecialchars($temaModo ?? 'dark') ?>" data-accent="<?= htmlspecialchars($temaAcento ?? 'indigo') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($nombreEmpresa ?? 'Minimarket POS') ?> - Acceso</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= APP_VERSION ?>">
  <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
  <script>
    (function() {
      try {
        const localTheme = localStorage.getItem('theme_mode');
        if (localTheme) document.documentElement.setAttribute('data-theme', localTheme);
      } catch (e) {}
    })();
  </script>
</head>
<body class="login-body">

<div class="login-card">
  <div class="login-header">
    <?php if (!empty($logoUrl)): ?>
      <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="brand-logo-login">
    <?php else: ?>
      <div class="brand-icon" style="width: 54px; height: 54px; margin: 0 auto 0.75rem auto; font-size: 1.6rem;">
        <i class="fa-solid fa-store"></i>
      </div>
    <?php endif; ?>
    <h1 class="login-title"><?= htmlspecialchars($nombreEmpresa ?? 'Minimarket POS') ?></h1>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Acceso al Sistema Web</p>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #f87171; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.9rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 1.25rem;">
    <div>
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem;">USUARIO</label>
      <input type="text" name="username" class="form-control" placeholder="Ej: admin, cajero1" required autofocus autocomplete="off">
    </div>

    <div>
      <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem;">CONTRASEÑA</label>
      <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block" style="padding: 0.85rem; margin-top: 0.5rem; font-size: 1rem;">
      <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión
    </button>
  </form>

  <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-dark); text-align: center; font-size: 0.8rem; color: var(--text-muted);">
    Usuarios demo: <code>admin</code>, <code>supervisor</code>, <code>cajero1</code> (Clave: Demo1234)
  </div>
  <div style="margin-top: 0.75rem; text-align: center; font-size: 0.75rem; color: var(--text-muted);">
    Minimarket POS Web <?= APP_VERSION ?>
  </div>
</div>

</body>
</html>
