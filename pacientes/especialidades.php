<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/estilo-admin.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <main class="contenido">
        <div class="table-container">
            <h2>TABLA DE ESPECIALIDADES</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include '../conexion.php';
                        $sql = "SELECT * FROM Especialidades";
                        $consulta = $conn->prepare($sql);
                        $consulta->execute();
                        $especialidades = $consulta->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($especialidades as $row) {
                            echo "<tr>";
                            echo "<td>" . $row["nombreEspecialidad"] . "</td>";
                            echo "<td>" . $row["descripcion"] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>