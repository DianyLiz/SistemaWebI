<link rel="stylesheet" href="../css/modal-cita.css">
<link rel="stylesheet" href="../css/modal-cita.css">

<div id="modalAgregarCita" class="modalAgregarCita" >
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-cita.php" method="POST">
            <div class="title">Nueva Cita</div>
            <div class="form-group">
                <label for="add-paciente">Paciente</label>
                <select id="add-paciente" name="paciente" required>
                <option value="">Seleccionar</option>
                    <?php
                    $sql = "SELECT Pacientes.idPaciente, Usuarios.nombre, Usuarios.apellido 
                    FROM Pacientes 
                    INNER JOIN Usuarios ON Pacientes.idUsuario = Usuarios.idUsuario";
            
            
                        $query = $conn->prepare($sql);
                        $query->execute();
                        $pacientes = $query->fetchAll(PDO::FETCH_ASSOC);
            
                        foreach ($pacientes as $paciente) {
                            echo "<option value='{$paciente['idPaciente']}'>{$paciente['nombre']} {$paciente['apellido']}</option>";
                        }
                    ?>
                </select>
                

                <label for="add-medico">Médico</label>
                <select id="add-medico" name="medico" required>
                    <option value="">Seleccionar</option>
                    <?php
                    $sql = "SELECT Medicos.idMedico, Usuarios.nombre, Usuarios.apellido 
                    FROM Medicos 
                    INNER JOIN Usuarios ON Medicos.idUsuario = Usuarios.idUsuario";
            
                    $query = $conn->prepare($sql);
                    $query->execute();
                    $medicos = $query->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($medicos as $medico) {
                        echo "<option value='{$medico['idMedico']}'>{$medico['nombre']} {$medico['apellido']}</option>";
                    }
                    ?>
                </select>

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
    var modal = document.getElementById("modalAgregarCita");

    var btn = document.getElementById("abrirModal");

    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>