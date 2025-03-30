<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Cita</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .confirmacion-container {
            background-color: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }

        .confirmacion-container i {
            font-size: 60px;
            color: #38c172;
            margin-bottom: 20px;
        }

        .confirmacion-container h2 {
            margin-bottom: 10px;
            font-size: 28px;
            color: #2d3748;
        }

        .confirmacion-container p {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 30px;
        }

        .btn-volver {
            padding: 10px 25px;
            background-color: #042947;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-volver:hover {
            background-color: #064170;
        }

    </style>
</head>
<body>

    <div class="confirmacion-container">
        <i class="fas fa-check-circle"></i>
        <h2>¡Cita Confirmada!</h2>
        <p>Tu cita ha sido registrada con éxito. Pronto recibirás un correo con los detalles.</p>
        <a href="index.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        <script>
    window.onload = function() {
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 }
        });
    };
</script>
</body>
</html>
