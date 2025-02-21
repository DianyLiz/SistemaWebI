<?php
session_start();
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['usuario']['rol'] != "Administrador") {
        header('Location: ../principal/');
    }
} else {
    header('Location: ../principal/');
}
