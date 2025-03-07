<?php 
include '../../conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idCita = filter_input(INPUT_POST, 'idCita', FILTER_SANITIZE_NUMBER_INT);
    $idPaciente = filter_input(INPUT_POST, 'idPaciente', FILTER_SANITIZE_NUMBER_INT);
    $idMedico = filter_input(INPUT_POST, 'idMedico', FILTER_SANITIZE_NUMBER_INT);
    $hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

    if (empty($idPaciente) || empty($idMedico) || empty($hora) || empty($motivo) || empty($estado)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../ListadeCitas.php');
        exit();
    }

    $hora = date("H:i:s", strtotime($hora));

    try {
        $consulta = "SELECT * FROM Citas WHERE idPaciente = ? AND idMedico = ? AND hora = ? AND idCita != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $idMedico, $hora, $idCita]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe una cita con este paciente y médico a la misma hora.";
            header('Location: ../ListadeCitas.php');
            exit();
        }

        $consulta = "UPDATE Citas SET idPaciente = :idPaciente, idMedico = :idMedico, hora = :hora, motivo = :motivo, estado = :estado WHERE idCita = :idCita";
        $statement = $conn->prepare($consulta);
        $statement->execute([
            'idPaciente' => $idPaciente,
            'idMedico' => $idMedico,
            'hora' => $hora,
            'motivo' => $motivo,
            'estado' => $estado,
            'idCita' => $idCita
        ]);

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Cita Nº {$idCita} actualizada correctamente.";
        } else {
            $_SESSION['error'] = "No se realizaron cambios en la cita Nº {$idCita}. Verifica si los datos son distintos.";
        }

        header('Location: ../ListadeCitas.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../ListadeCitas.php');
        exit();
    }
}
?>
