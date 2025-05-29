<?php
session_start();
include("conexion.php");

$error = "";

if (filter_has_var(INPUT_POST, 'email') && filter_has_var(INPUT_POST, 'password')) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($pass, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigir según el rol
            if ($usuario['rol'] === 'Admin') {
                header("Location: index_admin.php");
            } else {
                header("Location: admin_documento2.php");
            }
            exit;
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Correo no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>
  <div class="login-container">
    <h2>Iniciar Sesión</h2>
    <img src="imagen/img1.png.png" alt="Logo EsSalud" class="logo" />
    
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" action="">
      <input type="email" name="email" placeholder="Correo electrónico" required><br>
      <input type="password" name="password" placeholder="Contraseña" required><br>
      <button type="submit">Ingresar</button>
    </form>

    <div class="footer">
      © 2025 Seguro Social de Salud del Perú - EsSalud
    </div>
  </div>
</body>
</html>
