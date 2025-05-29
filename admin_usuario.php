<?php
session_start();
include("conexion.php");

// Validar acceso solo para Admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Función para enviar email de confirmación
function enviarEmailConfirmacion($email, $nombre, $token) {
    $link = "https://tu-dominio.com/confirmar.php?email=" . urlencode($email) . "&token=" . $token;
    $asunto = "Confirma tu cuenta";
    $mensaje = "Hola $nombre,\n\nGracias por registrarte.\nPor favor confirma tu cuenta haciendo clic en el siguiente enlace:\n$link\n\nSi no te registraste, ignora este mensaje.";
    $cabeceras = "From: no-reply@tu-dominio.com\r\n";
    
    // Usar mail() - recuerda que tu servidor debe soportar envíos SMTP
    return mail($email, $asunto, $mensaje, $cabeceras);
}

// Procesar formulario (crear o editar usuario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (filter_has_var(INPUT_POST, 'editar_id') && !empty($_POST['editar_id'])) {
        // Edición usuario existente
        $id = $_POST['editar_id'];
        $nombre = $_POST['nombreUsuario'];
        $email = $_POST['emailUsuario'];
        $rol = $_POST['rolUsuario'];

        if (!empty($_POST['passUsuario'])) {
            $password = password_hash($_POST['passUsuario'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?");
            $stmt->bind_param("ssssi", $nombre, $email, $password, $rol, $id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");
            $stmt->bind_param("sssi", $nombre, $email, $rol, $id);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Usuario actualizado correctamente.'); window.location.href='admin_usuario.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error al actualizar el usuario.'); window.location.href='admin_usuario.php';</script>";
            exit;
        }
        $stmt->close();

    } else {
        // Crear usuario nuevo
        if (!empty($_POST['nombreUsuario']) &&
            !empty($_POST['emailUsuario']) &&
            !empty($_POST['passUsuario']) &&
            !empty($_POST['rolUsuario'])) {

            $nombre = $_POST['nombreUsuario'];
            $email = $_POST['emailUsuario'];
            $password = password_hash($_POST['passUsuario'], PASSWORD_DEFAULT);
            $rol = $_POST['rolUsuario'];

            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('Correo electrónico no válido.'); window.location.href='admin_usuario.php';</script>";
                exit;
            }

            // Generar token de confirmación
            $token = bin2hex(random_bytes(16)); // 32 caracteres hex

            // Insertar usuario con confirmado = 0 y token
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, confirmado, token_confirmacion) VALUES (?, ?, ?, ?, 0, ?)");
            $stmt->bind_param("sssss", $nombre, $email, $password, $rol, $token);

            if ($stmt->execute()) {
                // Enviar correo de confirmación
                if (enviarEmailConfirmacion($email, $nombre, $token)) {
                    echo "<script>alert('Usuario agregado correctamente. Revisa tu correo para confirmar la cuenta.'); window.location.href='admin_usuario.php';</script>";
                } else {
                    echo "<script>alert('Usuario creado pero no se pudo enviar el correo de confirmación.'); window.location.href='admin_usuario.php';</script>";
                }
                exit;
            } else {
                echo "<script>alert('Error al agregar el usuario.'); window.location.href='admin_usuario.php';</script>";
                exit;
            }
            $stmt->close();
        }
    }
}

$result = $conn->query("SELECT id, nombre, email, rol FROM usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Administrar Usuarios - Sistema Salud</title>
  <link rel="stylesheet" href="css/admin_usuario.css" />
  <style>
    .top-buttons {
      position: absolute;
      top: 10px;
      right: 10px;
      display: flex;
      gap: 10px;
    }
    .top-buttons a {
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      padding: 8px 12px;
      border-radius: 5px;
      font-family: inherit;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background-color 0.3s;
    }
    .top-buttons a.logout {
      background-color: #dc3545;
    }
    .top-buttons a.logout:hover {
      background-color: #a71d2a;
    }
    .top-buttons a:hover:not(.logout) {
      background-color: #0056b3;
    }
    .top-buttons svg {
      width: 20px;
      height: 20px;
    }
  </style>
  <script>
    function editarUsuario(id, nombre, email, rol) {
      document.getElementById('editar_id').value = id;
      document.getElementById('nombreUsuario').value = nombre;
      document.getElementById('emailUsuario').value = email;
      document.getElementById('passUsuario').value = '';
      document.getElementById('rolUsuario').value = rol;
      document.getElementById('btnSubmit').textContent = 'Actualizar Usuario';
    }
    function limpiarFormulario() {
      document.getElementById('editar_id').value = '';
      document.getElementById('formUsuario').reset();
      document.getElementById('btnSubmit').textContent = 'Agregar Usuario';
    }
  </script>
</head>
<body>
  <div class="top-buttons">
    <a href="index_admin.php" title="Inicio">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
      Inicio
    </a>
    <a href="admin_documento.php" title="Archivo">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/></svg>
      Archivo
    </a>
    <a href="login.php" class="logout" title="Cerrar sesión">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M16 13v-2H7V8l-5 4 5 4v-3zM19 3h-7v2h7v14h-7v2h7a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
      Cerrar sesión
    </a>
  </div>

  <div class="admin-container">
    <h2>Administración de Usuarios</h2>

    <form id="formUsuario" method="POST" action="">
      <input type="hidden" id="editar_id" name="editar_id" />

      <input type="text" id="nombreUsuario" name="nombreUsuario" placeholder="Nombre completo" required />
      <input type="email" id="emailUsuario" name="emailUsuario" placeholder="Correo electrónico" required />
      <input type="password" id="passUsuario" name="passUsuario" placeholder="Contraseña (dejar vacío si no desea cambiarla)" />

      <select id="rolUsuario" name="rolUsuario" required>
        <option value="" disabled selected>Seleccione un rol</option>
        <option value="Admin">Admin</option>
        <option value="Usuario">Usuario</option>
      </select>

      <button type="submit" id="btnSubmit">Agregar Usuario</button>
      <button type="button" onclick="limpiarFormulario()">Limpiar</button>
    </form>

    <h3>Lista de Usuarios</h3>
    <table id="tablaUsuarios" border="1" cellpadding="10" cellspacing="0">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['rol']) ?></td>
            <td>
              <a href="javascript:void(0)" class="action-btn edit-btn" 
                 onclick="editarUsuario('<?= $row['id'] ?>', '<?= htmlspecialchars(addslashes($row['nombre'])) ?>', '<?= htmlspecialchars(addslashes($row['email'])) ?>', '<?= $row['rol'] ?>')">Editar</a>
              <a href="eliminar_usuario.php?id=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>

<?php
$conn->close();
?>
