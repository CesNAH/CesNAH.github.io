<?php
include("conexion.php");

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND token_confirmacion = ? AND confirmado = 0");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Confirmar usuario
        $stmtUpdate = $conn->prepare("UPDATE usuarios SET confirmado = 1, token_confirmacion = NULL WHERE email = ?");
        $stmtUpdate->bind_param("s", $email);
        $stmtUpdate->execute();

        echo "Cuenta confirmada correctamente. Ya puedes iniciar sesión.";
    } else {
        echo "El enlace no es válido o la cuenta ya fue confirmada.";
    }
} else {
    echo "Parámetros incorrectos.";
}
?>
