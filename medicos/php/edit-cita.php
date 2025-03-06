<?php 
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idCita = filter_input(INPUT_POST, 'idCita', FILTER_SANITIZE_NUMBER_INT);
    $idPaciente = filter_input(INPUT_POST, 'idPaciente', FILTER_SANITIZE_NUMBER_INT);
    $idMedico = filter_input(INPUT_POST, 'idMedico', FILTER_SANITIZE_NUMBER_INT);
    $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING);
    $hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

    if (empty($idPaciente) || empty($idMedico) || empty($fecha) || empty($hora) || empty($motivo) || empty($estado)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../ListadeCitas.php');
        exit();
    }
}

    try {
        $consulta = "SELECT * FROM Citas WHERE idPaciente = ? AND idMedico = ? AND fecha = ? AND hora = ? AND idCita != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $idMedico, $fecha, $hora, $idCita]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe una cita con este paciente y médico en la misma fecha y hora.";
            header('Location: ../ListadeCitas.php');
            exit();
        }

        $consulta = "UPDATE Citas SET idPaciente = :idPaciente, idMedico = :idMedico, fecha = :fecha, hora = :hora, motivo = :motivo, estado = :estado WHERE idCita = :idCita";
        $statement = $conn->prepare($consulta);
        $statement->execute([
            'idPaciente' => $idPaciente,
            'idMedico' => $idMedico,
            'fecha' => $fecha,
            'hora' => $hora,
            'motivo' => $motivo,
            'estado' => $estado,
            'idCita' => $idCita
        ]);

        if ($statement->rowCount() > 0) {
            //$consulta = "UPDATE Usuarios SET nombre = :nombre, apellido = :apellido WHERE idUsuario = :idUsuario";
            $_SESSION['success'] = "Cita Nº {$idCita} actualizada correctamente.";
            header('Location: ../ListadeCitas.php');
            exit();
        } else {
            $_SESSION['error'] = "Error al actualizar la cita Nº {$idCita} o no hubo cambios.";
            header('Location: ../ListadeCitas.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../ListadeCitas.php');
        exit();
    }

?>