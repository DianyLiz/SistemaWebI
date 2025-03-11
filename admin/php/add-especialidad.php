<?php
include '../../conexion.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['form_data'] = $_POST;
    $nombreEspecialidad = $_POST['especialidad'];
    $descripcion = $_POST['descripcion'];

    if (empty($nombreEspecialidad) || empty($descripcion)) {
        $_SESSION['error'] = 'Complete los campos obligatorios.';
        header('Location: ../especialidades.php');
        exit();
    }

    try {
        $consulta = "SELECT nombreEspecialidad FROM Especialidades WHERE nombreEspecialidad = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$nombreEspecialidad]);

        if ($statement->fetch()) {
            $_SESSION['error'] = 'La especialidad ya existe en el sistema.';
            header('Location: ../especialidades.php');
            exit();
        }

        $consulta = "INSERT INTO Especialidades (nombreEspecialidad, descripcion) VALUES (?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$nombreEspecialidad, $descripcion]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = 'Especialidad agregada correctamente.';
            unset($_SESSION['form_data']);
            header('Location: ../especialidades.php');
            exit();
        } else {
            $_SESSION['error'] = 'No se pudo agregar la especialidad.';
            header('Location: ../especialidades.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../especialidades.php');
        exit();
    }
}
