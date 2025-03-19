<link rel="stylesheet" href="../css/modal-dmedicos.css">
<div id="modalEditarDocumento" class="modalEditarDocumento">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-documento.php" method="POST" enctype="multipart/form-data">
            <div class="title">Editar Documento Médico</div>
            <div class="form-group">

                <label for="edit-idPaciente">ID Paciente</label>
                <input id="edit-idPaciente" type="text" name="idPaciente" autocomplete="off" readonly>

                <label for="edit-nombrePaciente">Paciente</label>
                <input id="edit-nombrePaciente" type="text" name="nombrePaciente" autocomplete="off" readonly>

                <label for="edit-idMedico">ID Médico</label>
                <input id="edit-idMedico" type="text" name="idMedico" autocomplete="off" readonly>

                <label for="edit-nombreMedico">Médico</label>
                <input id="edit-nombreMedico" type="text" name="nombreMedico" autocomplete="off" readonly>

                <label for="edit-tipoDocumento">Tipo de Documento</label>
                <select id="edit-tipoDocumento" name="tipoDocumento" required>
                    <option value="Receta">Receta</option>
                    <option value="Informe">Constancia</option>
                </select>

                <label for="edit-archivo">Documento</label>
                <input type="file" id="edit-archivo" name="urlDocumento" accept="application/pdf,image/*">

                <label for="edit-descripcion">Descripción</label>
                <textarea id="edit-descripcion" name="descripcion" autocomplete="off"></textarea>

                <label for="edit-fechaSubida">Fecha de Subida</label>
                <input id="edit-fechaSubida" type="date" name="fechaSubida" autocomplete="off">
            </div>
            <button type="submit" class="modificar">Actualizar Documento</button>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById("modalEditarDocumento");
    var btn = document.getElementById("abrirModal");
    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
