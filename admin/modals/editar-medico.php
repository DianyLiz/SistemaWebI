<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalEditarMedico" class="modalEditarMedico">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-doctor.php" method="POST">
            <div class="title">Actualizar datos del Médico</div>
            <div class="form-group">
                <input id="edit-idusuario" type="text" name="idusuario" autocomplete="off" value="" readonly="true" hidden>
                <label for="edit-idmedico">ID</label>
                <input id="edit-idmedico" type="text" name="idmedico" autocomplete="off" value="" readonly="true">

                <label for="edit-dni">DNI</label>
                <input id="edit-dni" type="text" name="dni" autocomplete="off" value="">

                <label for="edit-nombre">Nombre</label>
                <input id="edit-nombre" type="text" name="nombre" autocomplete="off" value="">

                <label for="edit-apellido">Apellido</label>
                <input id="edit-apellido" type="text" name="apellido" autocomplete="off" value="">

                <label for="edit-idespecialidad">Especialidad</label>
                <select id="edit-idespecialidad" name="idespecialidad">
                    <option value="0">Seleccionar</option>
                    <?php
                    include '../conexion.php';
                    $consulta = "SELECT * FROM Especialidades";
                    $statement = $conn->prepare($consulta);
                    $statement->execute();
                    $resultset = $statement->fetchAll();
                    foreach ($resultset as $especialidad) {
                        echo '<option value="' . $especialidad['idEspecialidad'] . '">' . $especialidad['nombreEspecialidad'] . '</option>';
                    }
                    ?>
                </select>

                <label for="edit-licenciaMedica">Nº Licencia Medica</label>
                <input id="edit-licenciaMedica" type="text" name="licenciaMedica" autocomplete="off" value="">

                <label for="edit-aniosExperiencia">Años de Experiencia</label>
                <input id="edit-aniosExperiencia" type="text" name="aniosExperiencia" autocomplete="off" value="">

                <label for="edit-telefonoMedico">Teléfono</label>
                <input id="edit-telefonoMedico" type="text" name="telefonoMedico" autocomplete="off" value="">
            </div>
            <button type="submit" class="modificar">Modificar Médico</button>
        </form>
    </div>
</div>