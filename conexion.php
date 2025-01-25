<?php

class Conexion{

    public function ConexionBD(){
    $host='Diany\SQLEXPRESS';//aquí pone nombre que sale en SQL Server Management Studio
    $user='Diany';
    $password='Diany2004'; //contraseña con la que se mete a sql
    $db='SistemaCitasMedicas';

    try{
        $conexion = new PDO("sqlsrv:Server=$host;Database=$db",$user,$password);
        echo "Conexión exitosa";
        return $conexion;
    }
    catch(PDOException $e){
        echo "Error: ".$e->getMessage();
        echo "Error en la conexión";
    }
    }
}
?>