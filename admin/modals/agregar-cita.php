<link rel="stylesheet" href="../css/modal-usuario.css">
<div id="modalAgregarCita" class="modalAgregarCita" >
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-cita.php" method="POST">
            <div class="title">Nueva Cita</div>
            <div class="form-group">
                <label for="add-dnipaciente">DNI Paciente</label>
                <input type="text" name="dnipaciente" id="add-dnipaciente" autocomplete="off" required>

                <label for="add-idpaciente" hidden>ID Paciente</label>
                <input type="text" name="idpaciente" id="add-idpaciente" autocomplete="off" required readonly="true" hidden>

                <label for="add-paciente">Paciente</label>
                <input type="text" name="paciente" id="add-paciente" autocomplete="off" required readonly="true">

                <label for="add-dnimedico">DNI Médico</label>
                <input type="text" name="dnimedico" id="add-dnimedico" autocomplete="off" required>

                <label for="add-idmedico" hidden>ID Médico</label>
                <input type="text" name="idmedico" id="add-idmedico" autocomplete="off" required readonly="true" hidden>
                
                <label for="add-medico">Médico</label>
                <input type="text" name="medico" id="add-medico" autocomplete="off" required readonly="true">

                <label for="add-fecha">Fecha</label>
                <input id="add-fecha" type="date" name="fecha" autocomplete="off" required>

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
            </div>
            <button type="submit" class="modificar">Agregar Cita</button>
        </form>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("add-dnipaciente").addEventListener("input", function () {
        let dni = this.value.trim();
        if (dni.length > 0) {
            fetch("php/buscar-paciente.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "query=" + encodeURIComponent(dni),
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("add-paciente").value = data.nombre || "No encontrado";
                document.getElementById("add-idpaciente").value = data.idPaciente || "No encontrado";
            })
            .catch(error => console.error("Error:", error));
        } else {
            document.getElementById("add-paciente").value = "";
            document.getElementById("add-idpaciente").value = "";
        }
    });

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
</script>
