<?php 
    include '../../conexion.php';

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $consulta = "SELECT nombre, apellido FROM Usuarios WHERE usuario = ? AND contraseña = ?";
        $stmt = $conn->prepare($consulta);
        $stmt->execute([$usuario, $password]);
        $user = $stmt->fetch();


    session_start();
if ($user) {
    $_SESSION['usuario'] = [
        'usuario' => $usuario,
        'nombre' => $user['nombre'],
        'apellido' => $user['apellido']
    ];
    header('Location: ../../medicos/header.php');
    exit();
} else {
    $_SESSION['error'] = "Usuario o contraseña incorrectos.";
    header('Location: ../login.php');
    exit();
}


    }
?>
