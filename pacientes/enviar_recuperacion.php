<?php
include '../conexion.php';
include 'email.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT correo FROM Usuarios WHERE correo = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $token = bin2hex(random_bytes(16));
        $url = "http://localhost/CitasMedicas/Pacientes/restablecer_contrasena.php?token=$token";

        $data = [
            'email' => $email,
            'expiracion' => strtotime('+1 hour')
        ];
        file_put_contents(__DIR__."/tokens/$token.json", json_encode($data));

        $asunto = "Recuperación de contraseña MediCitas";
        $mensaje = "<h3>Recupera tu contraseña</h3>
                    <p>Haz clic aquí para restablecer tu contraseña:</p>
                    <a href='$url'>$url</a>";

        enviarEmail($email, $asunto, $mensaje);

        echo "<script>alert('Enlace enviado a tu correo.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Correo no encontrado.'); window.location='recuperar_contrasena.php';</script>";
    }
}
?>