<?php
include '../../conexion.php';
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $DNImedico = $_POST['query'];
    $consulta = "SELECT T1.nombre + ' ' + T1.apellido AS Medico, T2.idMedico
                 FROM Usuarios T1 
                 INNER JOIN Medicos T2 ON T2.idUsuario = T1.idUsuario
                 WHERE T1.dni = ? AND T1.rol = 'Médico'";
    $statement = $conn->prepare($consulta);
    $statement->execute([$DNImedico]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(["nombre" => $result["Medico"], "idMedico" => $result["idMedico"]]);
    } else {
        echo json_encode(["nombre" => "", "idMedico" => ""]);
    }
}
?>
