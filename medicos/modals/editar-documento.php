<div id="modalEditarDocumento" class="modalEditarDocumento">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-documento.php" method="POST">
            <div class="title">Editar datos de Documentos Medicos</div>
            <div class="form-group">
                <label for="edit-idDocumento">ID Documento</label>
                <input id="edit-idDocumento" type="number" name="idDocumento" autocomplete="off" readonly>

                <label for="edit-idPaciente">ID Paciente</label>
                <input id="edit-idPaciente" type="number" name="idPaciente" autocomplete="off">

                <label for="edit-nombrePaciente">Paciente</label>
                <input id="edit-nombrePaciente" type="text" name="nombrePaciente" autocomplete="off">

                <label for="edit-idCita">ID Cita</label>
                <input id="edit-idCita" type="number" name="idCita" autocomplete="off">

                <label for="edit-fechaCita">Fecha de Cita</label>
                <input id="edit-fechaCita" type="text" name="fechaCita" autocomplete="off">

                <label for="edit-tipoDocumento">Tipo de Documento</label>
                <input id="edit-tipoDocumento" type="text" name="tipoDocumento" autocomplete="off">

                <label for="edit-descripcion">Descripción</label>
                <input id="edit-descripcion" type="text" name="descripcion" autocomplete="off">

                <label for="edit-fechaSubida">Fecha de Subida</label>
                <input id="edit-fechaSubida" type="date" name="fechaSubida" autocomplete="off">

                <label for="edit-idMedico">ID Medico</label>
                <input id="edit-idMedico" type="number" name="idMedico" autocomplete="off">

                <label for="edit-nombreMedico">Médico</label>
                <input id="edit-nombreMedico" type="text" name="nombreMedico" autocomplete="off">
            </div>
            <button type="submit" class="modificar">Modificar Documento</button>
        </form>
    </div>
</div>