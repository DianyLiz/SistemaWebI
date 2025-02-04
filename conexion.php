<?php
$server = 'Diany\SQLEXPRESS';
$database = 'SistemaCitasMedicas';
$username = 'Diany';
$password = 'Diany2004';

try {
    $conn = new PDO("sqlsrv:server=$server;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Conexión establecida";
} catch (PDOException $e) {
    echo "Error de conexion: " . $e->getMessage();
}
?>