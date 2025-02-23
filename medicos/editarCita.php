<?php
include 'header.php';
include '../conexion.php';

// Verificar si se recibe el parámetro 'id'
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p>Error: No se recibió la ID de la cita.</p>";
    exit;
}

$idCita = $_GET['id'];

// Obtener la cita de la base de datos
$sql = "SELECT * FROM Citas WHERE idCita = :idCita";
$query = $conn->prepare($sql);
$query->bindParam(':idCita', $idCita, PDO::PARAM_INT);
$query->execute();
$cita = $query->fetch(PDO::FETCH_ASSOC);

// Verificar si la cita existe
if (!$cita) {
    echo "<p>Error: La cita no existe.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas</title>
    <link rel="stylesheet" href="modals.css">
</head>
<body>
    <div class="modal" id="modal-editar-cita">
        <div class="modal-contenido">
            <div class="modal-cabecera">
                <h2>Editar Cita</h2>
                <span class="btn-cerrar-modal" id="btnCerrarModalEditarCita">&times;</span>
            </div>
            <div class="modal-cuerpo">
                <form id="formEditarCita">
                    <input type="hidden" name="idCita" id="idCita" value="<?php echo htmlspecialchars($cita['idCita']); ?>">
                    
                    <div class="campo-formulario">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" value="<?php echo htmlspecialchars($cita['fecha']); ?>">
                    </div>
                    
                    <div class="campo-formulario">
                        <label for="hora">Hora</label>
                        <input type="time" name="hora" id="hora" value="<?php echo htmlspecialchars($cita['hora']); ?>">
                    </div>
                    
                    <div class="campo-formulario">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado">
                            <option value="Pendiente" <?php if ($cita['estado'] === 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="Confirmada" <?php if ($cita['estado'] === 'Confirmada') echo 'selected'; ?>>Confirmada</option>
                            <option value="Cancelada" <?php if ($cita['estado'] === 'Cancelada') echo 'selected'; ?>>Cancelada</option>
                        </select>
                    </div>
                    
                    <div class="campo-formulario">
                        <label for="observaciones">Observaciones</label>
                        <textarea name="observaciones" id="observaciones"><?php echo htmlspecialchars($cita['observaciones']); ?></textarea>
                    </div>
                    
                    <div class="campo-formulario">
                        <button type="submit" class="btn-guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    // Enviar el formulario con Fetch
    const formEditarCita = document.getElementById('formEditarCita');
    formEditarCita.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(formEditarCita);
        fetch('editarCita.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar la cita en la tabla
                const citaRow = document.getElementById('cita-' + data.idCita);
                citaRow.querySelector('.fecha').textContent = data.fecha;
                citaRow.querySelector('.hora').textContent = data.hora;
                citaRow.querySelector('.estado').textContent = data.estado;
                citaRow.querySelector('.observaciones').textContent = data.observaciones;

                // Cerrar el modal
                const modalEditarCita = document.getElementById('modal-editar-cita');
                modalEditarCita.style.display = 'none';
            } else {
                alert(data.error);
            }
        })
        .catch(error => console.error('Error en Fetch:', error));
    });
    </script>
    <script>
        document.getElementById('btnEditarCita').addEventListener('click', function() {
            document.getElementById('modal-editar-cita').style.display = 'block';
        })
        
        document.getElementById('btnCerrarModalEditarCita').addEventListener('click', function() {
            document.getElementById('modal-editar-cita').style.display = 'none';
        })
    </script>

</body>
</html>