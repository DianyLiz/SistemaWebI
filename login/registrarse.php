<!DOCTYPE html>
<html lang="en">
<?php session_start(); ?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro a MediCitas</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .form-doctor,
        .form-paciente {
            display: none;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <form action="php/registration.php" method="POST">
        <div class="title">Registrarse a MediCitas</div>

        <div class="form-group">
            <label for="dni">DNI</label>
            <input id="dni" type="text" name="dni" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['dni']) ? $_SESSION['form_data']['dni'] : ''; ?>">

            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['nombre']) ? $_SESSION['form_data']['nombre'] : ''; ?>">

            <label for="apellido">Apellido</label>
            <input id="apellido" type="text" name="apellido" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['apellido']) ? $_SESSION['form_data']['apellido'] : ''; ?>">

            <label for="correo">Correo</label>
            <input id="correo" type="email" name="correo" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['correo']) ? $_SESSION['form_data']['correo'] : ''; ?>">

            <label for="usuario">Nombre de Usuario</label>
            <input id="usuario" type="text" name="usuario" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['usuario']) ? $_SESSION['form_data']['usuario'] : ''; ?>">

            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['password']) ? $_SESSION['form_data']['password'] : ''; ?>">

            <label for="confirmPassword">Confirmar Contraseña</label>
            <input id="confirmPassword" type="password" name="confirmPassword" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['confirmPassword']) ? $_SESSION['form_data']['confirmPassword'] : ''; ?>">

            <label for="tipoUsuario">Tipo de Usuario</label>
            <select id="tipoUsuario" name="tipoUsuario">
                <option value="" <?= empty($_SESSION['form_data']['tipoUsuario']) ? 'selected' : '' ?>>Seleccionar</option>
                <option value="1" <?= (isset($_SESSION['form_data']['tipoUsuario']) && $_SESSION['form_data']['tipoUsuario'] == '1') ? 'selected' : '' ?>>Paciente</option>
                <option value="2" <?= (isset($_SESSION['form_data']['tipoUsuario']) && $_SESSION['form_data']['tipoUsuario'] == '2') ? 'selected' : '' ?>>Médico</option>
                <option value="3" <?= (isset($_SESSION['form_data']['tipoUsuario']) && $_SESSION['form_data']['tipoUsuario'] == '3') ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>

        <div class="form-doctor">
            <label for="idespecialidad">Especialidad</label>
            <select id="idespecialidad" name="idespecialidad">
                <option value="0">Seleccionar</option>
                <?php

                include '../conexion.php';
                $consulta = "SELECT * FROM Especialidades";
                $statement = $conn->prepare($consulta);
                $statement->execute();
                $resultset = $statement->fetchAll();
                foreach ($resultset as $especialidad) {
                    $isselected = isset($_SESSION['form_data']['idespecialidad']) && $_SESSION['form_data']['idespecialidad'] == $especialidad['idEspecialidad'] ? 'selected' : '';
                    echo '<option value="' . $especialidad['idEspecialidad'] . '" ' . $isselected . '>' . $especialidad['nombreEspecialidad'] . '</option>';
                }
                ?>
            </select>

            <label for="licenciaMedica">Nº Licencia Médica</label>
            <input id="licenciaMedica" type="text" name="licenciaMedica" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['licenciaMedica']) ? $_SESSION['form_data']['licenciaMedica'] : ''; ?>">

            <label for="aniosExperiencia">Años de Experiencia</label>
            <input id="aniosExperiencia" type="text" name="aniosExperiencia" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['aniosExperiencia']) ? $_SESSION['form_data']['aniosExperiencia'] : ''; ?>">
        </div>

        <div class="form-paciente">
            <label for="fechaNacimiento">Fecha de Nacimiento</label>
            <input id="fechaNacimiento" type="date" name="fechaNacimiento" value="<?php echo isset($_SESSION['form_data']['fechaNacimiento']) ? $_SESSION['form_data']['fechaNacimiento'] : ''; ?>">

            <label for="sexo">Sexo</label>
            <select id="sexo" name="sexo">
                <option value="" <?= empty($_SESSION['form_data']['tipoUsuario']) ? 'selected' : '' ?>>Seleccionar</option>
                <option value="Masculino" <?= (isset($_SESSION['form_data']['sexo']) && $_SESSION['form_data']['sexo'] == 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                <option value="Femenino" <?= (isset($_SESSION['form_data']['sexo']) && $_SESSION['form_data']['sexo'] == 'Femenino') ? 'selected' : '' ?>>Femenino</option>
            </select>

            <label for="telefono">Teléfono</label>
            <input id="telefono" type="text" name="telefono" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['telefono']) ? $_SESSION['form_data']['telefono'] : ''; ?>">

            <label for="direccion">Dirección (opcional)</label>
            <input id="direccion" type="text" name="direccion" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['direccion']) ? $_SESSION['form_data']['direccion'] : ''; ?>">
        </div>

        <button type="submit">Registrarse</button>
        <a href="login.php" class="link">Volver a iniciar sesión</a>
    </form>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tipoUsuarioSelect = document.getElementById("tipoUsuario");
            const formDoctor = document.querySelector(".form-doctor");
            const formPaciente = document.querySelector(".form-paciente");

            tipoUsuarioSelect.addEventListener("change", function() {
                if (tipoUsuarioSelect.value === "2") {
                    formDoctor.style.display = "grid";
                } else {
                    formDoctor.style.display = "none";
                }

                if (tipoUsuarioSelect.value === "1") {
                    formPaciente.style.display = "grid";
                } else {
                    formPaciente.style.display = "none";
                }
            });
        });
    </script>
</body>
<?php
if (isset($_SESSION['error'])) {
    echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Error",
                        text: "' . $_SESSION['error'] . '",
                        icon: "error"
                    });
                });
            </script>';
    unset($_SESSION['error']);
} else if (isset($_SESSION['success'])) {
    echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Éxito",
                        text: "' . $_SESSION['success'] . '",
                        icon: "success"
                    });
                });
            </script>';
    unset($_SESSION['success']);
}
?>

</html>