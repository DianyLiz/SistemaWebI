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
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $tipoUsuario = $_POST['tipoUsuario'];

    if (empty($dni) || empty($nombre) || empty($apellido) || empty($correo) || empty($usuario) || empty($password) || empty($confirmPassword) || empty($tipoUsuario)) {
        $_SESSION['error'] = "Complete los campos obligatorios";
        header('Location: ../registrarse.php');
        exit();
    }

    if ($tipoUsuario == 2) {
        $idespecialidad = $_POST['idespecialidad'];
        $licenciaMedica = $_POST['licenciaMedica'];
        $aniosExperiencia = $_POST['aniosExperiencia'];
        $tipoUsuario = 'Médico';

        if ($idespecialidad == 0 || empty($licenciaMedica) || empty($aniosExperiencia)) {
            $_SESSION['error'] = "Complete los campos obligatorios";
            header('Location: ../registrarse.php');
            exit();
        }
    } else if ($tipoUsuario == 1) {
        $fechaNacimiento = $_POST['fechaNacimiento'];
        $sexo = $_POST['sexo'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $tipoUsuario = 'Paciente';

        if (empty($fechaNacimiento) || empty($sexo) || empty($telefono) || empty($direccion)) {
            $_SESSION['error'] = "Complete los campos obligatorios";
            header('Location: ../registrarse.php');
            exit();
        }
    } else {
        $idespecialidad = null;
        $licenciaMedica = null;
        $aniosExperiencia = null;
        $fechaNacimiento = null;
        $sexo = null;
        $telefono = null;
        $tipoUsuario = 'Administrador';
    }

    if ($password != $confirmPassword) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        header('Location: ../registrarse.php');
        exit();
    }
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $consulta = "SELECT * FROM Usuarios WHERE dni = ? OR correo = ? OR usuario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $correo, $usuario]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El DNI, correo o usuario ya están registrados.";
            header('Location: ../registrarse.php');
            exit();
        }
        $consulta = "INSERT INTO Usuarios (dni, nombre, apellido, correo, usuario, contrasenia, rol) VALUES (?,?,?,?,?,?,?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $nombre, $apellido, $correo, $usuario, $passwordHash, $tipoUsuario]);

        if ($statement->rowCount() > 0) {
            if ($tipoUsuario == 'Médico') {
                $idUsuario = $conn->lastInsertId();
                $medico = "INSERT INTO Medicos (idUsuario, idEspecialidad, numerolicenciaMedica, anosExperiencia) VALUES (?,?,?,?)";
                $statement = $conn->prepare($medico);
                $statement->execute([$idUsuario, $idespecialidad, $licenciaMedica, $aniosExperiencia]);
            } else if ($tipoUsuario == 'Paciente') {
                $idUsuario = $conn->lastInsertId();
                $paciente = "INSERT INTO Pacientes (idUsuario, fechaNacimiento, sexo, telefono, direccion) VALUES (?,?,?,?,?)";
                $statement = $conn->prepare($paciente);
                $statement->execute([$idUsuario, $fechaNacimiento, $sexo, $telefono, $direccion]);
            }

            $_SESSION['success'] = "Usuario registrado correctamente.";
            unset($_SESSION['form_data']);
            header('Location: ../registrarse.php');
        } else {
            $_SESSION['error'] = "Error al registrar usuario.";
            header('Location: ../registrarse.php');
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../registrarse.php');
        exit();
    }
}
