<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['form_data'] = $_POST;
    $idPaciente = $_POST['idPaciente'];
    $idCita = $_POST['idCita'];
    $tipoDocumento = $_POST['tipoDocumento'];
    $descripcion = $_POST['descripcion'];
    $fechaSubida = $_POST['fechaSubida'];
    $idMedico = $_POST['idMedico'];

    if (empty($idPaciente) || empty($idCita) || empty($tipoDocumento) || empty($descripcion) || empty($fechaSubida) || empty($idMedico)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header("Location: ../documentosmedicos.php");
        exit();
    }

    try {
        // Verificar si ya existe un documento con los mismos datos
        $consulta = "SELECT * FROM DocumentosMedicos WHERE idPaciente = ? AND idCita = ? AND tipoDocumento = ? AND descripcion = ? AND fechaSubida = ? AND idMedico = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $idCita, $tipoDocumento, $descripcion, $fechaSubida, $idMedico]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe un documento con este paciente, cita, médico, tipo de documento, descripción y fecha de subida.";
            header("Location: ../documentosmedicos.php");
            exit();
        }

        // Insertar el nuevo documento
        $consulta = "INSERT INTO DocumentosMedicos (idPaciente, idCita, tipoDocumento, descripcion, fechaSubida, idMedico) VALUES (?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $idCita, $tipoDocumento, $descripcion, $fechaSubida, $idMedico]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Documento agregado correctamente.";
            unset($_SESSION['form_data']);
            header("Location: ../documentosmedicos.php");
        } else {
            $_SESSION['error'] = "Hubo un problema al agregar el documento.";
            header("Location: ../documentosmedicos.php");
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }

    header("Location: ../documentosmedicos.php");
    exit();
}
?>