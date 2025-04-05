<?php
include '../../conexion.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusuario = $_POST['idusuario'];
    $idpaciente = $_POST['idpaciente'];
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $sexo = $_POST['sexo'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $idTutor = $_POST['idTutor'];

    if ($idTutor != null) {
        $nombreTutor = $_POST['nombreTutor'];
        $dniTutor = $_POST['dniTutor'];

        if (empty($nombreTutor) || empty($dniTutor) || empty($telefono)) {
            $_SESSION['error'] = "Complete los campos obligatorios.";
            header('Location: ../pacientes.php');
            exit();
        }
    } else {
        if (empty($dni) || empty($nombre) || empty($apellido) || empty($sexo) || empty($fechaNacimiento) || empty($telefono) || empty($direccion)) {
            $_SESSION['error'] = "Complete los campos obligatorios.";
            header('Location: ../pacientes.php');
            exit();
        }
    }

    try {
        $consulta = "SELECT * FROM Usuarios WHERE (dni = ?) AND idUsuario != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dni, $idusuario]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El DNI ya está registrado.";
            header('Location: ../pacientes.php');
            exit();
        }

        // Obtener el idResponsable actual del paciente
        $consulta = "SELECT idResponsable FROM Pacientes WHERE idPaciente = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idpaciente]);
        $resultado = $statement->fetch(PDO::FETCH_ASSOC);
        $idResponsableActual = $resultado['idResponsable'];

        // Si el idTutor ha cambiado y no es nulo, actualizar idResponsable
        if ($idTutor != null && $idTutor != $idResponsableActual) {
            $consulta = "UPDATE Pacientes SET idResponsable = ? WHERE idPaciente = ?";
            $statement = $conn->prepare($consulta);
            $statement->execute([$idTutor, $idpaciente]);
        }

        // Verificar si el DNI ya existe en otro tutor
        $consulta = "SELECT * FROM Responsables WHERE dni = ? AND idResponsable != ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$dniTutor, $idTutor]);

        if ($statement->fetch()) {
            $_SESSION['error'] = "El DNI ya está registrado en otro tutor.";
            header('Location: ../pacientes.php');
            exit();
        }

        // Obtener los datos actuales del tutor desde la base de datos
        $consulta = "SELECT nombre, dni, telefono FROM Responsables WHERE idResponsable = ?";
        $statement = $conn->prepare($consulta);
        $statement->execute([$idTutor]);
        $tutorActual = $statement->fetch(PDO::FETCH_ASSOC);

        if ($tutorActual) {
            // Verificar si algún campo ha cambiado
            $nombreCambiado = ($tutorActual['nombre'] !== $nombreTutor);
            $dniCambiado = ($tutorActual['dni'] !== $dniTutor);
            $telefonoCambiado = ($tutorActual['telefono'] !== $telefono);

            // Si hay cambios, actualizar solo los campos modificados
            if ($nombreCambiado || $dniCambiado || $telefonoCambiado) {
                $updateFields = [];
                $params = [];

                if ($nombreCambiado) {
                    $updateFields[] = "nombre = ?";
                    $params[] = $nombreTutor;
                }
                if ($dniCambiado) {
                    $updateFields[] = "dni = ?";
                    $params[] = $dniTutor;
                }
                if ($telefonoCambiado) {
                    $updateFields[] = "telefono = ?";
                    $params[] = $telefono;
                }

                // Construir la consulta dinámica
                $params[] = $idTutor;
                $updateQuery = "UPDATE Responsables SET " . implode(", ", $updateFields) . " WHERE idResponsable = ?";

                $statement = $conn->prepare($updateQuery);
                $statement->execute($params);
            }
        }

        // Actualizar datos del paciente
        $consulta = "UPDATE Pacientes SET fechaNacimiento = :fechaNacimiento, sexo = :sexo, direccion = :direccion";
        $parametros = [
            'fechaNacimiento' => $fechaNacimiento,
            'sexo' => $sexo,
            'direccion' => $direccion,
            'idpaciente' => $idpaciente
        ];

        // Si el paciente es adulto (idTutor es NULL), agregar el campo telefono
        if ($idTutor == null) {
            $consulta .= ", telefono = :telefono";
            $parametros['telefono'] = $telefono;
        }

        $consulta .= " WHERE idPaciente = :idpaciente";
        $statement = $conn->prepare($consulta);
        $statement->execute($parametros);

        if ($statement->rowCount() > 0) {
            // Actualizar datos del usuario para el paciente
            $consulta = "UPDATE Usuarios SET dni = :dni, nombre = :nombre, apellido = :apellido WHERE idUsuario = :idusuario";
            $statement = $conn->prepare($consulta);
            $statement->execute([$dni, $nombre, $apellido, $idusuario]);

            if ($statement->rowCount() > 0) {
                $_SESSION['success'] = "Paciente Nº {$idpaciente} actualizado correctamente.";
                header('Location: ../pacientes.php');
                exit();
            } else {
                $_SESSION['error'] = "Error al actualizar el usuario del paciente Nº {$idpaciente}.";
                header('Location: ../pacientes.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Error al actualizar los datos del paciente Nº {$idpaciente}.";
            header('Location: ../pacientes.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: ../pacientes.php');
        exit();
    }
}
