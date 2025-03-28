<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idExpediente = intval($_POST['idExpediente']);
    $idPaciente = intval($_POST['idPaciente']);
    $fechaCreacion = $_POST['fechaCreacion'];
    $antecedentes = $_POST['antecedentes'];
    $alergias = $_POST['alergias'];
    $medicamentosActuales = $_POST['medicamentosActuales'];
    $enfermedadesCronicas = $_POST['enfermedadesCronicas'];
    $descripcion = $_POST['descripcion'];
    $fechaActualizacion = $_POST['fechaActualizacion'];

    if (empty($idExpediente) || empty($idPaciente) || empty($fechaCreacion) || empty($antecedentes) || empty($alergias) || empty($medicamentosActuales) || empty($enfermedadesCronicas) || empty($descripcion) || empty($fechaActualizacion)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header("Location: ../expedientesmedicos.php");
        exit();
    }

    try {
        $consulta = "UPDATE ExpedienteMedico SET idPaciente = ?, FechaCreacion = ?, Antecedentes = ?, Alergias = ?, MedicamentosActuales = ?, EnfermedadesCronicas = ?, Descripcion = ?, FechaActualizacion = ? WHERE IdExpediente = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $fechaCreacion, $antecedentes, $alergias, $medicamentosActuales, $enfermedadesCronicas, $descripcion, $fechaActualizacion, $idExpediente]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Expediente actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Hubo un problema al actualizar el expediente.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }

    header("Location: ../expedientesmedicos.php");
    exit();
} else {
    header("Location: ../expedientesmedicos.php");
    exit();
}
