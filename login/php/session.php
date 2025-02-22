<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    if (empty($usuario) || empty($password)) {
        $_SESSION['error'] = "Complete los campos obligatorios";
        header('Location: ../login.php');
        exit();
    }

    try {
        $consulta = "SELECT * FROM Usuarios WHERE usuario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$usuario]);
        $resultset = $statement->fetch(PDO::FETCH_ASSOC);

        if ($resultset) {
            if (password_verify($password, $resultset['contrasenia'])) {
                $_SESSION['usuario'] = [
                    'usuario' => $usuario,
                    'nombre' => $resultset['nombre'],
                    'apellido' => $resultset['apellido'],
                    'rol' => $resultset['rol']
                ];

                if($resultset['rol'] == 'Administrador') {
                    header('Location: ../../admin/');
                } else if($resultset['rol'] == 'Médico') {       
                    header('Location: ../../medicos/');
                } else if($resultset['rol'] == 'Paciente') {
                    header('Location: ../../pacientes/');
                }
            } else {
                $_SESSION['error'] = "Usuario o contraseña incorrectos.";
                header('Location: ../login.php');
            }
        } else {
            $_SESSION['error'] = "Usuario o contraseña incorrectos.";
            header('Location: ../login.php');
        }
    } catch (PDOException $e) {
        
        $_SESSION['error'] = "Error al iniciar sesión.";
        header('Location: ../login.php');
    }
}
