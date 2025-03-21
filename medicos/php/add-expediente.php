<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['form_data'] = $_POST;

    $idPaciente = $_POST['idPaciente'];
    $fechaCreacion = $_POST['fechaCreacion'];
    $antecedentes = $_POST['antecedentes'];
    $alergias = $_POST['alergias'];
    $medicamentosActuales = $_POST['medicamentosActuales'];
    $enfermedadesCronicas = $_POST['enfermedadesCronicas'];
    $descripcion = $_POST['descripcion'];
    $fechaActualizacion = $_POST['fechaActualizacion'];

    if (empty($idPaciente) || empty($fechaCreacion) || empty($antecedentes) || empty($alergias) || empty($medicamentosActuales) || empty($enfermedadesCronicas) || empty($descripcion) || empty($fechaActualizacion)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header("Location: ../expedientesmedicos.php");
        exit();
    }

    try {
        $consulta = "INSERT INTO ExpedienteMedico (idPaciente, FechaCreacion, Antecedentes, Alergias, MedicamentosActuales, EnfermedadesCronicas, Descripcion, FechaActualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $fechaCreacion, $antecedentes, $alergias, $medicamentosActuales, $enfermedadesCronicas, $descripcion, $fechaActualizacion]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Expediente agregado correctamente.";
            unset($_SESSION['form_data']);
            header("Location: ../expedientesmedicos.php");
        } else {
            $_SESSION['error'] = "Hubo un problema al agregar el expediente.";
            header("Location: ../expedientesmedicos.php");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header("Location: ../expedientesmedicos.php");
    }
} else {
    $_SESSION['error'] = "Error en la solicitud.";
    header("Location: ../expedientesmedicos.php");
    exit();
}
?>