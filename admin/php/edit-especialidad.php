<?php
include '../../conexion.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idespecialidad = $_POST['idespecialidad'];
    $nombreEspecialidad = $_POST['especialidad'];
    $descripcion = $_POST['descripcion'];

    if (empty($nombreEspecialidad) || empty($descripcion)) {
        $_SESSION['error'] = 'Complete los campos obligatorios.';
        header('Location: ../especialidades.php');
        exit();
    }

    try {
        $consulta = "SELECT nombreEspecialidad FROM Especialidades WHERE nombreEspecialidad = ? AND idespecialidad != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$nombreEspecialidad, $idespecialidad]);

        if ($statement->fetch()) {
            $_SESSION['error'] = 'La especialidad ya existe en el sistema.';
            header('Location: ../especialidades.php');
            exit();
        }

        $consulta = "UPDATE Especialidades SET nombreEspecialidad = ?, descripcion = ? WHERE idespecialidad = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$nombreEspecialidad, $descripcion, $idespecialidad]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Especialidad Nº {$idespecialidad} correctamente.";
            header('Location: ../especialidades.php');
            exit();
        } else {
            $_SESSION['error'] = "Error al actualizar la especialidad Nº {$idespecialidad}.";
            header('Location: ../especialidades.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../especialidades.php');
        exit();
    }
}
