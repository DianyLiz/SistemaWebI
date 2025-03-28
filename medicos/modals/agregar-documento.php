<style>
    .modal-content .tabla-container {
        display: none;
    }
</style>
<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalAgregarDocumento" class="modalAgregarDocumento">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-documento.php" method="POST" enctype="multipart/form-data">
            <div class="title">Nuevo Documento Médico</div>
            <div class="form-group">
                <label for="add-fecha">Fecha</label>
                <input type="date" id="add-fecha" onchange="obtenerCitasDisponibles()" name="fecha" autocomplete="off" required>
            </div>
            <div class="tabla-container" id="tablaCitasDisponiblesAgregar">
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

                <label for="add-paciente">Paciente</label>
                <input type="text" id="add-paciente" name="paciente" autocomplete="off" required readonly>

                <label for="add-dnipaciente">DNI Paciente</label>
                <input type="text" id="add-dnipaciente" name="dniPaciente" autocomplete="off" required readonly>

                <label for="add-medico">Médico</label>
                <input type="text" id="add-medico" name="medico" autocomplete="off" required readonly>

                <label for="add-dnimedico">DNI Médico</label>
                <input type="text" id="add-dnimedico" name="dniMedico" autocomplete="off" required readonly>

                <label for="add-idcita">ID Cita</label>
                <input type="text" id="add-idcita" name="idCita" autocomplete="off" required readonly>

                <label for="add-tipoDocumento">Tipo de Documento</label>
                <select id="add-tipoDocumento" name="tipoDocumento" required>
                    <option value="">Seleccionar</option>
                    <option value="Receta">Receta</option>
                    <option value="Constancia">Constancia</option>
                </select>

                <label for="add-descripcion">Descripción</label>
                <textarea id="add-descripcion" name="descripcion" rows="10" cols="50" style="resize: none;" required></textarea>
            </div>
            <button type="submit" class="modificar">Agregar Documento</button>
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
        document.getElementById("add-dnimedico").value = "";
        document.getElementById("add-medico").value = "";
        document.getElementById("add-dnipaciente").value = "";
        document.getElementById("add-paciente").value = "";
        document.getElementById("add-idcita").value = "";
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
    document.getElementById("add-fecha").addEventListener("change", () => obtenerCitasDisponibles("agregar"));

    document.getElementById("add-fecha").addEventListener("input", function() {
        const fecha = this.value;
        const tablaContainer = document.getElementById("tablaCitasDisponiblesAgregar");

        if (!fecha) {
            // Ocultar la tabla
            tablaContainer.style.display = "none";
            document.getElementById("add-dnimedico").value = "";
            document.getElementById("add-medico").value = "";
            document.getElementById("add-dnipaciente").value = "";
            document.getElementById("add-paciente").value = "";
            document.getElementById("add-idcita").value = "";
        }
    });

    document.addEventListener("click", function(event) {
        if (event.target.closest(".edit-btn")) {
            event.preventDefault();
            let btn = event.target.closest(".edit-btn");

            document.getElementById("add-dnimedico").value = btn.dataset.dnimedico;
            document.getElementById("add-medico").value = btn.dataset.medico;
            document.getElementById("add-dnipaciente").value = btn.dataset.dnipaciente;
            document.getElementById("add-paciente").value = btn.dataset.paciente;
            document.getElementById("add-idcita").value = btn.dataset.idcita;
        }
    });
</script>
<?php
$conn = null;
?>