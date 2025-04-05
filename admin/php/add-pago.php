<?php 
    include '../../conexion.php';
    session_start();
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $_SESSION['form_data'] = $_POST;
        $idCita = $_POST['idCita'];
        $paciente = $_POST['paciente'];
        $dniPaciente = $_POST['dniPaciente'];
        $fecha = $_POST['fecha'];
        $monto = $_POST['monto'];
        $metodoPago = $_POST['metodoPago'];
        $fechaPago = date('Y-m-d');

        if(empty($idCita) || empty($monto) || empty($metodoPago)) {
            $_SESSION['error'] = "Complete los campos obligatorios.";
            header('Location: ../financiamientos.php');
            exit();
        }

        try {
            // Verificar que la cita haya sido confirmada antes de proceder a pagar
            $consulta = "SELECT * FROM Citas WHERE idCita = ? AND estado = 'Confirmada'";
            $statement = $conn->prepare($consulta);
            $statement->execute([$idCita]);

            if($statement->fetch()) {
                // Verificar que el monto sea mayor a cero
                if($monto <= 0) {
                    $_SESSION['error'] = "El monto debe ser mayor a cero.";
                    header('Location: ../financiamientos.php');
                    exit();
                }

                // Verificar que la cita no haya sido pagada ya
                $consulta = "SELECT * FROM Pagos WHERE idCita = ?";
                $statement = $conn->prepare($consulta);
                $statement->execute([$idCita]);

                if($statement->fetch()) {
                    $_SESSION['error'] = "La cita Nº {$idCita} ya ha sido pagada.";
                    header('Location: ../financiamientos.php');
                    exit();
                }

                // Proceder al pago de la cita
                $consulta = "INSERT INTO Pagos (idCita, monto, metodoPago, fechaPago) VALUES (?, ?, ?, ?)";
                $statement = $conn->prepare($consulta);
                $statement->execute([$idCita, $monto, $metodoPago, $fechaPago]);
                $_SESSION['success'] = "Pago agregado exitosamente.";
                unset($_SESSION['form_data']);
                header('Location: ../financiamientos.php');
                exit();
            } else {
                $_SESSION['error'] = "La cita seleccionada no está confirmada o no existe.";
                header('Location: ../ListadeCitas.php');
                exit();}


        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al agregar el pago: " . $e->getMessage();
            header('Location: ../ListadeCitas.php');
            exit();
        }
    }
?>