<?php
include '../../conexion.php';
session_start();

function validarHoraEnRango($hora, $horaInicio, $horaFin)
{
    $hora = strtotime($hora);
    $horaInicio = strtotime($horaInicio);
    $horaFin = strtotime($horaFin);

    return $hora >= $horaInicio && $hora <= $horaFin;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idCita = $_POST['idCita'];
    $idPaciente = $_POST['idPaciente'];
    $paciente = $_POST['paciente'];
    $idMedico = $_POST['idMedico'];
    $medico = $_POST['medico'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo'];
    $estado = $_POST['estado'];
    $idhorario = $_POST['idHorario'];

    if (empty($idPaciente) || empty($idMedico) || empty($hora) || empty($motivo) || empty($estado)) {
        $_SESSION['error'] = "Complete los campos obligatorios.";
        header('Location: ../ListadeCitas.php');
        exit();
    }

    if ($paciente == "No encontrado" || $medico == "No encontrado") {
        $_SESSION['error'] = "Paciente o Médico no encontrado.";
        header('Location: ../ListadeCitas.php');
        exit();
    }

    try {
        // Obtener el horario actual de la cita antes de modificarla
        $consulta = "SELECT idHorario FROM Citas WHERE idCita = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idCita]);
        $citaAnterior = $statement->fetch();

        if (!$citaAnterior) {
            $_SESSION['error'] = "La cita no existe.";
            header('Location: ../ListadeCitas.php');
            exit();
        }

        $idHorarioAnterior = $citaAnterior['idHorario'];

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
        $consulta = "SELECT * FROM Citas WHERE idPaciente = ? AND idMedico = ? AND hora = ? AND idCita != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idPaciente, $idMedico, $hora, $idCita]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "Ya existe una cita con este paciente y médico en la misma fecha y hora.";
            header('Location: ../ListadeCitas.php');
            exit();
        }

        $consulta = "UPDATE Citas SET idPaciente = :idPaciente, idMedico = :idMedico, hora = :hora, motivo = :motivo, estado = :estado, idHorario = :idHorario WHERE idCita = :idCita";
        $statement = $conn->prepare($consulta);
        $statement->execute([
            'idPaciente' => $idPaciente,
            'idMedico' => $idMedico,
            'hora' => $hora,
            'motivo' => $motivo,
            'estado' => $estado,
            'idCita' => $idCita,
            'idHorario' => $idhorario
        ]);

        $idHorarioActual = $horario['idHorario'];

        if ($statement->rowCount() > 0) {
            $_SESSION['success'] = "Cita Nº {$idCita} actualizada correctamente.";
            
            // Solo modificar cupos si el horario cambió
            if ($idHorarioAnterior != $idhorario) {
                $consulta = "UPDATE HorariosMedicos SET cupos = cupos + 1 WHERE idHorario = ?";
                $statement = $conn->prepare($consulta);
                $statement->execute([$idHorarioAnterior]);

                $consulta = "UPDATE HorariosMedicos SET cupos = cupos - 1 WHERE idHorario = ? AND cupos > 0";
                $statement = $conn->prepare($consulta);
                $statement->execute([$idhorario]);
            }

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
