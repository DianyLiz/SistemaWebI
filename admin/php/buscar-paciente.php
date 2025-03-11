<?php
include '../../conexion.php';
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $DNIpaciente = $_POST['query'];
    $consulta = "SELECT T1.nombre + ' ' + T1.apellido AS Paciente, T2.idPaciente
                 FROM Usuarios T1 
                 INNER JOIN Pacientes T2 ON T2.idUsuario = T1.idUsuario
                 WHERE T1.dni = ? AND T1.rol = 'Paciente'";
    $statement = $conn->prepare($consulta);
    $statement->execute([$DNIpaciente]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(["nombre" => $result["Paciente"], "idPaciente" => $result["idPaciente"]]);
    } else {
        echo json_encode(["nombre" => "", "idPaciente" => ""]);
    }
}
?>
