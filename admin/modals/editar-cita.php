<link rel="stylesheet" href="../css/modal-cita.css">
<div id="modalEditarCita" class="modalEditarCita">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/edit-cita.php" method="POST">
            <div class="title">Actualizar datos de la Cita</div>
            <div class="form-group">
                <label for="edit-idCita">ID Cita</label>
                <input id="edit-idCita" type="text" name="idCita" autocomplete="off" readonly>

                <label for="edit-dnipaciente">DNI Paciente</label>
                <input id="edit-dnipaciente" type="text" name="dnipaciente" autocomplete="off">

                <label for="edit-idpaciente" hidden>ID Paciente</label>
                <input id="edit-idpaciente" type="text" name="idPaciente" autocomplete="off" readonly hidden>

                <label for="edit-paciente">Paciente</label>
                <input id="edit-paciente" type="text" name="paciente" autocomplete="off" readonly>

                <label for="edit-dnimedico">DNI Médico</label>
                <input id="edit-dnimedico" type="text" name="dnimedico" autocomplete="off">

                <label for="edit-idmedico" hidden>ID Medico</label>
                <input id="edit-idmedico" type="text" name="idMedico" autocomplete="off" readonly hidden>

                <label for="edit-medico">Médico</label>
                <input id="edit-medico" type="text" name="medico" autocomplete="off" readonly>

                <label for="edit-fecha">Fecha</label>
                <input id="edit-fecha" type="date" name="fecha" autocomplete="off">

                <label for="edit-hora">Hora</label>
                <input id="edit-hora" type="time" name="hora" autocomplete="off">

                <label for="edit-motivo">Motivo</label>
                <input id="edit-motivo" type="text" name="motivo" autocomplete="off">

                <label for="edit-estado">Estado</label>
                <select id="edit-estado" name="estado">
                    <option value="Pendiente">Pendiente</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>
            <button type="submit" class="modificar">Modificar Cita</button>
        </form>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("edit-dnipaciente").addEventListener("input", function () {
        let dni = this.value.trim();
        if (dni.length > 0) {
            fetch("php/buscar-paciente.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "query=" + encodeURIComponent(dni),
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("edit-paciente").value = data.nombre || "No encontrado";
                document.getElementById("edit-idpaciente").value = data.idPaciente || "No encontrado";
            })
            .catch(error => console.error("Error:", error));
        } else {
            document.getElementById("edit-paciente").value = "";
            document.getElementById("edit-idpaciente").value = "";
        }
    });

    document.getElementById("edit-dnimedico").addEventListener("input", function () {
        let dni = this.value.trim();
        if (dni.length > 0) {
            fetch("php/buscar-medico.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "query=" + encodeURIComponent(dni),
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("edit-medico").value = data.nombre || "No encontrado";
                document.getElementById("edit-idmedico").value = data.idMedico || "No encontrado";
            })
            .catch(error => console.error("Error:", error));
        } else {
            document.getElementById("edit-medico").value = "";
            document.getElementById("edit-idmedico").value = "";
        }
    });
});
</script>