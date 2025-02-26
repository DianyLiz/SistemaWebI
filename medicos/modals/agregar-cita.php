<link rel="stylesheet" href="../css/modal-usuario.css">
<link rel="stylesheet" href="../css/modal-usuario.css">

<div id="modalAgregarCita" class="modalAgregarUsuario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-cita.php" method="POST">
            <div class="title">Nueva Cita</div>
            <div class="form-group">
                <label for="add-paciente">Paciente</label>
                <input id="add-paciente" type="text" name="paciente" autocomplete="off" required>

                <label for="add-medico">Médico</label>
                <input id="add-medico" type="text" name="medico" autocomplete="off" required>

                <label for="add-fecha">Fecha</label>
                <input id="add-fecha" type="date" name="fecha" autocomplete="off" required>

                <label for="add-hora">Hora</label>
                <input id="add-hora" type="time" name="hora" autocomplete="off" required>

                <label for="add-estado">Estado</label>
                <select id="add-estado" name="estado" required>
                    <option value="">Seleccionar</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>
            <button type="submit" class="modificar">Agregar Cita</button>
        </form>
    </div>
</div>
<script>
    // Obtener el modal
    var modal = document.getElementById("modalAgregarCita");

    // Obtener el botón que abre el modal
    var btn = document.getElementById("abrirModal");

    // Obtener el elemento <span> que cierra el modal
    var span = document.getElementsByClassName("close")[0];

    // Cuando el usuario hace clic en el botón, abre el modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Cuando el usuario hace clic en <span> (x), cierra el modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Cuando el usuario hace clic fuera del modal, ciérralo
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>