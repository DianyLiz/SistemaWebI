<?php
include '../../conexion.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusuario = $_POST['idusuario'];
    $idmedico = $_POST['idmedico'];
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $idespecialidad = $_POST['idespecialidad'];
    $licenciaMedica = $_POST['licenciaMedica'];
    $aniosExperiencia = $_POST['aniosExperiencia'];
    $telefonoMedico = $_POST['telefonoMedico'];

    if (empty($dni) || empty($nombre) || empty($apellido) || empty($idespecialidad) || empty($licenciaMedica) || empty($aniosExperiencia) || empty($telefonoMedico)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../medicos.php');
        exit();
    }

    try {
        // Verificar que el DNI no se repita
        $consulta = "SELECT * FROM Usuarios WHERE (dni = ?) AND idUsuario != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $idusuario]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El DNI ya está registrado.";
            header('Location: ../medicos.php');
            exit();
        }

        // Verificar que el numero de licencia o el telefono no se repitan
        $consulta = "SELECT * FROM Medicos WHERE (numeroLicenciaMedica = ? OR telefono = ?) AND idMedico != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$licenciaMedica, $telefonoMedico, $idmedico]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El número de licencia médica o el número telefónico ya está registrado.";
            header('Location: ../medicos.php');
            exit();
        }

        // Actualizar datos del médico
        $consulta = "UPDATE Medicos SET idEspecialidad = :idespecialidad, numeroLicenciaMedica = :licenciaMedica, anosExperiencia = :aniosExperiencia, telefono = :telefonoMedico WHERE idMedico = :idmedico";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idespecialidad, $licenciaMedica, $aniosExperiencia, $telefonoMedico, $idmedico]);

        if ($statement->rowCount() > 0) {
            // Actualizar datos del usuario para el médico
            $consulta = "UPDATE Usuarios SET dni = :dni, nombre = :nombre, apellido = :apellido WHERE idUsuario = :idusuario";
            $statement = $conn->prepare($consulta);
            $statement->execute([$dni, $nombre, $apellido, $idusuario]);

            if ($statement->rowCount() > 0) {
                $_SESSION['success'] = "Médico Nº {$idmedico} actualizado correctamente.";
                header('Location: ../medicos.php');
                exit();
            } else {
                $_SESSION['error'] = "Error al actualizar el usuario del médico Nº {$idmedico}.";
                header('Location: ../medicos.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Error al actualizar los datos del médico Nº {$idmedico}.";
            header('Location: ../medicos.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../medicos.php');
        exit();
    }
}
