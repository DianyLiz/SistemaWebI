<link rel="stylesheet" href="../css/modal-horario.css">
<div id="modalEditarHorario" class="modalEditarHorario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-horario.php" method="POST">
            <div class="title">Actualizar datos del Horario</div>
            <div class="form-group">
                <label for="edit-idHorario">ID Horario</label>
                <input id="edit-idHorario" type="text" name="idHorario" readonly>
                <label for="edit-idmedico">ID Médico</label>
                <input id="edit-idmedico" type="text" name="idMedico">
                <label for="edit-nombreMedico">Médico</label>
                <input id="edit-nombreMedico" type="text" name="nombreMedico">
                <label for="edit-dia">Día</label>
                <input id="edit-dia" type="text" name="diaSemana">
                <label for="edit-hora">Hora Inicio</label>
                <input id="edit-hora" type="time" name="horaInicio">
                <label for="edit-fin">Hora Fin</label>
                <input id="edit-fin" type="time" name="horaFin">
                <label for="edit-cupos">Cupos</label>
                <input id="edit-cupos" type="number" name="cupos">
                <label for="edit-fecha">Fecha</label>
                <input id="edit-fecha" type="date" name="fecha">
            </div>
            <button type="submit" class="modificar">Modificar Horario</button>
        </form>
    </div>
</div>
