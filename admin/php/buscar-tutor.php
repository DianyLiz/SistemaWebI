<?php
include '../../conexion.php';
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $busqueda = "%" . $_POST['query'] . "%"; // Permite buscar nombres parciales
    $consulta = "SELECT TOP 10 T1.nombre AS Tutor, T1.dni, T1.idResponsable, T1.telefono
                 FROM Responsables T1 
                 WHERE T1.nombre LIKE ? ";

    $statement = $conn->prepare($consulta);
    $statement->execute([$busqueda]);
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

     echo json_encode($result ?: []); // Si no hay resultados, devolver un array vacío
}
?>
