<?php
$server = 'Tomy_PC\SQLEXPRESS';
$database = 'SistemaCitasMedicas';
$username = 'user_tomy';
$password = '19022005';

try {
    $conn = new PDO("sqlsrv:server=$server;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Conexión establecida";
} catch (PDOException $e) {
    echo "Error de conexion: " . $e->getMessage();
}
?>