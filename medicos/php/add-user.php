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
    $rol = $_POST['tipoUsuario'];
    $rolPaginaActual = $_POST['rol-tipoUsuario'];

    function Redirigir($rolPaginaActual)
    {
        if ($rolPaginaActual == 'Médico') {
            header('Location: ../medicos.php');
        } else if ($rolPaginaActual == 'Paciente') {
            header('Location: ../pacientes.php');
        } else {
            header('Location: ../usuarios.php');
        }
    }

    if (empty($dni) || empty($nombre) || empty($apellido) || empty($correo) || empty($usuario) || empty($password) || empty($confirmPassword) || empty($rol)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        Redirigir($rolPaginaActual);
        exit();
    }

    if ($rol == '1') {
        $rol = 'Paciente';
        $fechaNacimiento = $_POST['fechaNacimiento'];
        $sexo = $_POST['sexo'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];

        if (empty($fechaNacimiento) || empty($sexo) || empty($telefono) || empty($direccion)) {
            $_SESSION['error'] = "Complete los campos obligatorios";
            Redirigir($rolPaginaActual);
            exit();
        }
    } else if ($rol == '2') {
        $idespecialidad = $_POST['idespecialidad'];
        $licenciaMedica = $_POST['licenciaMedica'];
        $aniosExperiencia = $_POST['aniosExperiencia'];
        $rol = 'Médico';

        if ($idespecialidad == 0 || empty($licenciaMedica) || empty($aniosExperiencia)) {
            $_SESSION['error'] = "Complete los campos obligatorios";
            Redirigir($rolPaginaActual);
            exit();
        }
    } else if ($rol == '3') {
        $idespecialidad = null;
        $licenciaMedica = null;
        $aniosExperiencia = null;
        $fechaNacimiento = null;
        $sexo = null;
        $telefono = null;
        $rol = 'Administrador';
    } else {
        $_SESSION['error'] = "Seleccione un rol.";
        Redirigir($rolPaginaActual);
        exit();
    }

    if ($password != $confirmPassword) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
        Redirigir($rolPaginaActual);
        exit();
    }
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {

        $consulta = "SELECT * FROM Usuarios WHERE dni = ? OR correo = ? OR usuario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $correo, $usuario]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El DNI, correo o usuario ya están registrados.";
            Redirigir($rolPaginaActual);
            exit();
        }

        $consulta = "SELECT * FROM Medicos WHERE numerolicenciaMedica = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$licenciaMedica]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El número de licencia médica ya está registrado.";
            Redirigir($rolPaginaActual);
            exit();
        }

        $consulta = "INSERT INTO Usuarios (dni, nombre, apellido, usuario, correo, contrasenia, rol) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $nombre, $apellido, $usuario, $correo, $passwordHash, $rol]);

        if ($statement->rowCount() > 0) {
            if ($rol == 'Médico') {
                $idusuario = $conn->lastInsertId();
                $medico = "INSERT INTO Medicos (idUsuario, idEspecialidad, numerolicenciaMedica, anosExperiencia) VALUES (?,?,?,?)";
                $statement = $conn->prepare($medico);
                $statement->execute([$idusuario, $idespecialidad, $licenciaMedica, $aniosExperiencia]);
                
            } else if ($rol == 'Paciente') {
                $idusuario = $conn->lastInsertId();
                $paciente = "INSERT INTO Pacientes (idUsuario, fechaNacimiento, sexo, telefono, direccion) VALUES (?,?,?,?,?)";
                $statement = $conn->prepare($paciente);
                $statement->execute([$idusuario, $fechaNacimiento, $sexo, $telefono, $direccion]);
            }

            $_SESSION['success'] = "Usuario registrado correctamente.";
            unset($_SESSION['form_data']);
            Redirigir($rolPaginaActual);
            exit();
        } else {
            $_SESSION['error'] = "Error al registrar el usuario.";
            Redirigir($rolPaginaActual);
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        Redirigir($rolPaginaActual);
        exit();
    }
}