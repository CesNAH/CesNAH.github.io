<?php
session_start();

// Verificar que el usuario haya iniciado sesión y sea administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'Admin') {
    // Redirigir al login si no está autenticado o no es administrador
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel de Administración - Bienvenida</title>
  <link rel="stylesheet" href="css/index_admin.css" />
</head>
<body>
  <div class="admin-index-container">
    <h1>Bienvenido al Panel de Administración, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
    <p>Selecciona una opción para administrar el sistema:</p>

    <div class="botones">
      <a href="admin_usuario.php" class="btn">Administrar Usuarios</a>
      <a href="admin_documento.php" class="btn">Administrar Documentos</a>
      <a href="logout.php" class="btn btn-logout">Cerrar Sesión</a>
    </div>
  </div>
</body>
</html>