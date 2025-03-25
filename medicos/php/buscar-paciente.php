<?php
include '../../conexion.php';
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $busqueda = "%" . $_POST['query'] . "%"; // Permite buscar nombres parciales
    $consulta = "SELECT TOP 10 T1.nombre + ' ' + T1.apellido AS Paciente, T2.idPaciente, T1.dni
                 FROM Usuarios T1 
                 INNER JOIN Pacientes T2 ON T2.idUsuario = T1.idUsuario
                 WHERE T1.nombre LIKE ? AND T1.rol = 'Paciente'";

    $statement = $conn->prepare($consulta);
    $statement->execute([$busqueda]);
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: []); // Si no hay resultados, devolver un array vacío
}

?>
