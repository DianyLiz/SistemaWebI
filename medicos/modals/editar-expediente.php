<div id="modalEditarExpediente" class="modalEditarExpediente">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-expediente.php" method="POST">
            <div class="title">Editar datos de Expediente</div>
            <div class="form-group">
                <label for="edit-idExpediente">ID Expediente</label>
                <input id="edit-idExpediente" type="number" name="idExpediente" autocomplete="off" readonly>

                <label for="edit-idPaciente">ID Paciente</label>
                <input id="edit-idPaciente" type="number" name="idPaciente" autocomplete="off">

                <label for="edit-nombrePaciente">Paciente</label>
                <input id="edit-nombrePaciente" type="text" name="nombrePaciente" autocomplete="off">

                <label for="edit-fechaCreacion">Fecha Creacion</label>
                <input id="edit-fechaCreacion" type="date" name="fechaCreacion" autocomplete="off">

                <label for="edit-antecedentes">Antecedentes</label>
                <input id="edit-antecedentes" type="text" name="antecedentes" autocomplete="off">

                <label for="edit-alergias">Alergias</label>
                <input id="edit-alergias" type="text" name="alergias" autocomplete="off">

                <label for="edit-medicamentos">Medicamentos Actuales</label>
                <input id="edit-medicamentos" type="text" name="medicamentosActuales" autocomplete="off">

                <label for="edit-enfermedades">Enfermedades Crónicas</label>
                <input id="edit-enfermedades" type="text" name="enfermedadesCronicas" autocomplete="off">

                <label for="edit-descripcion">Descripción</label>
                <input id="edit-descripcion" type="text" name="descripcion" autocomplete="off">

                <label for="edit-fechaActualizacion">Fecha de Actualización</label>
                <input id="edit-fechaActualizacion" type="date" name="fechaActualizacion" autocomplete="off">
            </div>
            <button type="submit" class="modificar">Modificar Expediente</button>
        </form>
    </div>
</div>

         