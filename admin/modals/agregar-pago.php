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
<div id="modalAgregarPago" class="modalAgregarPago">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-pago.php" method="POST">
            <div class="title">Nuevo Pago de Cita</div>
            <div class="form-group">
                <label for="add-buscarpaciente">Paciente</label>
                <div class="autocomplete-container">
                    <input type="text" id="add-buscarpaciente" name="paciente" placeholder="Buscar paciente..." autocomplete="off" value="<?php if (isset($_SESSION['form_data']['paciente'])) echo $_SESSION['form_data']['paciente']; ?>" required>
                    <div class="suggestions" id="suggestionsAgregar"></div>
                </div>

                <label for="add-dnipaciente">DNI Paciente</label>
                <input type="text" name="dnipaciente" id="add-dnipaciente" autocomplete="off" value="<?php if (isset($_SESSION['form_data']['dnipaciente'])) echo $_SESSION['form_data']['dnipaciente']; ?>" required>

                <label for="add-fecha">Fecha</label>
                <input id="add-fecha" onchange="obtenerCitasDisponibles()" type="date" name="fecha" autocomplete="off" required value="<?php if (isset($_SESSION['form_data']['fecha'])) echo $_SESSION['form_data']['fecha']; ?>">
            </div>
            <div class="tabla-container" id="tablaCitasDisponiblesAgregar">
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
                <label for="add-idCita">Nº Cita</label>
                <input type="text" name="idCita" id="add-idCita" autocomplete="off" required readonly value="<?php if (isset($_SESSION['form_data']['idCita'])) echo $_SESSION['form_data']['idCita']; ?>">

                <label for="add-monto">Monto (Lps)</label>
                <input type="number" name="monto" id="add-monto" autocomplete="off" required value="<?php if (isset($_SESSION['form_data']['monto'])) echo $_SESSION['form_data']['monto']; ?>" min="0" step="0.01">

                <label for="add-metodoPago">Método de Pago</label>
                <select name="metodoPago" id="add-metodoPago" required>
                    <option value="" <?= empty($_SESSION['form_data']['metodoPago']) ? 'selected' : '' ?>>Seleccionar</option>
                    <option value="Efectivo" <?= (isset($_SESSION['form_data']['metodoPago']) && $_SESSION['form_data']['metodoPago'] == 'Efectivo') ? 'selected' : '' ?>>Efectivo</option>
                    <option value="Tarjeta" <?= (isset($_SESSION['form_data']['metodoPago']) && $_SESSION['form_data']['metodoPago'] == 'Tarjeta') ? 'selected' : '' ?>>Tarjeta de Crédito</option>
                    <option value="Transferencia" <?= (isset($_SESSION['form_data']['metodoPago']) && $_SESSION['form_data']['metodoPago'] == 'Transferencia') ? 'selected' : '' ?>>Transferencia</option>
                </select>
            </div>
            <button type="submit" class="modificar">Agregar Pago</button>
        </form>
    </div>
</div>
<script>
    document.getElementById("add-fecha").addEventListener("input", function() {
        const fecha = this.value;
        const tablaContainer = document.getElementById("tablaCitasDisponiblesAgregar");

        if (!fecha) {
            // Ocultar la tabla
            tablaContainer.style.display = "none";
            document.getElementById("add-idCita").value = "";
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
    document.getElementById("add-fecha").addEventListener("change", () => obtenerCitasDisponibles("agregar"));

    // Limpiar campos de pacientes si está vacío
    document.getElementById("add-buscarpaciente").addEventListener("input", function() {
        const paciente = this.value;

        if (!paciente) {
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
                            div.dataset.dni = paciente.dni;
                            div.addEventListener("click", function() {
                                inputAgregar.value = this.textContent;
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

            document.getElementById("add-idCita").value = btn.dataset.idcita;
        }
    });
</script>