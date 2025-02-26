<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalEditarUsuario" class="modalEditarUsuario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-user.php" method="POST">
            <div class="title">Actualizar datos del Usuario</div>
            <div class="form-group">
                <label for="edit-idusuario">ID</label>
                <input id="edit-idusuario" type="text" name="idusuario" autocomplete="off" value="" readonly="true">

                <label for="edit-dni">DNI</label>
                <input id="edit-dni" type="text" name="dni" autocomplete="off" value="">

                <label for="edit-nombre">Nombre</label>
                <input id="edit-nombre" type="text" name="nombre" autocomplete="off" value="">

                <label for="edit-apellido">Apellido</label>
                <input id="edit-apellido" type="text" name="apellido" autocomplete="off" value="">

                <label for="edit-correo">Correo</label>
                <input id="edit-correo" type="email" name="correo" autocomplete="off" value="">

                <label for="edit-usuario">Nombre de Usuario</label>
                <input id="edit-usuario" type="text" name="usuario" autocomplete="off" value="">
            </div>
            <button type="submit" class="modificar">Modificar Usuario</button>
        </form>
    </div>
</div>