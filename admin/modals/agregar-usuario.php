<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalAgregarUsuario" class="modalAgregarUsuario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-user.php" method="POST">
            <div class="title">Nuevo Usuario</div>
            <div class="form-group">
                <label for="add-dni">DNI</label>
                <input id="add-dni" type="text" name="dni" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['dni']) ? $_SESSION['form_data']['dni'] : ''; ?>">

                <label for="add-nombre">Nombre</label>
                <input id="add-nombre" type="text" name="nombre" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['nombre']) ? $_SESSION['form_data']['nombre'] : ''; ?>">

                <label for="add-apellido">Apellido</label>
                <input id="add-apellido" type="text" name="apellido" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['apellido']) ? $_SESSION['form_data']['apellido'] : ''; ?>">

                <label for="add-correo">Correo</label>
                <input id="add-correo" type="email" name="correo" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['correo']) ? $_SESSION['form_data']['correo'] : ''; ?>">

                <label for="add-usuario">Nombre de Usuario</label>
                <input id="add-usuario" type="text" name="usuario" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['usuario']) ? $_SESSION['form_data']['usuario'] : ''; ?>">

                <label for="add-password">Contraseña</label>
                <input id="add-password" type="password" name="password" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['password']) ? $_SESSION['form_data']['password'] : ''; ?>">

                <label for="add-confirmPassword">Confirmar Contraseña</label>
                <input id="add-confirmPassword" type="password" name="confirmPassword" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['confirmPassword']) ? $_SESSION['form_data']['confirmPassword'] : ''; ?>">

                <label for="add-tipoUsuario">Tipo de Usuario</label>
                <select id="add-tipoUsuario" name="tipoUsuario">
                <option value="" <?= empty($_SESSION['form_data']['tipoUsuario']) ? 'selected' : '' ?>>Seleccionar</option>
                <option value="1" <?= (isset($_SESSION['form_data']['tipoUsuario']) && $_SESSION['form_data']['tipoUsuario'] == '1') ? 'selected' : '' ?>>Paciente</option>
                <option value="2" <?= (isset($_SESSION['form_data']['tipoUsuario']) && $_SESSION['form_data']['tipoUsuario'] == '2') ? 'selected' : '' ?>>Médico</option>
                <option value="3" <?= (isset($_SESSION['form_data']['tipoUsuario']) && $_SESSION['form_data']['tipoUsuario'] == '3') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <button type="submit" class="modificar">Registrar Usuario</button>
        </form>
    </div>
</div>