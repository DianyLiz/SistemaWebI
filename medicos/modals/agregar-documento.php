<link rel="stylesheet" href="../css/modal-dmedicos.css">

<div id="modalAgregarDocumento" class="modalAgregarDocumento">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-documento.php" method="POST" enctype="multipart/form-data">
            <div class="title">Nuevo Documento Médico</div>
            <div class="form-group">
                <label for="add-paciente">Paciente</label>
                <select id="add-paciente" name="idPaciente" required>
                    <option value="">Seleccionar</option>
                    <?php
                    include '../../conexion.php';
                    $sql = "SELECT idPaciente, nombre, apellido 
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
                <select id="add-medico" name="idMedico" required>
                    <option value="">Seleccionar</option>
                    <?php
                    $sql = "SELECT idMedico, nombre, apellido 
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
        <label for="add-cita">Cita</label>
        <select id="add-cita" name="idCita" required>
            <option value="">Seleccionar</option>
            <?php
            try {
                $sql = "SELECT C.idCita , h.fecha FROM Citas C
                        inner JOIN HorariosMedicos h ON c.idHorario = h.idHorario"; 
                $query = $conn->prepare($sql);
                $query->execute();
                $citas = $query->fetchAll(PDO::FETCH_ASSOC);
                foreach ($citas as $cita) {
                    echo "<option value='{$cita['idCita']}'>{$cita['fecha']}</option>";
                }
            } catch (PDOException $e) {
                echo "<option value=''>Error al cargar citas</option>";
            }
            ?>
        </select>

                <label for="add-tipo">Tipo de Documento</label>
                <select id="add-tipo" name="tipoDocumento" required>
                    <option value="">Seleccionar</option>
                    <option value="Receta">Receta</option>
                    <option value="Constancia">Constancia</option>
                </select>

                <label for="add-descripcion">Descripción</label>
                <textarea id="add-descripcion" name="descripcion" autocomplete="off" required></textarea>

                <label for="add-fecha">Fecha de Subida</label>
                <input type="date" id="add-fecha" name="fechaSubida" autocomplete="off" required>
            </div>
            <button type="submit" class="modificar">Agregar Documento</button>
        </form>
    </div>
</div>

<script>
    var modal = document.getElementById("modalAgregarDocumento");
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
<?php
$conn = null;
?>