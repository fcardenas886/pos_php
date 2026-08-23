<?php
require_once __DIR__ . '/includes/header.php';
checkRole(['Administrador', 'Supervisor']);

$pdo = getDB();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (!empty($nombre)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Categorias (Nombre, Descripcion) VALUES (:nombre, :desc)");
            $stmt->execute([':nombre' => $nombre, ':desc' => $descripcion]);
            $message = 'Categoría creada exitosamente.';
        } catch (Exception $e) {
            $error = 'Error al guardar categoría: ' . $e->getMessage();
        }
    }
}

$categorias = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM Productos p WHERE p.CategoriaID = c.CategoriaID AND p.Activo = TRUE) AS TotalProductos
    FROM Categorias c ORDER BY c.Nombre ASC
")->fetchAll();

include __DIR__ . '/views/categorias.view.php';
require_once __DIR__ . '/includes/footer.php';
