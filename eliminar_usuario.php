<?php
include("conexion.php");

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

header("Location: admin_usuario.php");
?>