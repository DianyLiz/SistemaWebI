<?php
session_start();
include '../../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente = filter_input(INPUT_POST, 'paciente', FILTER_SANITIZE_NUMBER_INT);
    $cita = filter_input(INPUT_POST, 'cita', FILTER_SANITIZE_NUMBER_INT);
    $tipoDocumento = filter_input(INPUT_POST, 'tipoDocumento', FILTER_SANITIZE_STRING);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $medico = filter_input(INPUT_POST, 'medico', FILTER_SANITIZE_NUMBER_INT);
    $fechaSubida = date('Y-m-d H:i:s'); 

    if (empty($paciente) || empty($cita) || empty($tipoDocumento) || empty($medico)) {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../documentosmedicos.php");
        exit;
    }

    // Manejo del archivo subido
    if (!isset($_FILES['documento']) || $_FILES['documento']['error'] != UPLOAD_ERR_OK) {
        $_SESSION['mensaje'] = "Error al subir el documento.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../documentosmedicos.php");
        exit;
    }

    $directorio = "../../uploads/";
    $nombreArchivo = basename($_FILES["documento"]["name"]);
    $rutaArchivo = $directorio . $nombreArchivo;

    if (!move_uploaded_file($_FILES["documento"]["tmp_name"], $rutaArchivo)) {
        $_SESSION['mensaje'] = "No se pudo guardar el archivo.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../documentosmedicos.php");
        exit;
    }

    try {

        $sql_verificar = "SELECT * FROM DocumentosMedicos 
                          WHERE idPaciente = ? AND idCita = ? AND tipoDocumento = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->execute([$paciente, $cita, $tipoDocumento]);

        if ($stmt_verificar->fetch()) {
            $_SESSION['mensaje'] = "Este documento ya está registrado para esta cita.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../documentosmedicos.php");
            exit;
        }


        $sql = "INSERT INTO DocumentosMedicos (idPaciente, idCita, tipoDocumento, urlDocumento, descripcion, fechaSubida, idMedico) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$paciente, $cita, $tipoDocumento, $rutaArchivo, $descripcion, $fechaSubida, $medico]);

        $_SESSION['mensaje'] = "Documento agregado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error al agregar el documento: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: ../documentosmedicos.php");
    exit;
}
?>
