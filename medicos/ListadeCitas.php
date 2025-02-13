<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
         <ul>
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
                    <tr>
                        <td>Juan Pérez</td>
                        <td>Dr. Carlos Gómez</td>
                        <td>2023-10-15</td>
                        <td>10:00 AM</td>
                        <td><span class="status confirmed">Confirmada</span></td>
                    </tr>
                    <tr>
                        <td>María López</td>
                        <td>Dra. Ana Martínez</td>
                        <td>2023-10-16</td>
                        <td>11:30 AM</td>
                        <td><span class="status pending">Pendiente</span></td>
                    </tr>
                    <tr>
                        <td>Carlos Sánchez</td>
                        <td>Dr. Luis Rodríguez</td>
                        <td>2023-10-17</td>
                        <td>09:00 AM</td>
                        <td><span class="status cancelled">Cancelada</span></td>
                    </tr>
                    <tr>
                        <td>Laura García</td>
                        <td>Dr. Pedro Fernández</td>
                        <td>2023-10-18</td>
                        <td>03:00 PM</td>
                        <td><span class="status confirmed">Confirmada</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
