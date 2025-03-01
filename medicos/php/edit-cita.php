<?php 
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idCita = $_POST['idCita'];
    $idPaciente = $_POST['idPaciente'];
    $idMedico = $_POST['idMedico'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo'];
    $estado = $_POST['estado'];

    if (empty($idPaciente) || empty($idMedico) || empty($fecha) || empty($hora) || empty($motivo) || empty($estado)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../ListadeCitas.php');
        exit();
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
}
?>
