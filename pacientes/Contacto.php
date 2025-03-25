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
            <h2>CONTACTO MEDICOS</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        include '../conexion.php';
                        $sql = "SELECT * FROM Usuarios where rol = 'Médico'"; 
                        $query = $conn->prepare($sql);
                        $query->execute();
                        $Usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($Usuarios as $row) {
                            echo "<tr>";
                            echo "<td>" . $row["nombre"] . " " . $row["apellido"]. "</td>";
                            echo "<td>" . $row["correo"] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>