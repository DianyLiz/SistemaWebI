<?php
include '../conexion.php'; // Asegúrate de que la ruta es correcta

// Verificar si la conexión se estableció correctamente
if (!$conn) {
    die("Error: No se pudo conectar a la base de datos.");
}

try {
    // Consulta SQL corregida para obtener los nombres desde la tabla Usuarios
    $sql = "SELECT U1.nombre + ' ' + U1.apellido AS paciente, 
                   U2.nombre + ' ' + U2.apellido AS medico, 
                   Citas.fecha, 
                   Citas.hora, 
                   Citas.estado 
            FROM Citas 
            INNER JOIN Pacientes ON Citas.idPaciente = Pacientes.idPaciente
            INNER JOIN Usuarios U1 ON Pacientes.idUsuario = U1.idUsuario
            INNER JOIN Medicos ON Citas.idMedico = Medicos.idMedico
            INNER JOIN Usuarios U2 ON Medicos.idUsuario = U2.idUsuario";

    $query = $conn->prepare($sql);
    $query->execute();
    $citas = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
</head>
<body>
<nav>
    <div class="logo">
        MediCitas
    </div>
    <input type="checkbox" id="click">
    <label for="click" class="menu-btn">
        <i class="fas fa-bars"></i>
    </label>
    <ul class="menu">
        <li><a class="active" href="../medicos/header.php">Salir</a></li>
    </ul>
</nav>

<main>
    <div class="table-container">
        <h2>Tabla de Citas Médicas</h2>
        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mapeo de estados a clases CSS
                $estadoClases = [
                    'Confirmada' => 'confirmed',
                    'Pendiente' => 'pending',
                    'Cancelada' => 'cancelled',
                ];

                if (count($citas) > 0) {
                    foreach ($citas as $fila) {
                        // Formatear la hora
                        $hora_formateada = date("H:i", strtotime($fila['hora']));

                        // Obtener la clase CSS correspondiente al estado
                        $claseEstado = $estadoClases[$fila['estado']] ?? '';

                        echo "<tr>
                                <td>{$fila['paciente']}</td>
                                <td>{$fila['medico']}</td>
                                <td>{$fila['fecha']}</td>
                                <td>{$hora_formateada}</td>
                                <td><span class='status $claseEstado'>" . ucfirst($fila['estado']) . "</span></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay citas registradas</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<?php
// Cerrar conexión
$conn = null;
?>

</body>
</html>