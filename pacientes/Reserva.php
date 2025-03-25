<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/estilo-admin.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="../css/Reserva.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>

<main class="contenido">
    <div class="table-container">
        <h2>Selecciona una Especialidad Para Tu Cita</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Seleccionar</th>
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
                    echo '<td><button class="btn-seleccionar" data-especialidad="' . $row["nombreEspecialidad"] . '">Seleccionar</button></td>';
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="cita-container">
        <button id="btn-regresar">Regresar</button>
        <h3>Selecciona una Fecha Para Tu Cita</h3>
        <input type="text" id="fecha-cita" placeholder="Selecciona una fecha">

        <div id="horarios-disponibles" style="display:none;">
            <h3>Horarios Disponibles</h3>
            <table>
                <thead>
                    <tr>
                        <th>Hora Inicio</th>
                        <th>Hora Fin</th>
                        <th>Medico</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody id="horarios-list"></tbody>
            </table>
        </div>

        <button id="btn-continuar" style="display:none;">Continuar</button>
    </div>
</main>

<script>
$(document).ready(function() {
    let especialidadSeleccionada = "";
    let horarioSeleccionado = "";

    $(".btn-seleccionar").click(function() {
        especialidadSeleccionada = $(this).data("especialidad");
        $("#cita-container").fadeIn();
        $(".table-container").fadeOut();
    });

    flatpickr("#fecha-cita", {
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: "es",
        disableMobile: true,
        onChange: function(selectedDates, dateStr, instance) {
            $("#horarios-disponibles").fadeOut();
            $.ajax({
                url: 'obtenerHorarios.php',
                method: 'POST',
                data: { fecha: dateStr, especialidad: especialidadSeleccionada },
                success: function(response) {
                    if (response.trim()) {
                        $("#horarios-disponibles").fadeIn();
                        $("#horarios-list").html(response);
                    } else {
                        $("#horarios-disponibles").hide();
                        alert("No hay horarios disponibles.");
                    }
                }
            });
        }
    });

    $(document).on("click", ".btn-horario", function() {
        $(".btn-horario").removeClass("selected");
        $(this).addClass("selected");
        horarioSeleccionado = $(this).data("horario");
    });

    $("#btn-regresar").click(function() {
        $("#cita-container").fadeOut();
        $(".table-container").fadeIn();
        especialidadSeleccionada = "";
        horarioSeleccionado = "";
        $("#fecha-cita").val("");
        $("#horarios-list").empty();
        $("#horarios-disponibles").hide();
        $(".btn-horario").removeClass("selected");
        $("#btn-continuar").hide();
    });
});
</script>

</body>
</html>
