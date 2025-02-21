<?php
session_start();
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['usuario']['rol'] != "Médico") {
        header('Location: ../principal/');
    }
} else {
    header('Location: ../principal/');
}
