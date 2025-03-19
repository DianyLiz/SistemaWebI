<?php
session_start();
include '../../conexion.php';

// Verificar conexión a la base de datos
if (!$conn) {
    die("Error de conexión a la base de datos: " . print_r($conn->errorInfo(), true));
}

// Verificar si el método de la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entradas del formulario
    $paciente = filter_input(INPUT_POST, 'paciente', FILTER_SANITIZE_NUMBER_INT);
    $cita = filter_input(INPUT_POST, 'cita', FILTER_SANITIZE_NUMBER_INT);
    $tipoDocumento = filter_input(INPUT_POST, 'tipoDocumento', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $medico = filter_input(INPUT_POST, 'medico', FILTER_SANITIZE_NUMBER_INT);
    $fechaSubida = date('Y-m-d H:i:s');

    // Verificar que los datos requeridos no estén vacíos
    if (empty($paciente) || empty($cita) || empty($tipoDocumento) || empty($medico)) {
        $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../documentosmedicos.php");
        exit;
    }

    // Verificar si el archivo se subió correctamente
    if (!isset($_FILES['documento']) || $_FILES['documento']['error'] != UPLOAD_ERR_OK) {
        $_SESSION['mensaje'] = "Error al subir el documento.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../documentosmedicos.php");
        exit;
    }

    // Directorio de almacenamiento
    $directorio = "../../uploads/";
    
    // Verificar que el directorio exista y tenga permisos de escritura
    if (!is_dir($directorio) || !is_writable($directorio)) {
        die("Error: El directorio de almacenamiento no existe o no tiene permisos de escritura.");
    }

    $nombreArchivo = basename($_FILES["documento"]["name"]);
    $rutaArchivo = $directorio . $nombreArchivo;

    // Mover archivo al directorio de almacenamiento
    if (!move_uploaded_file($_FILES["documento"]["tmp_name"], $rutaArchivo)) {
        die("Error al mover el archivo. Código de error: " . $_FILES["documento"]["error"]);
    }

    try {
        // Verificar si ya existe un documento con los mismos datos
        $sql_verificar = "SELECT * FROM DocumentosMedicos WHERE idPaciente = ? AND idCita = ? AND tipoDocumento = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->execute([$paciente, $cita, $tipoDocumento]);

        if ($stmt_verificar->fetch()) {
            $_SESSION['mensaje'] = "Este documento ya está registrado para esta cita.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../documentosmedicos.php");
            exit;
        }

        // Insertar el documento en la base de datos
        $sql = "INSERT INTO DocumentosMedicos (idPaciente, idCita, tipoDocumento, descripcion, fechaSubida, idMedico) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute([$paciente, $cita, $tipoDocumento, $rutaArchivo, $descripcion, $fechaSubida, $medico])) {
            die("Error en la ejecución de la consulta: " . print_r($stmt->errorInfo(), true));
        }

        $_SESSION['mensaje'] = "Documento agregado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (PDOException $e) {
        die("Error al agregar el documento: " . $e->getMessage());
    }

    // Redirigir de vuelta a la página de documentos médicos
    header("Location: ../documentosmedicos.php");
    exit;
}
?>
