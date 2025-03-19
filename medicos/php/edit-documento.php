<?php
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idDocumento = filter_input(INPUT_POST, 'idDocumento', FILTER_SANITIZE_NUMBER_INT);
    $idPaciente = filter_input(INPUT_POST, 'idPaciente', FILTER_SANITIZE_NUMBER_INT);
    $idMedico = filter_input(INPUT_POST, 'idMedico', FILTER_SANITIZE_NUMBER_INT);
    $tipoDocumento = filter_input(INPUT_POST, 'tipoDocumento', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $fechaSubida = filter_input(INPUT_POST, 'fechaSubida', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


    if (empty($idDocumento) || empty($idPaciente) || empty($idMedico) || empty($tipoDocumento) || empty($descripcion) || empty($fechaSubida)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../documentosmedicos.php');
        exit();
    }

    try {
        $consulta = "SELECT * FROM DocumentosMedicos WHERE idPaciente = ? AND idMedico = ? AND tipoDocumento = ? AND idDocumento != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $idMedico, $tipoDocumento, $idDocumento]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe un documento con este paciente y médico del mismo tipo.";
            header('Location: ../documentosmedicos.php');
            exit();
        }

        $consulta = "UPDATE DocumentosMedicos SET 
            idPaciente = :idPaciente,
            idMedico = :idMedico,
            tipoDocumento = :tipoDocumento,
            descripcion = :descripcion,
            fechaSubida = :fechaSubida
            WHERE idDocumento = :idDocumento";

        $statement = $conn->prepare($consulta);
        $statement->execute([
            'idPaciente' => $idPaciente,
            'idMedico' => $idMedico,
            'tipoDocumento' => $tipoDocumento,
            'descripcion' => $descripcion,
            'fechaSubida' => $fechaSubida,
            'idDocumento' => $idDocumento
        ]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Documento Nº {$idDocumento} actualizado correctamente.";
        } else {
            $_SESSION['error'] = "No se realizaron cambios en el documento Nº {$idDocumento}. Verifica si los datos son distintos.";
        }

        header('Location: ../documentosmedicos.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../documentosmedicos.php');
        exit();
    }
}
?>
