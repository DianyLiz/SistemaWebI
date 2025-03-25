<?php
include '../conexion.php';

try {
    $sql_pacientes = "SELECT COUNT(*) AS total FROM pacientes";
    $stmt_pacientes = $conn->prepare($sql_pacientes);
    $stmt_pacientes->execute();
    $row_pacientes = $stmt_pacientes->fetch(PDO::FETCH_ASSOC);
    $total_pacientes = $row_pacientes ? $row_pacientes['total'] : 0;

    $sql_citas = "SELECT COUNT(*) AS total FROM citas";
    $stmt_citas = $conn->prepare($sql_citas);
    $stmt_citas->execute();
    $row_citas = $stmt_citas->fetch(PDO::FETCH_ASSOC);
    $total_citas = $row_citas ? $row_citas['total'] : 0;
    
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Médico</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .contenedor {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        header {
            background-color: #2196F3;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .usuario {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .usuario img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .contenedor-principal {
            display: flex;
            flex: 1;
        }

        .menu {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 1rem;
        }

        .menu ul {
            list-style: none;
        }

        .menu li {
            padding: 0.5rem;
            margin: 0.5rem 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .menu li:hover {
            background-color: #34495e;
        }

        .menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contenido {
            flex: 1;
            padding: 2rem;
        }

        .contenido h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 2rem;
        }

        .bienvenida {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            text-align: center;
        }

        .bienvenida h2 {
            color: #2196F3;
            margin-bottom: 1rem;
        }

        .btn-accion {
            padding: 0.75rem 1.5rem;
            border: none;
            background-color: #2196F3;
            color: white;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
            transition: background 0.3s;
        }

        .btn-accion:hover {
            background-color: #1976D2;
        }

        .estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .estadistica {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .estadistica h3 {
            color: #2196F3;
            margin-bottom: 0.5rem;
        }

        .estadistica p {
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include '../medicos/header.php'; ?>

    <div class="contenedor">
        <div class="contenedor-principal">
            <?php include '../medicos/menu.php'; ?>

            <main class="contenido">
                <h1>Bienvenido al Panel Médico</h1>

                <div class="bienvenida">
                    <h2>Portal Médico</h2>
                    <p>Este es su panel de control principal donde podrá acceder a todas las funcionalidades del módulo de médico.</p>
                </div>

                <div class="estadisticas">
                    <div class="estadistica">
                        <h3>Pacientes</h3>
                        <p><?php echo htmlspecialchars($total_pacientes); ?></p>
                    </div>
                    <div class="estadistica">
                        <h3>Citas Médicas</h3>
                        <p><?php echo htmlspecialchars($total_citas); ?></p>
                    </div>
                </div>
            </main>
        </div>
    </div>

</body>
</html>