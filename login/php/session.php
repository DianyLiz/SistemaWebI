<?php 
    include '../../conexion.php';

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $consulta = "SELECT 1 FROM Usuarios WHERE nombre = ? AND contraseña = ?";
        $stmt = $conn->prepare($consulta);
        $stmt->execute([$usuario, $password]);
        $user = $stmt->fetch();

        session_start();
        if ($user) {
            $_SESSION['usuario'] = $usuario;
            header('Location: ../../admin/index.php');
        } else {
            $_SESSION['error'] = "Usuario o contraseña incorrectos.";
            header('Location: ../login.php');
        }
    }
?>
