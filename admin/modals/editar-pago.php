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
<div id="modalEditarPago" class="modalEditarPago">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-pago.php" method="POST">
            <div class="title">Actualizar datos de Pago de Cita</div>
            <div class="form-group">
                <label for="edit-idPago">ID Pago</label>
                <input type="text" name="idpago" id="edit-idPago" autocomplete="off" value="" required>

                <label for="edit-buscarpaciente">Paciente</label>
                <div class="autocomplete-container">
                    <input type="text" id="edit-buscarpaciente" name="paciente" placeholder="Buscar paciente..." autocomplete="off" value="" required>
                    <div class="suggestions" id="suggestionsEditar"></div>
                </div>

                <label for="edit-dnipaciente">DNI Paciente</label>
                <input type="text" name="dnipaciente" id="edit-dnipaciente" autocomplete="off" value="" required>

                <label for="edit-fecha">Fecha</label>
                <input id="edit-fecha" onchange="obtenerCitasDisponibles()" type="date" name="fecha" autocomplete="off" required value="">
            </div>
            <div class="tabla-container" id="tablaCitasDisponiblesEditar">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nº CITA</th>
                                <th>FECHA</th>
                                <th>PACIENTE</th>
                                <th>MEDICO</th>
                                <th>HORA ATENCION</th>
                                <th>-</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-group">
                <label for="edit-idCita">Nº Cita</label>
                <input type="text" name="idCita" id="edit-idCita" autocomplete="off" required readonly value="">

                <label for="edit-monto">Monto (Lps)</label>
                <input type="number" name="monto" id="edit-monto" autocomplete="off" required value="">

                <label for="edit-metodoPago">Método de Pago</label>
                <select name="metodoPago" id="edit-metodoPago" required>
                    <option value="">Seleccionar</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta de Crédito</option>
                    <option value="Transferencia">Transferencia</option>
                </select>
            </div>
            <button type="submit" class="modificar">Agregar Pago</button>
        </form>
    </div>
</div>
<script>
    document.getElementById("edit-fecha").addEventListener("input", function() {
        const fecha = this.value;
        const tablaContainer = document.getElementById("tablaCitasDisponiblesEditar");

        if (!fecha) {
            // Ocultar la tabla
            tablaContainer.style.display = "none";
            document.getElementById("edit-idCita").value = "";
        }
    });

    function obtenerCitasDisponibles(origen) {
        const dnipaciente = document.getElementById(origen === "editar" ? "edit-dnipaciente" : "add-dnipaciente").value;
        const fecha = document.getElementById(origen === "editar" ? "edit-fecha" : "add-fecha").value;
        const tablaID = origen === "editar" ? "tablaCitasDisponiblesEditar" : "tablaCitasDisponiblesAgregar";
        const tablaContainer = document.getElementById(tablaID);

        if (!fecha || !dnipaciente) {
            console.error("Faltan parámetros: fecha o paciente");
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("GET", `php/buscar-cita.php?dnipaciente=${encodeURIComponent(dnipaciente)}&fecha=${fecha}`, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const respuesta = JSON.parse(xhr.responseText);
                console.log("Respuesta del servidor:", respuesta);

                if (respuesta.length > 0) { // Ahora directamente usa el array
                    actualizarTablaCitas(respuesta, tablaID);
                    tablaContainer.style.display = "block";
                } else {
                    actualizarTablaCitas([], tablaID);
                    tablaContainer.style.display = "block";
                }
            }
        };
        xhr.send();
    }

    function formatearHora(hora) {
        return hora.split(".")[0]; // Quita los milisegundos
    }

    function actualizarTablaCitas(citas, tablaID) {
        const tbody = document.querySelector(`#${tablaID} tbody`);
        tbody.innerHTML = "";

        if (citas.length > 0) {
            citas.forEach(cita => {
                console.log("Cita recibida:", cita);
                const tr = document.createElement("tr");
                tr.innerHTML = `
                <td>${cita.idCita}</td>
                <td>${cita.fecha}</td>
                <td>${cita.Paciente}</td>
                <td>${cita.Medico}</td>
                <td>${formatearHora(cita.HoraAtencion)}</td> <!-- Formatear hora -->
                <td>
                    <a href="#" class="edit-btn" data-idcita="${cita.idCita}"></a>
                </td>
            `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='6' style='text-align:center;'>No hay citas disponibles para este paciente.</td></tr>";
        }
    }

    // Llamadas en los inputs de fecha
    document.getElementById("edit-fecha").addEventListener("change", () => obtenerCitasDisponibles("editar"));

    // Limpiar campos de pacientes si está vacío
    document.getElementById("edit-buscarpaciente").addEventListener("input", function() {
        const paciente = this.value;

        if (!paciente) {
            document.getElementById("edit-dnipaciente").value = "";
        }
    });

    // Lista dinamica de pacientes
    document.addEventListener("DOMContentLoaded", function() {
        const inputEditar = document.getElementById("edit-buscarpaciente");
        const suggestionsEditar = document.getElementById("suggestionsEditar");

        inputEditar.addEventListener("input", function() {
            let query = this.value.trim();
            suggestionsEditar.innerHTML = "";

            if (query.length === 0) {
                suggestionsEditar.style.display = "none";
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
                    suggestionsEditar.innerHTML = "";
                    if (data.length > 0) {
                        suggestionsEditar.style.display = "block";
                        data.forEach(paciente => {
                            let div = document.createElement("div");
                            div.textContent = paciente.Paciente;
                            div.dataset.dni = paciente.dni;
                            div.addEventListener("click", function() {
                                inputEditar.value = this.textContent;
                                document.getElementById("edit-dnipaciente").value = this.dataset.dni;
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

    document.addEventListener("click", function(event) {
        if (event.target.closest(".edit-btn")) {
            event.preventDefault();
            let btn = event.target.closest(".edit-btn");

            document.getElementById("edit-idCita").value = btn.dataset.idcita;
        }
    });
</script>