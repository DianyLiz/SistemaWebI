<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalAgregarEspecialidad" class="modalAgregarEspecialidad">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-especialidad.php" method="POST">
            <div class="title">Nueva Especialidad</div>
            <div class="form-group">
                <label for="add-especialidad">Especialidad</label>
                <input type="text" name="especialidad" id="add-especialidad" autocomplete="off" required value="<?= isset($_SESSION['form_data']['especialidad']) ? $_SESSION['form_data']['especialidad'] : '' ?>">

                <label for="add-descripcion">Descripción</label>
                <textarea id="add-descripcion" name="descripcion" rows="10" cols="50" style="resize: none;"><?= isset($_SESSION['form_data']['descripcion']) ? $_SESSION['form_data']['descripcion'] : '' ?></textarea>
            </div>
            <button type="submit" class="modificar">Agregar Especialidad</button>
        </form>
    </div>
</div>