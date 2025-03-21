<link rel="stylesheet" href="../css/modal-expedientes.css">

<div id="modalAgregarExpediente" class="modalAgregarExpediente">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form action="php/add-expediente.php" method="POST" enctype="multipart/form-data">
            <div class="title">Nuevo Expediente Médico</div>
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

                <label for="add-fecha-creacion">Fecha de Creación</label>
                <input type="date" id="add-fecha-creacion" name="fechaCreacion" autocomplete="off" required>

                <label for="add-antecedentes">Antecedentes</label>
                <textarea id="add-antecedentes" name="antecedentes" autocomplete="off" required></textarea>

                <label for="add-alergias">Alergias</label>
                <textarea id="add-alergias" name="alergias" autocomplete="off" required></textarea>

                <label for="add-medicamentos">Medicamentos Actuales</label>
                <textarea id="add-medicamentos" name="medicamentosActuales" autocomplete="off" required></textarea>

                <label for="add-enfermedades">Enfermedades Crónicas</label>
                <textarea id="add-enfermedades" name="enfermedadesCronicas" autocomplete="off" required></textarea>

                <label for="add-descripcion">Descripción</label>
                <textarea id="add-descripcion" name="descripcion" autocomplete="off" required></textarea>

                <label for="add-fecha-actualizacion">Fecha de Actualización</label>
                <input type="date" id="add-fecha-actualizacion" name="fechaActualizacion" autocomplete="off" required>
            </div>
            <button type="submit" class="modificar">Agregar Expediente</button>
        </form>
    </div>
</div>

<script>
   
    var modal = document.getElementById("modalAgregarExpediente");
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