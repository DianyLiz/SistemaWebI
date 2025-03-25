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
<div id="modalEditarHorario" class="modalEditarHorario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-horario.php" method="POST">
            <div class="title">Actualizar datos del Horario</div>
            <div class="tabla-container">
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
                <label for="edit-idHorario">ID Horario</label>
                <input id="edit-idHorario" type="text" name="idHorario" readonly>

                <label for="edit-dnimedico">DNI Médico</label>
                <input id="edit-dnimedico" type="text" name="dniMedico">

                <label for="edit-idmedico">ID Médico</label>
                <input id="edit-idmedico" type="text" name="idMedico">

                <label for="edit-buscarmedico">Médico</label>
                <div class="autocomplete-container">
                    <input type="text" id="edit-buscarmedico" name="nombreMedico" placeholder="Buscar médico..." autocomplete="off">
                    <div class="suggestions" id="suggestionsEditar"></div>
                </div>

                <label for="edit-fecha">Fecha</label>
                <input id="edit-fecha" type="date" name="fecha">

                <label for="edit-diaSemana">Día</label>
                <input id="edit-diaSemana" type="text" name="diaSemana" readonly>

                <label for="edit-hora">Hora Inicio</label>
                <input id="edit-hora" type="time" name="horaInicio">

                <label for="edit-fin">Hora Fin</label>
                <input id="edit-fin" type="time" name="horaFin">

                <label for="edit-cupos">Cupos</label>
                <input id="edit-cupos" type="number" name="cupos">
            </div>
            <div class="botones">
                <button type="submit" class="estilobotones">Modificar Horario</button>
                <button type="button" class="estilobotones" id="delete-btn">Eliminar Horario</button>
                <button type="button" class="estilobotones" onclick="abrirModalDesdeEditar()">Nuevo Horario</button>

            </div>
        </form>
    </div>
</div>
<script>
    function abrirModalDesdeEditar() {
        // Obtener los valores de fecha y día de la semana desde modalEditarHorario
        const fecha = document.getElementById("edit-fecha").value;
        const dia = document.getElementById("edit-diaSemana").value;

        // Verificar si los elementos existen y tienen valores
        if (!fecha || !dia) {
            console.error("No se encontraron valores de fecha o día en modalEditarHorario.");
            return;
        }

        // Cerrar modalEditarHorario
        const modalEditar = document.getElementById("modalEditarHorario");
        if (modalEditar) {
            modalEditar.style.display = "none";
        }

        // Abrir modalAgregarHorario y asignar valores
        const modalAgregar = document.getElementById("modalAgregarHorario");
        if (modalAgregar) {
            modalAgregar.style.display = "block";

            // Insertar valores en los inputs de modalAgregarHorario
            document.getElementById("add-fecha").value = fecha;
            document.getElementById("add-diaSemana").value = dia;
        }
    }

    function autocompletarCampos() {
        const editButtons = document.querySelectorAll(".edit-btn");
        editButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                document.getElementById("edit-idHorario").value = this.dataset.idhorario;
                document.getElementById("edit-dnimedico").value = this.dataset.dnimedico;
                document.getElementById("edit-idmedico").value = this.dataset.idmedico;
                document.getElementById("edit-buscarmedico").value = this.dataset.nombremedico;
                document.getElementById("edit-fecha").value = this.dataset.fecha;
                document.getElementById("edit-diaSemana").value = this.dataset.diasemana;
                document.getElementById("edit-hora").value = this.dataset.horainicio;
                document.getElementById("edit-fin").value = this.dataset.horafin;
                document.getElementById("edit-cupos").value = this.dataset.cupos;
            });
        });
    }

    const deleteButtons = document.querySelectorAll("#delete-btn");
    deleteButtons.forEach(btn => {
        btn.addEventListener("click", async event => {
            event.preventDefault();
            const idHorario = document.getElementById("edit-idHorario").value;
            if (!idHorario) {
                Swal.fire({
                    title: "Error",
                    text: "Seleccione un Horario Médico.",
                    icon: "error"
                });
            } else {
                const confirmacion = await Swal.fire({
                    title: `¿Eliminar el Horario Nº ${idHorario}?`,
                    text: "Esta acción no se puede deshacer.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Eliminar",
                    cancelButtonText: "Cancelar"
                });

                if (!confirmacion.isConfirmed) return;
                try {
                    const response = await fetch("php/delete-horario.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idHorario=${idHorario}`
                    });
                    const data = await response.json();
                    await Swal.fire({
                        title: data.status === "success" ? "Éxito" : "Error",
                        text: data.message,
                        icon: data.status === "success" ? "success" : "error"
                    });
                    if (data.status === "success") location.reload();
                } catch (error) {
                    Swal.fire({
                        title: "Error",
                        text: "Hubo un problema al eliminar el horario.",
                        icon: "error"
                    });
                    console.error("Error:", error);
                }
            }
        });
    });

    // Limpiar campos de medicos si está vacío
    document.getElementById("edit-buscarmedico").addEventListener("input", function() {
        const medico = this.value;

        if(!medico) {
            document.getElementById("edit-idmedico").value = "";
            document.getElementById("edit-dnimedico").value = "";
        }
    });

    // Lista dinamica de medicos
    document.addEventListener("DOMContentLoaded", function() {
        const inputEditar = document.getElementById("edit-buscarmedico");
        const suggestionsEditar = document.getElementById("suggestionsEditar");

        inputEditar.addEventListener("input", function() {
            let query = this.value.trim();
            suggestionsEditar.innerHTML = "";

            if (query.length === 0) {
                suggestionsEditar.style.display = "none";
                return;
            }

            fetch("php/buscar-medico.php", {
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
                        data.forEach(medico => {
                            let div = document.createElement("div");
                            div.textContent = medico.Medico;
                            div.dataset.id = medico.idMedico;
                            div.dataset.dni = medico.dni;
                            div.addEventListener("click", function() {
                                inputEditar.value = this.textContent;
                                document.getElementById("edit-idmedico").value = this.dataset.id;
                                document.getElementById("edit-dnimedico").value = this.dataset.dni;
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