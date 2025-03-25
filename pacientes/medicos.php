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
            <h2>TABLA DE MEDICOS</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>ESPECIALIDAD</th>
                            <th>LICENCIA</th>
                            <th>AÑOS EXPERIENCIA</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        include '../conexion.php';
                        $sql = "
                            SELECT 
                                u.nombre AS nombreMedico,
                                u.apellido AS apellidoMedico,
                                e.nombreEspecialidad AS especialidad,
                                m.numeroLicenciaMedica,
                                m.anosExperiencia
                            FROM Medicos m
                            JOIN Usuarios u ON m.idUsuario = u.idUsuario
                            JOIN Especialidades e ON m.idEspecialidad = e.idEspecialidad
                        ";
                        $consulta = $conn->prepare($sql);
                        $consulta->execute();
                        $medicos = $consulta->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($medicos as $row) {
                            echo "<tr>";
                            echo "<td>" . $row["nombreMedico"]. " " . $row["apellidoMedico"] . "</td>";
                            echo "<td>" . $row["especialidad"] . "</td>";
                            echo "<td>" . $row["numeroLicenciaMedica"] . "</td>";
                            echo "<td>" . $row["anosExperiencia"] . "</td>";
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