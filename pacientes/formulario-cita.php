<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idHorario = $_POST['horario'] ?? '';
    $idMedico = $_POST['medico'] ?? '';
    $hora = $_POST['hora'] ?? '';

    if (!$idHorario || !$idMedico || !$hora) {
        echo "<p>Error: Datos incompletos. Volvé atrás e intentá de nuevo.</p>";
        exit;
    }
} else {
    header("Location: reserva.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Cita</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: left;
            width: 100%;
            max-width: 500px;
        }

        .form-container h2 {
            text-align: center;
            color: #2d3748;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #4a5568;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 16px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-guardar {
        background-color: #042947;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    .btn-guardar:hover {
    background-color: #064170;
    }

    </style>
</head>
<body>

    <form action="InsertarCitas.php" method="POST" class="form-container">
        <h2>Confirmar Cita</h2>
        <input type="hidden" name="horario" value="<?php echo htmlspecialchars($idHorario); ?>">
        <input type="hidden" name="medico" value="<?php echo htmlspecialchars($idMedico); ?>">
        <input type="hidden" name="hora" value="<?php echo htmlspecialchars($hora); ?>">

        <div class="form-group">
            <label for="dni">DNI:</label>
            <input type="text" id="dni" name="dni" required>
        </div>

        <div class="form-group">
            <label for="motivo">Motivo de la Cita:</label>
            <textarea id="motivo" name="motivo" required></textarea>
        </div>

        <button type="submit" class="btn-guardar">Confirmar Cita</button>
    </form>

</body>
</html>
