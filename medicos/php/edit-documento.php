<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idDocumento = $_POST['idDocumento'];
    $idCita = $_POST['idCita'];
    $tipoDocumento = $_POST['tipoDocumento'];
    $descripcion = $_POST['descripcion'];
    $fechaSubida = date("Y-m-d");

    if (empty($idDocumento) || empty($idCita) || empty($tipoDocumento) || empty($descripcion) || empty($fechaSubida)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header("Location: ../documentosmedicos.php");
        exit();
    }

    try {
        // Verificar si ya existe un documento con los mismos datos
        $consulta = "SELECT * FROM DocumentosMedicos WHERE idCita = ? AND tipoDocumento = ? AND descripcion = ? AND idDocumento != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idCita, $tipoDocumento, $descripcion, $idDocumento]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe un documento con los mismos datos de Cita y tipo de documento.";
            header("Location: ../documentosmedicos.php");
            exit();
        }

        $consulta = "UPDATE DocumentosMedicos SET idCita = ?, tipoDocumento = ?, descripcion = ?, fechaSubida = ? WHERE idDocumento = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idCita, $tipoDocumento, $descripcion, $fechaSubida, $idDocumento]);

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