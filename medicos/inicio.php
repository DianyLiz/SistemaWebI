<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Módulo Médicos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            padding: 2rem 0;
        }
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .section {
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: white;
        }
        h1 {
            color: #333;
        }
        h2 {
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido al Módulo de Médicos</h1>
            <h2>Sistema de Gestión Médica</h2>
        </div>

        <div class="content">
            <div class="section">
                <h2>Ultimas Citas</h2>
                <p>Aquí se mostrarán las citas recientes</p>
                <!-- Aquí puedes agregar una tabla con las citas -->
            </div>

            <div class="section">
                <h2>Pacientes</h2>
                <p>Lista de pacientes asignados</p>
                <!-- Aquí puedes agregar una lista de pacientes -->
            </div>

            <div class="section">
                <h2>Acciones Rápidas</h2>
                <p>Accesos directos a las funciones principales</p>
                <a href="#" class="button">Nueva Cita</a>
                <a href="#" class="button">Ver Historial</a>
                <a href="#" class="button">Buscar Paciente</a>
            </div>
        </div>
    </div>
</body>
</html>