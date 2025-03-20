<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idDocumento = intval($_POST['idDocumento']);
    $idPaciente = intval($_POST['idPaciente']);
    $idCita = intval($_POST['idCita']);
    $tipoDocumento = $_POST['tipoDocumento'];
    $descripcion = $_POST['descripcion'];
    $fechaSubida = $_POST['fechaSubida'];
    $idMedico = intval($_POST['idMedico']);

    if (empty($idPaciente) || empty($idCita) || empty($tipoDocumento) || empty($descripcion) || empty($fechaSubida) || empty($idMedico)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header("Location: ../documentosmedicos.php");
        exit();
    }

    try {
        $consulta = "UPDATE DocumentosMedicos SET idPaciente = ?, idCita = ?, tipoDocumento = ?, descripcion = ?, fechaSubida = ?, idMedico = ? WHERE idDocumento = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $idCita, $tipoDocumento, $descripcion, $fechaSubida, $idMedico, $idDocumento]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Documento actualizado correctamente.";
            unset($_SESSION['form_data']);
            header("Location: ../documentosmedicos.php");
        } else {
            $_SESSION['error'] = "Hubo un problema al actualizar el documento.";
            header("Location: ../documentosmedicos.php");
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header("Location: ../documentosmedicos.php");
    }
}
?>