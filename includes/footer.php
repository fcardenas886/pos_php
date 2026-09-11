</main>

<footer style="text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: 0.85rem; border-top: 1px solid var(--border-dark); margin-top: auto;">
  <p>
    &copy; <?= date('Y') ?> Minimarket POS Web 
    <a href="acerca.php" style="color: var(--primary); font-weight: 600; text-decoration: none; margin-left: 0.25rem;" title="Ver evolución del sistema y notas de versiones">
      <?= APP_VERSION ?> · Acerca de y Novedades
    </a>
    — Operando en Laragon (PHP + MySQL)
  </p>
</footer>

<script src="assets/js/main.js?v=<?= APP_VERSION ?>"></script>
</body>
</html>
