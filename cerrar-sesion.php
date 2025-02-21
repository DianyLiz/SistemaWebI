<?php 
    session_start();
    session_destroy();
    header('Location: ../CitasMedicas/principal/');
?>