<?php 
include '../../conexion.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['form_data'] = $_POST;
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $usuario = $_POST['usuario'];
    $idusuario = $_POST['idusuario'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $rol = $_POST['tipoUsuario'];

    if (empty($dni) || empty($nombre) || empty($apellido) || empty($correo) || empty($usuario) || empty($password) || empty($confirmPassword) || empty($rol)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../usuarios.php');
        exit();
    }

    if($rol == '1'){
        $rol = 'Paciente';
    } else if($rol == '2'){
        $rol = 'Médico';
    } else if($rol == '3'){
        $rol = 'Administrador';
    } else {
        $_SESSION['error'] = "Seleccione un rol.";
        header('Location: ../usuarios.php');
        exit();
    }

    if ($password != $confirmPassword) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header('Location: ../usuarios.php');
        exit();
    }
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {

        $consulta = "SELECT * FROM Usuarios WHERE dni = ? OR correo = ? OR usuario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $correo, $usuario]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El DNI, correo o usuario ya están registrados.";
            header('Location: ../usuarios.php');
            exit();
        }
        $consulta = "INSERT INTO Usuarios (dni, nombre, apellido, usuario, correo, contrasenia, rol) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $nombre, $apellido, $usuario, $correo, $passwordHash, $rol]);

        if($statement->rowCount() > 0) {
            $_SESSION['success'] = "Usuario registrado correctamente.";
            unset($_SESSION['form_data']);
            header('Location: ../usuarios.php');
            exit();
        } else {
            $_SESSION['error'] = "Error al registrar el usuario.";
            header('Location: ../usuarios.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../usuarios.php');
        exit();
    }
}
?>