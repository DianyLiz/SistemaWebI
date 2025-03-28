<style>
    .modal-content .tabla-container {
        display: none;
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
<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalAgregarCita" class="modalAgregarCita">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-cita.php" method="POST">
            <div class="title">Nueva Cita</div>
            <div class="form-group">
                <label for="add-buscarpaciente">Paciente</label>
                <div class="autocomplete-container">
                    <input type="text" id="add-buscarpaciente" placeholder="Buscar paciente..." autocomplete="off">
                    <div class="suggestions" id="suggestionsAgregar"></div>
                </div>

                <label for="add-dnipaciente">DNI Paciente</label>
                <input type="text" name="dnipaciente" id="add-dnipaciente" autocomplete="off" required>

                <label for="add-idpaciente">ID Paciente</label>
                <input type="text" name="idpaciente" id="add-idpaciente" autocomplete="off" required readonly="true">

                <label for="add-fecha">Fecha</label>
                <input id="add-fecha" onchange="obtenerHorariosDisponibles()" type="date" name="fecha" autocomplete="off" required>
            </div>
            <div class="tabla-container" id="tablaHorariosDisponiblesAgregar">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>FECHA</th>
                                <th>DIA</th>
                                <th>MEDICO</th>
                                <th>HORARIO</th>
                                <th>CUPOS</th>
                                <th>-</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label for="add-dnimedico">DNI Médico</label>
                <input type="text" name="dnimedico" id="add-dnimedico" autocomplete="off" required>

                <label for="add-idmedico">ID Médico</label>
                <input type="text" name="idmedico" id="add-idmedico" autocomplete="off" required readonly="true">

                <label for="add-medico">Médico</label>
                <input type="text" name="medico" id="add-medico" autocomplete="off" required readonly="true">

                <label for="add-hora">Hora</label>
                <input id="add-hora" type="time" name="hora" autocomplete="off" required>

                <label for="add-motivo">Motivo</label>
                <input id="add-motivo" type="text" name="motivo" autocomplete="off" required>

                <label for="add-estado">Estado</label>
                <select id="add-estado" name="estado" required>
                    <option value="">Seleccionar</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Cancelada">Cancelada</option>
                </select>

                <label for="add-idhorario">ID Horario</label>
                <input id="add-idhorario" type="text" name="idHorario" autocomplete="off" required>
            </div>
            <button type="submit" class="modificar">Agregar Cita</button>
        </form>
    </div>
</div>
<script>
    document.getElementById("add-fecha").addEventListener("input", function() {
        const fecha = this.value;
        const tablaContainer = document.getElementById("tablaHorariosDisponiblesAgregar");

        if (!fecha) {
            // Ocultar la tabla
            tablaContainer.style.display = "none";

            // Limpiar los campos del médico
            document.getElementById("add-dnimedico").value = "";
            document.getElementById("add-idmedico").value = "";
            document.getElementById("add-medico").value = "";
            document.getElementById("add-idhorario").value = "";
        }
    });

    function obtenerHorariosDisponibles(origen) {
        const fecha = document.getElementById(origen === "editar" ? "edit-fecha" : "add-fecha").value;
        const tablaID = origen === "editar" ? "tablaHorariosDisponiblesEditar" : "tablaHorariosDisponiblesAgregar";
        const tablaContainer = document.getElementById(tablaID);

        console.log(`Obteniendo horarios para: ${origen}, Fecha: ${fecha}`);

        if (!fecha) {
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", `php/obtener-horarios.php?fecha=${fecha}`, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const respuesta = JSON.parse(xhr.responseText);
                console.log("Respuesta del servidor:", respuesta);

                if (respuesta.success && respuesta.horarios.length > 0) {
                    actualizarTablaHorarios(respuesta.horarios, tablaID);
                    tablaContainer.style.display = "block";
                } else {
                    actualizarTablaHorarios([], tablaID);
                    tablaContainer.style.display = "block";
                    //tablaContainer.style.display = "none";
                }
            }
        };
        xhr.send();
    }

    function actualizarTablaHorarios(horarios, tablaID) {
        const tbody = document.querySelector(`#${tablaID} tbody`);
        tbody.innerHTML = ""; // Limpiar la tabla

        if (horarios.length > 0) {
            horarios.forEach(horario => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
            <td>${horario.fecha}</td>
            <td>${horario.diaSemana}</td>
            <td>${horario.medico}</td>
            <td>${horario.HoraInicio} - ${horario.HoraFin}</td>
            <td>${horario.cupos}</td>
            <td>
                <a href="#" class="edit-btn"
                    data-idhorario="${horario.idHorario}"
                    data-dnimedico="${horario.DNIMedico}"
                    data-idmedico="${horario.idMedico}"
                    data-medico="${horario.medico}"></a>
            </td>
        `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='6' style='text-align:center;'>No hay médicos disponibles en este horario.</td></tr>";
        }
    }

    // Llamadas en los inputs de fecha
    document.getElementById("add-fecha").addEventListener("change", () => obtenerHorariosDisponibles("agregar"));

    // Limpiar campos de pacientes si está vacío
    document.getElementById("add-buscarpaciente").addEventListener("input", function() {
        const paciente = this.value;

        if(!paciente) {
            document.getElementById("add-idpaciente").value = "";
            document.getElementById("add-dnipaciente").value = "";
        }
    });

    // Lista dinamica de pacientes
    document.addEventListener("DOMContentLoaded", function() {
        const inputAgregar = document.getElementById("add-buscarpaciente");
        const suggestionsAgregar = document.getElementById("suggestionsAgregar");

        inputAgregar.addEventListener("input", function() {
            let query = this.value.trim();
            suggestionsAgregar.innerHTML = "";

            if (query.length === 0) {
                suggestionsAgregar.style.display = "none";
                return;
            }

            fetch("php/buscar-paciente.php", {
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
                        data.forEach(paciente => {
                            let div = document.createElement("div");
                            div.textContent = paciente.Paciente;
                            div.dataset.id = paciente.idPaciente;
                            div.dataset.dni = paciente.dni;
                            div.addEventListener("click", function() {
                                inputAgregar.value = this.textContent;
                                document.getElementById("add-idpaciente").value = this.dataset.id;
                                document.getElementById("add-dnipaciente").value = this.dataset.dni;
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

    document.addEventListener("click", function(event) {
        if (event.target.closest(".edit-btn")) {
            event.preventDefault();
            let btn = event.target.closest(".edit-btn");

            document.getElementById("add-dnimedico").value = btn.dataset.dnimedico;
            document.getElementById("add-idmedico").value = btn.dataset.idmedico;
            document.getElementById("add-medico").value = btn.dataset.medico;
            document.getElementById("add-idhorario").value = btn.dataset.idhorario;
        }
    });
</script>