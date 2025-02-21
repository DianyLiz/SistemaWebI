<?php
session_start();
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['usuario']['rol'] != "Paciente") {
        header('Location: ../principal/');
    }
} else {
    header('Location: ../principal/');
}
