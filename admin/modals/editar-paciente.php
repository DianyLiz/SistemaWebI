<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalEditarPaciente" class="modalEditarPaciente">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-paciente.php" method="POST">
            <div class="title">Actualizar datos del Paciente</div>
            <div class="form-group">
                <input id="edit-idusuario" type="text" name="idusuario" autocomplete="off" value="" readonly="true" hidden>
                <label for="edit-idpaciente">ID</label>
                <input id="edit-idpaciente" type="text" name="idpaciente" autocomplete="off" value="" readonly="true">

                <label for="edit-dni">DNI</label>
                <input id="edit-dni" type="text" name="dni" autocomplete="off" value="">

                <label for="edit-nombre">Nombre</label>
                <input id="edit-nombre" type="text" name="nombre" autocomplete="off" value="">

                <label for="edit-apellido">Apellido</label>
                <input id="edit-apellido" type="text" name="apellido" autocomplete="off" value="">

                <label for="edit-sexo">Sexo</label>
                <select id="edit-sexo" name="sexo">
                    <option value="">Seleccionar</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>

                <label for="edit-fechaNacimiento">Fecha de Nacimiento</label>
                <input id="edit-fechaNacimiento" type="date" name="fechaNacimiento" autocomplete="off" value="">

                <label for="edit-telefono">Teléfono</label>
                <input id="edit-telefono" type="text" name="telefono" autocomplete="off" value="">

                <label for="edit-direccion">Dirección (opcional)</label>
                <input id="edit-direccion" type="text" name="direccion" autocomplete="off" value="">
            </div>
            <button type="submit" class="modificar">Modificar Paciente</button>
        </form>
    </div>
</div>