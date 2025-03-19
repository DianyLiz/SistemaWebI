<link rel="stylesheet" href="../css/modal-horario.css">
<div id="modalAgregarHorario" class="modalAgregarHorario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-horario.php" method="POST">
            <div class="title">Nuevo Horario</div>
            <div class="form-group"> 
                <label for="add-dnimedico">DNI Médico</label>
                <input type="text" name="dnimedico" id="add-dnimedico" autocomplete="off" required>

                <label for="add-idmedico" hidden>ID Médico</label>
                <input type="text" name="idmedico" id="add-idmedico" autocomplete="off" required readonly="true" hidden>
                
                <label for="add-medico">Médico</label>
                <input type="text" name="medico" id="add-medico" autocomplete="off" required readonly="true">

                <label for="add-fecha">Fecha</label>
                <input id="add-fecha" type="date" name="fecha" autocomplete="off" required readonly="true">

                <label for="add-diaSemana">Dia</label>
                <input type="text" name="diaSemana" id="add-diaSemana" autocomplete="off" required readonly="true">

                <label for="add-horainicio">Hora Inicio</label>
                <input id="add-horainicio" type="time" name="horainicio" autocomplete="off" required>

                <label for="add-horafin">Hora Fin</label>
                <input id="add-horafin" type="time" name="horafin" autocomplete="off" required>

                <label for="add-cupos">Cupos</label>
                <input id="add-cupos" type="text" name="cupos" autocomplete="off" required>
            </div>
            <div class="botones">
                <button type="submit" class="estilobotones" id="editarbtn" onclick="abrirModalDesdeAgregar()">Editar Horarios</button>
                <button type="submit" class="estilobotones" id="agregarbtn">Agregar Horario</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("add-dnimedico").addEventListener("input", function () {
        let dni = this.value.trim();
        if (dni.length > 0) {
            fetch("php/buscar-medico.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "query=" + encodeURIComponent(dni),
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("add-medico").value = data.nombre || "No encontrado";
                document.getElementById("add-idmedico").value = data.idMedico || "No encontrado";
            })
            .catch(error => console.error("Error:", error));
        } else {
            document.getElementById("add-medico").value = "";
            document.getElementById("add-idmedico").value = "";
        }
    });
});

function abrirModalDesdeAgregar() {
        const fecha = document.getElementById("edit-fecha").value;
        const dia = document.getElementById("edit-diaSemana").value;

        // Verificar si los elementos existen y tienen valores
        if (!fecha || !dia) {
            console.error("No se encontraron valores de fecha o día en modalAgregarHorario.");
            return;
        }

        // Cerrar modalEditarHorario
        const modalAgregar = document.getElementById("modalAgregarHorario");
        if (modalAgregar) {
            modalAgregar.style.display = "none";
        }

        const modalEditar = document.getElementById("modalEditarHorario");
        if (modalEditar) {
            modalEditar.style.display = "block";

            // Insertar valores en los inputs de modalAgregarHorario
            document.getElementById("add-fecha").value = fecha;
            document.getElementById("add-diaSemana").value = dia;
        }
    }
</script>