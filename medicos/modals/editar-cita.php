<link rel="stylesheet" href="../css/modal-cita.css">
<div id="modalEditarCita" class="modalEditarCita">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-cita.php" method="POST">
            <div class="title">Actualizar datos de la Cita</div>
            <div class="form-group">
                <label for="edit-idCita">ID Cita</label>
                <input id="edit-idCita" type="text" name="idCita" autocomplete="off" readonly>

                <label for="edit-idpaciente">ID Paciente</label>
                <input id="edit-idpaciente" type="text" name="idPaciente" autocomplete="off">

                <label for="edit-nombrePaciente">Paciente</label>
                <input id="edit-nombrePaciente" type="text" name="nombrePaciente" autocomplete="off">

                <label for="edit-idmedico">ID Medico</label>
                <input id="edit-idmedico" type="text" name="idMedico" autocomplete="off">

                <label for="edit-nombreMedico">Médico</label>
                <input id="edit-nombreMedico" type="text" name="nombreMedico" autocomplete="off">


                <label for="edit-hora">Hora</label>
                <input id="edit-hora" type="time" name="hora" autocomplete="off">

                <label for="edit-motivo">Motivo</label>
                <input id="edit-motivo" type="text" name="motivo" autocomplete="off">

                <label for="edit-estado">Estado</label>
                <select id="edit-estado" name="estado">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>

                <label for="edit-fecha">Fecha</label>
                <input id="edit-fecha" type="date" name="fecha" autocomplete="off">
            </div>
            <button type="submit" class="modificar">Modificar Cita</button>
        </form>
    </div>
</div>