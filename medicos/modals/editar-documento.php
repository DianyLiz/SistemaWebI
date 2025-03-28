<style>
    .modal-content .tabla-container {
        display: none;
    }
</style>
<div id="modalEditarDocumento" class="modalEditarDocumento">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-documento.php" method="POST">
            <div class="title">Editar datos de Documentos Medicos</div>
            <div class="form-group">
                <label for="edit-idDocumento">ID Documento</label>
                <input id="edit-idDocumento" type="number" name="idDocumento" autocomplete="off" readonly>

                <label for="edit-idCita">ID Cita</label>
                <input id="edit-idCita" type="number" name="idCita" autocomplete="off" readonly>

                <label for="edit-fecha">Fecha de Cita</label>
                <input id="edit-fecha" onchange="obtenerCitasDisponibles()" type="date" name="fechaCita" autocomplete="off">
            </div>
            <div class="tabla-container" id="tablaCitasDisponiblesEditar">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>FECHA</th>
                                <th>PACIENTE</th>
                                <th>MEDICO</th>
                                <th>HORA</th>
                                <th>-</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label for="edit-dniPaciente">DNI Paciente</label>
                <input id="edit-dniPaciente" type="text" name="dniPaciente" autocomplete="off" readonly>

                <label for="edit-nombrePaciente">Paciente</label>
                <input id="edit-nombrePaciente" type="text" name="nombrePaciente" autocomplete="off" readonly>

                <label for="edit-dniMedico">DNI Médico</label>
                <input id="edit-dniMedico" type="text" name="dnimedico" autocomplete="off" readonly>

                <label for="edit-nombreMedico">Médico</label>
                <input id="edit-nombreMedico" type="text" name="nombreMedico" autocomplete="off" readonly>

                <label for="edit-tipoDocumento">Tipo de Documento</label>
                <select id="edit-tipoDocumento" name="tipoDocumento" required>
                    <option value="">Seleccionar</option>
                    <option value="Receta">Receta</option>
                    <option value="Constancia">Constancia</option>
                </select>

                <label for="edit-descripcion">Descripción</label>
                <textarea id="edit-descripcion" name="descripcion" rows="10" cols="50" style="resize: none;" required></textarea>

                <label for="edit-fechaSubida">Fecha de Subida</label>
                <input id="edit-fechaSubida" type="date" name="fechaSubida" autocomplete="off" readonly>
            </div>
            <button type="submit" class="modificar">Modificar Documento</button>
        </form>
    </div>
</div>
<script>
    function obtenerCitasDisponibles(origen) {
        const fecha = document.getElementById(origen === "editar" ? "edit-fecha" : "add-fecha").value;
        const tablaID = origen === "editar" ? "tablaCitasDisponiblesEditar" : "tablaCitasDisponiblesAgregar";
        const tablaContainer = document.getElementById(tablaID);

        console.log(`Obteniendo citas para: ${origen}, Fecha: ${fecha}`);

        if (!fecha) {
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", `php/obtener-citas.php?fecha=${fecha}`, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const respuesta = JSON.parse(xhr.responseText);
                console.log("Respuesta del servidor:", respuesta);

                if (respuesta.success && respuesta.citas.length > 0) {
                    actualizarTablaCitas(respuesta.citas, tablaID);
                    tablaContainer.style.display = "block";
                } else {
                    actualizarTablaCitas([], tablaID);
                    tablaContainer.style.display = "block";
                    //tablaContainer.style.display = "none";
                }
            }
        };
        xhr.send();
        document.getElementById("edit-dnimedico").value = "";
        document.getElementById("edit-medico").value = "";
        document.getElementById("edit-dnipaciente").value = "";
        document.getElementById("edit-paciente").value = "";
        document.getElementById("edit-idcita").value = "";
    }

    function actualizarTablaCitas(citas, tablaID) {
        const tbody = document.querySelector(`#${tablaID} tbody`);
        tbody.innerHTML = ""; // Limpiar la tabla

        if (citas.length > 0) {
            citas.forEach(citas => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
            <td>${citas.idCita}</td>
            <td>${citas.FechaAtencion}</td>
            <td>${citas.paciente}</td>
            <td>${citas.medico}</td>
            <td>${citas.hora}</td>
            <td>
                <a href="#" class="edit-btn"
                    data-idcita="${citas.idCita}"
                    data-dnimedico="${citas.dnimedico}"
                    data-medico="${citas.medico}"
                    data-dnipaciente="${citas.dnipaciente}"
                    data-paciente="${citas.paciente}">
                    <img src="../img/edit.png" width="35" height="35">
                </a>
            </td>
        `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='6' style='text-align:center;'>No hay médicos disponibles en este horario.</td></tr>";
        }
    }

    // Llamadas en los inputs de fecha
    document.getElementById("edit-fecha").addEventListener("change", () => obtenerCitasDisponibles("editar"));

    document.getElementById("edit-fecha").addEventListener("input", function() {
        const fecha = this.value;
        const tablaContainer = document.getElementById("tablaCitasDisponiblesEditar");

        if (!fecha) {
            // Ocultar la tabla
            tablaContainer.style.display = "none";
            document.getElementById("edit-dniMedico").value = "";
            document.getElementById("edit-nombreMedico").value = "";
            document.getElementById("edit-dniPaciente").value = "";
            document.getElementById("edit-nombrePaciente").value = "";
            document.getElementById("edit-idCita").value = "";
        }
    });

    document.addEventListener("click", function(event) {
        if (event.target.closest(".edit-btn")) {
            event.preventDefault();
            let btn = event.target.closest(".edit-btn");
            // Asignando valores
            document.getElementById("edit-dniMedico").value = btn.dataset.dnimedico;
            document.getElementById("edit-nombreMedico").value = btn.dataset.medico;
            document.getElementById("edit-dniPaciente").value = btn.dataset.dnipaciente;
            document.getElementById("edit-nombrePaciente").value = btn.dataset.paciente;
            document.getElementById("edit-idCita").value = btn.dataset.idcita;
        }
    });
</script>