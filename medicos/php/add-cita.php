<?php
session_start();
include '../../conexion.php';

function validarHoraEnRango($hora, $horaInicio, $horaFin) {
    $hora = strtotime($hora);
    $horaInicio = strtotime($horaInicio);
    $horaFin = strtotime($horaFin);

    return $hora >= $horaInicio && $hora <= $horaFin;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idpaciente = $_POST['idpaciente']; 
    $paciente = $_POST['paciente'];
    $idmedico = $_POST['idmedico']; 
    $medico = $_POST['medico'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo']; 
    $estado = $_POST['estado'];
    $idhorario = $_POST['idHorario'];

    if (empty($idpaciente) || empty($idmedico) || empty($hora) || empty($motivo) || empty($estado) || empty($idhorario)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../ListadeCitas.php');
        exit();
    }

    if($paciente == "No encontrado" || $medico == "No encontrado") {
        $_SESSION['error'] = "Paciente o Médico no encontrado.";
        header('Location: ../ListadeCitas.php');
        exit();
    }

    try {
        // Verificar que la hora de la cita esté dentro del rango del horario seleccionado
        $consulta = "SELECT horaInicio, horaFin FROM HorariosMedicos WHERE idHorario = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idhorario]);
        $horario = $statement->fetch();

        if (!validarHoraEnRango($hora, $horario['horaInicio'], $horario['horaFin'])) {
            $_SESSION['error'] = "La hora de la cita no está dentro del rango del horario seleccionado.";
            header('Location: ../ListadeCitas.php');
            exit();
        }

        // Verificar que no exista otra cita con el mismo paciente, médico, fecha y hora
        $consulta = "SELECT * FROM Citas WHERE idPaciente = ? AND idMedico = ? AND idHorario = ? AND hora = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idpaciente, $idmedico, $idhorario, $hora]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe una cita con este paciente y médico en la misma fecha y hora.";
            header('Location: ../ListadeCitas.php');
            exit();
        }

        $consulta = "INSERT INTO Citas (idPaciente, idMedico, hora, motivo, estado, idHorario) VALUES (?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idpaciente, $idmedico, $hora, $motivo, $estado, $idhorario]);

        if($statement->rowCount() > 0) {
            $_SESSION['success'] = "Cita agregada correctamente.";
            $consulta = "UPDATE HorariosMedicos SET cupos = cupos - 1 WHERE idHorario = ?";
            $statement = $conn->prepare($consulta);
            $statement->execute([$idhorario]);

        } else {
            $_SESSION['error'] = "Ocurrió un error al agregar la cita.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    }

    header("Location: ../ListadeCitas.php");
    exit();
}
?>
