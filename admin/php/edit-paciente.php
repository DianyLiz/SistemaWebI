<?php 
include '../../conexion.php';
session_start();
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusuario = $_POST['idusuario'];
    $idpaciente = $_POST['idpaciente'];
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $sexo = $_POST['sexo'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    if(empty($dni) || empty($nombre) || empty($apellido) || empty($sexo) || empty($fechaNacimiento) || empty($telefono) || empty($direccion)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../pacientes.php');
        exit();
    }

    try {
        $consulta = "SELECT * FROM Usuarios WHERE (dni = ?) AND idUsuario != $idusuario";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni]);

        if($statement->fetch()) {
            $_SESSION['error'] = "El DNI ya está registrado.";
            header('Location: ../pacientes.php');
            exit();
        }

        // Actualizar datos del paciente
        $consulta = "UPDATE Pacientes SET fechaNacimiento = :fechaNacimiento, sexo = :sexo, telefono = :telefono, direccion = :direccion WHERE idPaciente = :idpaciente";
        $statement = $conn->prepare($consulta);
        $statement->execute([$fechaNacimiento, $sexo, $telefono, $direccion, $idpaciente]);

        if($statement->rowCount() > 0) {
            // Actualizar datos del usuario para el paciente
            $consulta = "UPDATE Usuarios SET dni = :dni, nombre = :nombre, apellido = :apellido WHERE idUsuario = :idusuario";
            $statement = $conn->prepare($consulta);
            $statement->execute([$dni, $nombre, $apellido, $idusuario]);

            if($statement->rowCount() > 0) {
                $_SESSION['success'] = "Paciente Nº {$idpaciente} actualizado correctamente.";
                header('Location: ../pacientes.php');
                exit();

            } else {
                $_SESSION['error'] = "Error al actualizar el usuario del paciente Nº {$idpaciente}.";
                header('Location: ../pacientes.php');
                exit();

            }
        } else {
            $_SESSION['error'] = "Error al actualizar los datos del paciente Nº {$idpaciente}.";
            header('Location: ../pacientes.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../medicos.php');
        exit();
    }
}
?>