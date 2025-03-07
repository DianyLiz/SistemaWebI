<link rel="stylesheet" href="../css/modal-horario.css">

<div id="modalAgregarHorario" class="modalAgregarHorario">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-horario.php" method="POST">
            <div class="title">Nuevo Horario</div>
            <div class="form-group"> 
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

                <label for="add-dia">Dia</label>
                <input id="add-dia" type="text" name="dia" autocomplete="off" required>

                <label for="add-hora">Hora Inicio</label>
                <input id="add-hora" type="time" name="hora" autocomplete="off" required>

                <label for="add-fin">Hora Fin</label>
                <input id="add-fin" type="time" name="fin" autocomplete="off" required>

                <label for="add-Cupos">Cupos</label>
                <input id="add-cupos" type="text" name="cupos" autocomplete="off" required>

                <label for="add-fecha">Fecha</label>
                <input id="add-fecha" type="date" name="fecha" autocomplete="off" required>
            </div>
            <button type="submit" class="modificar">Agregar Horario</button>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById("modalAgregarHorario");
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
