<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalEditarEspecialidad" class="modalEditarEspecialidad">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-especialidad.php" method="POST">
            <div class="title">Actualizar datos de la Especialidad</div>
            <div class="form-group">
                <label for="edit-idespecialidad">ID Especialidad</label>
                <input type="text" name="idespecialidad" id="edit-idespecialidad" autocomplete="off" required readonly>

                <label for="edit-especialidad">Especialidad</label>
                <input type="text" name="especialidad" id="edit-especialidad" autocomplete="off" required value="">

                <label for="edit-descripcion">Descripción</label>
                <textarea id="edit-descripcion" name="descripcion" rows="10" cols="50" style="resize: none;"></textarea>
            </div>
            <button type="submit" class="modificar">Modificar Especialidad</button>
        </form>
    </div>
</div>