<style>
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
<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalEditarPaciente" class="modalEditarPaciente">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-paciente.php" method="POST">
            <div class="title">Actualizar datos del Paciente</div>
            <div class="form-group">
                <label for="edit-idusuario">ID Usuario</label>
                <input id="edit-idusuario" type="text" name="idusuario" autocomplete="off" value="" readonly="true">

                <label for="edit-idpaciente">ID Paciente</label>
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

                <div id="camposTutorEditar" style="display: none;">
                    <label for="edit-nombreTutor">Nombre del Tutor</label>
                    <div class="autocomplete-container">
                        <input id="edit-nombreTutor" type="text" name="nombreTutor" placeholder="Buscar tutor..." autocomplete="off" value="">
                        <div id="suggestionEditar" class="suggestions"></div>
                    </div>

                    <label for="edit-idTutor">ID del Tutor</label>
                    <input id="edit-idTutor" type="text" name="idTutor" autocomplete="off" value="" readonly="true">

                    <label for="edit-dniTutor">DNI del Tutor</label>
                    <input id="edit-dniTutor" type="text" name="dniTutor" autocomplete="off" value="">
                </div>

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
<script>
    // Limpiar campos de tutor si está vacío
    document.getElementById("edit-nombreTutor").addEventListener("input", function() {
        const tutor = this.value;

        if (!tutor) {
            document.getElementById("edit-dniTutor").value = "";
            document.getElementById("edit-telefono").value = "";
            document.getElementById("edit-idTutor").value = "";
        }
    });

    // Lista dinamica de tutores
    document.addEventListener("DOMContentLoaded", function() {
        const inputEditar = document.getElementById("edit-nombreTutor");
        const suggestionsEditar = document.getElementById("suggestionEditar");

        inputEditar.addEventListener("input", function() {
            let query = this.value.trim();
            suggestionsEditar.innerHTML = "";

            if (query.length === 0) {
                suggestionsEditar.style.display = "none";
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
                    suggestionsEditar.innerHTML = "";
                    if (data.length > 0) {
                        suggestionsEditar.style.display = "block";
                        data.forEach(tutor => {
                            let div = document.createElement("div");
                            div.textContent = tutor.Tutor;
                            div.dataset.dni = tutor.dni;
                            div.dataset.id = tutor.idResponsable;
                            div.dataset.telefono = tutor.telefono;
                            div.addEventListener("click", function() {
                                inputEditar.value = this.textContent;
                                document.getElementById("edit-dniTutor").value = this.dataset.dni;
                                document.getElementById("edit-telefono").value = this.dataset.telefono;
                                document.getElementById("edit-idTutor").value = this.dataset.id;
                                suggestionsEditar.style.display = "none";
                            });
                            suggestionsEditar.appendChild(div);
                        });
                    } else {
                        suggestionsEditar.style.display = "none";
                    }
                })
                .catch(error => console.error("Error:", error));
        });

        document.addEventListener("click", function(e) {
            if (!document.querySelector(".autocomplete-container").contains(e.target)) {
                suggestionsEditar.style.display = "none";
            }
        });
    });
</script>