<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header('Location: ../principal/');
    exit;
}

if ($_SESSION['usuario']['rol'] != "Paciente") {
    header('Location: ../principal/');
    exit;
}
?>