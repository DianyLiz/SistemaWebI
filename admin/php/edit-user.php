<?php 
include '../../conexion.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $usuario = $_POST['usuario'];
    $idusuario = $_POST['idusuario'];

    if (empty($dni) || empty($nombre) || empty($apellido) || empty($correo) || empty($usuario)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../usuarios.php');
        exit();
    }

    try {

        $consulta = "SELECT * FROM Usuarios WHERE (dni = ? OR correo = ? OR usuario = ?) AND idusuario != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $correo, $usuario, $idusuario]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El DNI, correo o usuario ya están registrados.";
            header('Location: ../usuarios.php');
            exit();
        }
        $consulta = "UPDATE Usuarios SET dni = :dni, nombre = :nombre, apellido = :apellido, correo = :correo, usuario = :usuario WHERE idusuario = :idusuario";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $nombre, $apellido, $correo, $usuario, $idusuario]);

        if($statement->rowCount() > 0) {
            $_SESSION['success'] = "Usuario Nº {$idusuario} actualizado correctamente.";
            header('Location: ../usuarios.php');
            exit();
        } else {
            $_SESSION['error'] = "Error al actualizar el usuario Nº {$idusuario}.";
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