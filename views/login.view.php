<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Minimarket POS</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">

<div class="login-card">
  <div class="login-header">
    <div class="brand-icon" style="width: 54px; height: 54px; margin: 0 auto; font-size: 1.6rem;">
      <i class="fa-solid fa-store"></i>
    </div>
    <h1 class="login-title">Minimarket POS</h1>
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
</div>

</body>
</html>
