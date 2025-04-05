<link rel="stylesheet" href="../css/modal-usuario.css">
<style>
    .form-doctor,
    .form-paciente {
        display: none;
        margin-top: 15px;
    }

    .autocomplete-container {
        position: relative;
    }

    .suggestions {
        position: absolute;
        width: 100%;
        border: 1px solid #ccc;
        border-top: none;
        background: white;
        max-height: 150px;
        overflow-y: auto;
        display: none;
    }

    .suggestions div {
        padding: 8px;
        cursor: pointer;
    }

    .suggestions div:hover {
        background-color: #f0f0f0;
    }
</style>
<div id="modalAgregarUsuario" class="modalAgregarUsuario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-user.php" method="POST">
            <div class="title">Nuevo <?php 
            if($paginaActual == 'usuarios') {
                echo 'Usuario';
            } else if($paginaActual == 'medicos') {
                echo 'Médico';
            } else if($paginaActual == 'pacientes') {
                echo 'Paciente';
            } else {
                echo 'Usuario';
            }
            ?></div>
            <div class="form-group">
                <label for="add-dni">DNI</label>
                <input id="add-dni" type="text" name="dni" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['dni']) ? $_SESSION['form_data']['dni'] : ''; ?>">

                <label for="add-nombre">Nombre</label>
                <input id="add-nombre" type="text" name="nombre" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['nombre']) ? $_SESSION['form_data']['nombre'] : ''; ?>">

                <label for="add-apellido">Apellido</label>
                <input id="add-apellido" type="text" name="apellido" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['apellido']) ? $_SESSION['form_data']['apellido'] : ''; ?>">

                <div id="camposNino">
                    <label for="es-nino">¿Es un paciente niño?</label>
                    <select id="es-nino" name="esNino">
                        <option value="no" <?= (isset($_SESSION['form_data']['esNino']) && $_SESSION['form_data']['esNino'] == 'no') ? 'selected' : '' ?>>No</option>
                        <option value="si" <?= (isset($_SESSION['form_data']['esNino']) && $_SESSION['form_data']['esNino'] == 'si') ? 'selected' : '' ?>>Sí</option>
                    </select>
                </div>

                <div id="camposUsuario" style="<?php
                                                if (isset($_SESSION['form_data']['esNino']) && $_SESSION['form_data']['esNino'] == 'si') {
                                                    echo 'display: none;';
                                                } else {
                                                    echo 'display: contents;';
                                                } ?>">
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
                    <input type="text" id="rol-tipoUsuario" name="rol-tipoUsuario" readonly="true" value="">
                </div>

            </div>

            <div class="form-doctor">
                <label for="add-idespecialidad">Especialidad</label>
                <select id="add-idespecialidad" name="idespecialidad">
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

                <label for="add-licenciaMedica">Nº Licencia Médica</label>
                <input id="add-licenciaMedica" type="text" name="licenciaMedica" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['licenciaMedica']) ? $_SESSION['form_data']['licenciaMedica'] : ''; ?>">

                <label for="add-aniosExperiencia">Años de Experiencia</label>
                <input id="add-aniosExperiencia" type="text" name="aniosExperiencia" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['aniosExperiencia']) ? $_SESSION['form_data']['aniosExperiencia'] : ''; ?>">

                <label for="add-telefonoMedico">Teléfono</label>
                <input id="add-telefonoMedico" type="text" name="telefonoMedico" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['telefonoMedico']) ? $_SESSION['form_data']['telefonoMedico'] : ''; ?>">
            </div>

            <div class="form-paciente">
                <label for="add-fechaNacimiento">Fecha de Nacimiento</label>
                <input id="add-fechaNacimiento" type="date" name="fechaNacimiento" value="<?php echo isset($_SESSION['form_data']['fechaNacimiento']) ? $_SESSION['form_data']['fechaNacimiento'] : ''; ?>">

                <label for="add-sexo">Sexo</label>
                <select id="add-sexo" name="sexo">
                    <option value="" <?= empty($_SESSION['form_data']['add-tipoUsuario']) ? 'selected' : '' ?>>Seleccionar</option>
                    <option value="Masculino" <?= (isset($_SESSION['form_data']['sexo']) && $_SESSION['form_data']['sexo'] == 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                    <option value="Femenino" <?= (isset($_SESSION['form_data']['sexo']) && $_SESSION['form_data']['sexo'] == 'Femenino') ? 'selected' : '' ?>>Femenino</option>
                </select>

                <div id="camposTutor" style="<?php
                                                if (isset($_SESSION['form_data']['esNino']) && $_SESSION['form_data']['esNino'] == 'si') {
                                                    echo 'display: contents;';
                                                } else {
                                                    echo 'display: none;';
                                                } ?>">
                    <label for="add-nombreTutor">Nombre del Tutor</label>
                    <div class="autocomplete-container">
                        <input id="add-nombreTutor" type="text" name="nombreTutor" placeholder="Buscar tutor..." autocomplete="off" value="<?php echo isset($_SESSION['form_data']['nombreTutor']) ? $_SESSION['form_data']['nombreTutor'] : ''; ?>">
                        <div id="suggestionAgregar" class="suggestions"></div>
                    </div>

                    <label for="add-idTutor" hidden>ID del Tutor</label>
                    <input id="add-idTutor" hidden type="text" name="idTutor" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['idTutor']) ? $_SESSION['form_data']['idTutor'] : ''; ?>" readonly="true">

                    <label for="add-dniTutor">DNI del Tutor</label>
                    <input id="add-dniTutor" type="text" name="dniTutor" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['dniTutor']) ? $_SESSION['form_data']['dniTutor'] : ''; ?>" readonly="true">
                </div>

                <label for="add-telefono">Teléfono</label>
                <input id="add-telefono" type="text" name="telefono" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['telefono']) ? $_SESSION['form_data']['telefono'] : ''; ?>">

                <label for="add-direccion">Dirección (opcional)</label>
                <input id="add-direccion" type="text" name="direccion" autocomplete="off" value="<?php echo isset($_SESSION['form_data']['direccion']) ? $_SESSION['form_data']['direccion'] : ''; ?>">
            </div>
            <button type="submit" class="modificar">Registrar <?php 
            if($paginaActual == 'usuarios') {
                echo 'Usuario';
            } else if($paginaActual == 'medicos') {
                echo 'Médico';
            } else if($paginaActual == 'pacientes') {
                echo 'Paciente';
            } else {
                echo 'Usuario';
            }
            ?></button>
        </form>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const tipoUsuarioSelect = document.getElementById("add-tipoUsuario");
                const rolTipoUsuario = document.getElementById("rol-tipoUsuario");
                const formDoctor = document.querySelector(".form-doctor");
                const formPaciente = document.querySelector(".form-paciente");
                const paginaActual = "<?php echo $paginaActual; ?>";

                if (paginaActual === "medicos") {
                    tipoUsuarioSelect.value = "2";
                    tipoUsuarioSelect.hidden = true;
                    rolTipoUsuario.value = "Médico";
                    rolTipoUsuario.hidden = false;
                    document.getElementById("camposNino").style.display = "none";

                } else if (paginaActual === "pacientes") {
                    tipoUsuarioSelect.value = "1";
                    tipoUsuarioSelect.hidden = true;
                    rolTipoUsuario.value = "Paciente";
                    rolTipoUsuario.hidden = false;
                    document.getElementById("camposNino").style.display = "contents";

                } else {
                    tipoUsuarioSelect.hidden = false;
                    rolTipoUsuario.value = "";
                    rolTipoUsuario.hidden = true;
                    document.getElementById("camposNino").style.display = "contents";
                }

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

            document.getElementById("es-nino").addEventListener("change", function() {
                let esNino = this.value === "si";
                document.getElementById("camposUsuario").style.display = esNino ? "none" : "contents";
                document.getElementById("camposTutor").style.display = esNino ? "contents" : "none";

                if ("<?php echo $paginaActual; ?>" === "pacientes") {
                    document.querySelector(".form-paciente").style.display = esNino ? "grid" : "grid";
                } else {
                    document.querySelector(".form-paciente").style.display = esNino ? "grid" : "none";
                }
            });

            // Limpiar campos de tutor si está vacío
            document.getElementById("add-nombreTutor").addEventListener("input", function() {
                const tutor = this.value;

                if (!tutor) {
                    document.getElementById("add-dniTutor").value = "";
                    document.getElementById("add-telefono").value = "";
                    document.getElementById("add-idTutor").value = "";
                }
            });

            // Lista dinamica de tutores
            document.addEventListener("DOMContentLoaded", function() {
                const inputAgregar = document.getElementById("add-nombreTutor");
                const suggestionsAgregar = document.getElementById("suggestionAgregar");

                inputAgregar.addEventListener("input", function() {
                    let query = this.value.trim();
                    suggestionsAgregar.innerHTML = "";

                    if (query.length === 0) {
                        suggestionsAgregar.style.display = "none";
                        return;
                    }

                    fetch("php/buscar-tutor.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "query=" + encodeURIComponent(query)
                        })
                        .then(response => response.json())
                        .then(data => {
                            suggestionsAgregar.innerHTML = "";
                            if (data.length > 0) {
                                suggestionsAgregar.style.display = "block";
                                data.forEach(tutor => {
                                    let div = document.createElement("div");
                                    div.textContent = tutor.Tutor;
                                    div.dataset.dni = tutor.dni;
                                    div.dataset.id = tutor.idResponsable;
                                    div.dataset.telefono = tutor.telefono;
                                    div.addEventListener("click", function() {
                                        inputAgregar.value = this.textContent;
                                        document.getElementById("add-dniTutor").value = this.dataset.dni;
                                        document.getElementById("add-telefono").value = this.dataset.telefono;
                                        document.getElementById("add-idTutor").value = this.dataset.id;
                                        suggestionsAgregar.style.display = "none";
                                    });
                                    suggestionsAgregar.appendChild(div);
                                });
                            } else {
                                suggestionsAgregar.style.display = "none";
                            }
                        })
                        .catch(error => console.error("Error:", error));
                });

                document.addEventListener("click", function(e) {
                    if (!document.querySelector(".autocomplete-container").contains(e.target)) {
                        suggestionsAgregar.style.display = "none";
                    }
                });
            });
        </script>
    </div>
</div>