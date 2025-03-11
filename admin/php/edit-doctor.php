<?php 
include '../../conexion.php';
session_start();
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusuario = $_POST['idusuario'];
    $idmedico = $_POST['idmedico'];
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $idespecialidad = $_POST['idespecialidad'];
    $licenciaMedica = $_POST['licenciaMedica'];
    $aniosExperiencia = $_POST['aniosExperiencia'];

    if(empty($dni) || empty($nombre) || empty($apellido) || empty($idespecialidad) || empty($licenciaMedica) || empty($aniosExperiencia)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../medicos.php');
        exit();
    }

    try {
        $consulta = "SELECT * FROM Usuarios WHERE (dni = ?) AND idUsuario != $idusuario";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni]);

        if($statement->fetch()) {
            $_SESSION['error'] = "El DNI ya está registrado.";
            header('Location: ../medicos.php');
            exit();
        }
        // Actualizar datos del médico
        $consulta = "UPDATE Medicos SET idEspecialidad = :idespecialidad, numeroLicenciaMedica = :licenciaMedica, anosExperiencia = :aniosExperiencia WHERE idMedico = :idmedico";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idespecialidad, $licenciaMedica, $aniosExperiencia, $idmedico]);

        if($statement->rowCount() > 0) {
            // Actualizar datos del usuario para el médico
            $consulta = "UPDATE Usuarios SET dni = :dni, nombre = :nombre, apellido = :apellido WHERE idUsuario = :idusuario";
            $statement = $conn->prepare($consulta);
            $statement->execute([$dni, $nombre, $apellido, $idusuario]);

            if($statement->rowCount() > 0) {
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
?>