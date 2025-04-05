<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/estilo-admin.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>

    <main class="contenido">
        <div class="table-container">
            <h2>CONTACTOS MEDICOS</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include '../conexion.php';
                        $sql = "SELECT CONCAT(T1.nombre,' ',T1.apellido) AS nombre, 
                                T1.correo, T2.telefono, T3.nombreEspecialidad 
                                FROM Usuarios T1
                                INNER JOIN medicos T2 ON T2.idUsuario = T1.idUsuario
                                INNER JOIN especialidades T3 ON T3.idEspecialidad = T2.idEspecialidad
                                where T1.rol = 'Médico'";
                        $query = $conn->prepare($sql);
                        $query->execute();
                        $Usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($Usuarios as $row) {
                            echo "<tr>";
                            echo "<td>" . $row["nombre"] . "</td>";
                            echo "<td>" . $row["nombreEspecialidad"] . "</td>";
                            echo "<td>" . $row["correo"] . "</td>";
                            echo "<td>" . $row["telefono"] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <section id="contacto" class="container">
            <h2>Contáctanos</h2>
            <p>Ubicación: Santa Bárbara, Honduras</p>
            <p>Contáctanos Por WhatsApp:</p>
            <a href="https://wa.me/+50495629127" target="_blank" class="btn-whatsapp">
                <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
            </a>
        </section>
    </main>

</body>