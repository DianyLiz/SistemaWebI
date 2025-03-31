<?php
require_once 'session-control.php'; 
if (isset($_SESSION['alert_message'])) {
    $alertType = $_SESSION['alert_type'];
    $alertMessage = addslashes($_SESSION['alert_message']);
    $alertScript = <<<EOT
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: '$alertType',
            title: '$alertType' === 'success' ? 'Éxito' : 'Error',
            text: '$alertMessage',
            confirmButtonText: "Entendido"
        });
    });
    </script>
EOT;
    unset($_SESSION['alert_type']);
    unset($_SESSION['alert_message']);
} else {
    $alertScript = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/estilo-admin.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="../css/Reserva.css">
    <link rel="stylesheet" href="../css/modal-usuario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .detalles-horario {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .detalles-horario p {
            margin: 5px 0;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0,0,0,.1);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .error {
            color: #dc3545;
            text-align: center;
            padding: 20px;
        }
        .btn-reservar.selected {
            background-color: #28a745;
            color: white;
        }
        .no-horarios {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>

<?php
include 'header.php';
include 'menu.php';
echo $alertScript;
?>

<main class="contenido">

    <div class="table-container">
        <h2>Selecciona una Especialidad Para Tu Cita</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Seleccionar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include '../conexion.php';
                $sql = "SELECT * FROM Especialidades";
                $consulta = $conn->prepare($sql);
                $consulta->execute();
                while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>
                        <td>'.htmlspecialchars($row["nombreEspecialidad"]).'</td>
                        <td>'.htmlspecialchars($row["descripcion"]).'</td>
                        <td><button class="btn-seleccionar" data-especialidad="'.htmlspecialchars($row["nombreEspecialidad"]).'">Seleccionar</button></td>
                    </tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="cita-container" style="display:none;">
        <button id="btn-regresar" class="btn-cancelar">Regresar</button>
        <h3>Selecciona una Fecha Para Tu Cita</h3>
        <input type="text" id="fecha-cita" placeholder="Selecciona una fecha">

        <div id="horarios-disponibles" style="display:none; margin-top:20px;">
            <h3>Horarios Disponibles</h3>
            <div class="scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th>Hora Inicio</th>
                            <th>Médico</th>
                            <th>Duración</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="horarios-list"></tbody>
                </table>
            </div>
        </div>

        <div id="motivo-cita" style="display:none; margin-top:20px;">
            <h3>Motivo de la Cita</h3>
            <div class="form-group">
                <label for="dni">DNI:</label>
                <input type="text" id="dni" value="<?= htmlspecialchars($_SESSION['usuario']['dni'] ?? '') ?>" placeholder="Ingresa tu número de DNI">
            </div>
            <div class="form-group">
                <label for="motivo">Motivo:</label>
                <textarea id="motivo" placeholder="Describe el motivo de tu cita" required></textarea>
            </div>
            <h3>Detalles del Horario</h3>
            <div id="horario-seleccionado" class="detalles-horario">
                <p><strong>Fecha:</strong> <span id="fecha-seleccionada"></span></p>
                <p><strong>Hora:</strong> <span id="hora-seleccionada"></span></p>
                <p><strong>Médico:</strong> <span id="medico-seleccionado"></span></p>
                <p><strong>Duración:</strong> <span id="duracion-cita"></span> minutos</p>
            </div>
            <button id="btn-confirmar" class="btn-aceptar">Confirmar Cita</button>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let especialidadSeleccionada = null;
    let fechaSeleccionada = null;

    // Selección de especialidad
    $('.btn-seleccionar').click(function() {
        especialidadSeleccionada = $(this).data('especialidad');
        $('.table-container').fadeOut(300, function() {
            $('#cita-container').fadeIn(300);
        });
    });

    // Configuración del calendario
    flatpickr('#fecha-cita', {
        dateFormat: 'Y-m-d',
        minDate: 'today',
        locale: 'es',
        onChange: function(selectedDates, dateStr) {
            fechaSeleccionada = dateStr;
            $('#horarios-disponibles').hide();
            $('#motivo-cita').hide();
            
            if(especialidadSeleccionada && fechaSeleccionada) {
                cargarHorariosDisponibles();
            }
        }
    });

    // Función mejorada para cargar horarios
    function cargarHorariosDisponibles() {
        $('#horarios-list').html('<tr><td colspan="4"><div class="loading-spinner"></div> Cargando horarios...</td></tr>');
        $('#horarios-disponibles').show();

        $.ajax({
            url: 'obtenerHorarios.php',
            type: 'POST',
            data: {
                fecha: fechaSeleccionada,
                especialidad: especialidadSeleccionada
            },
            success: function(response) {
                $('#horarios-list').html(response);
                
                // Verificar si hay resultados
                if ($('#horarios-list tr').length === 1 && $('#horarios-list .no-horarios').length > 0) {
                    // No hay horarios disponibles (mensaje ya está incluido)
                } else if ($('#horarios-list tr').length === 0) {
                    $('#horarios-list').html('<tr><td colspan="4" class="error">No se encontraron horarios</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX:", status, error);
                $('#horarios-list').html('<tr><td colspan="4" class="error">Error al cargar horarios. Intente nuevamente.</td></tr>');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los horarios. Por favor, intente nuevamente.'
                });
            }
        });
    }

    // Selección de horario
    $(document).on('click', '.btn-reservar', function() {
        $('.btn-reservar').removeClass('selected');
        $(this).addClass('selected');
        
        const horaInicio = $(this).data('hora-inicio');
        const medico = $(this).closest('tr').find('td:nth-child(2)').text();
        const duracion = $(this).data('duracion') || 60;
        
        // Validar formato de hora
        if(!/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(horaInicio)) {
            console.error("Formato de hora inválido");
            return;
        }
        
        // Actualizar UI
        $('#hora-seleccionada').text(horaInicio);
        $('#fecha-seleccionada').text(fechaSeleccionada);
        $('#medico-seleccionado').text(medico);
        $('#duracion-cita').text(duracion);
        
        $('#motivo-cita').fadeIn();
        
        $('html, body').animate({
            scrollTop: $('#motivo-cita').offset().top - 20
        }, 300);
    });

    // Botón regresar
    $('#btn-regresar').click(function() {
        resetearProcesoReserva();
    });

    // Resetear proceso de reserva
    function resetearProcesoReserva() {
        $('#cita-container').fadeOut(300, function() {
            $('.table-container').fadeIn(300);
        });
        $('#fecha-cita').val('');
        $('#horarios-list').empty();
        $('#horarios-disponibles').hide();
        $('#motivo-cita').hide();
        $('.btn-reservar').removeClass('selected');
        especialidadSeleccionada = null;
        fechaSeleccionada = null;
    }

    // Confirmar cita
    $('#btn-confirmar').click(function() {
        const motivo = $('#motivo').val().trim();
        const dni = $('#dni').val().trim();
        const botonSeleccionado = $('.btn-reservar.selected');
        
        if(!botonSeleccionado.length) {
            Swal.fire('Error', 'Por favor selecciona un horario disponible', 'error');
            return;
        }
        
        if(!dni) {
            Swal.fire('Error', 'Por favor ingresa tu DNI', 'error');
            return;
        }

        if(!/^\d{13}$/.test(dni)) {
            Swal.fire('Error', 'El DNI debe tener 13 dígitos numéricos', 'error');
            return;
        }
        
        if(!motivo) {
            Swal.fire('Error', 'Por favor describe el motivo de tu cita', 'error');
            return;
        }
        
        // Mostrar carga mientras se verifica disponibilidad
        Swal.fire({
            title: 'Verificando disponibilidad',
            html: 'Por favor espera...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        verificarDisponibilidad().then(disponible => {
            Swal.close();
            
            if(!disponible) {
                Swal.fire({
                    icon: 'error',
                    title: 'Horario no disponible',
                    text: 'El horario seleccionado ya fue reservado. Por favor selecciona otro horario.',
                    willClose: () => {
                        resetearProcesoReserva();
                    }
                });
                return;
            }
            
            // Confirmar con todos los datos
            Swal.fire({
                title: 'Confirmar Cita',
                html: `
                    <div style="text-align:left;">
                        <p><strong>Fecha:</strong> ${fechaSeleccionada}</p>
                        <p><strong>Hora:</strong> ${botonSeleccionado.data('hora-inicio')}</p>
                        <p><strong>Médico:</strong> ${$('#medico-seleccionado').text()}</p>
                        <p><strong>Duración:</strong> ${$('#duracion-cita').text()} minutos</p>
                        <p><strong>DNI:</strong> ${dni}</p>
                        <p><strong>Motivo:</strong> ${motivo}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        enviarConfirmacionCita({
                            dni: dni,
                            motivo: motivo,
                            medico: botonSeleccionado.data('medico'),
                            horario: botonSeleccionado.data('horario'),
                            hora_inicio: botonSeleccionado.data('hora-inicio'),
                            fecha: fechaSeleccionada,
                            duracion: $('#duracion-cita').text()
                        }, resolve);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // La redirección se maneja en enviarConfirmacionCita
                }
            });
        }).catch(error => {
            Swal.fire('Error', 'Ocurrió un error al verificar la disponibilidad: ' + error.message, 'error');
        });
    });

    // Verificar disponibilidad del horario
    function verificarDisponibilidad() {
        const botonSeleccionado = $('.btn-reservar.selected');
        
        if (!botonSeleccionado.length) {
            return Promise.reject(new Error('No se ha seleccionado ningún horario'));
        }
        
        const horaInicio = botonSeleccionado.data('hora-inicio');
        
        if (!horaInicio) {
            return Promise.reject(new Error('No se encontró la hora de inicio'));
        }
        
        return $.ajax({
            url: 'verificarDisponibilidad.php',
            type: 'POST',
            data: {
                fecha: fechaSeleccionada,
                horario: botonSeleccionado.data('horario'),
                hora_inicio: horaInicio
            },
            dataType: 'json',
            timeout: 10000
        }).then(response => {
            if(response.error) {
                throw new Error(response.error);
            }
            return response.disponible;
        }).catch(error => {
            console.error("Error en verificación:", error);
            throw error;
        });
    }

    // Enviar confirmación de cita
    function enviarConfirmacionCita(datos, callback) {
        $.ajax({
            url: 'InsertarCitas.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            timeout: 15000
        }).then(response => {
            if(response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Cita confirmada!',
                    text: response.message,
                    willClose: () => {
                        if(response.redirect) {
                            window.location.href = response.redirect;
                        }
                    }
                });
            } else {
                throw new Error(response.message || 'Error al confirmar la cita');
            }
        }).catch(error => {
            Swal.fire('Error', error.message || 'No se pudo conectar con el servidor', 'error');
        }).always(() => {
            if(typeof callback === 'function') {
                callback();
            }
        });
    }

    // Prevenir pérdida de datos
    window.addEventListener('beforeunload', function(e) {
        if ($('#motivo-cita').is(':visible') && $('#motivo').val().trim() !== '') {
            e.preventDefault();
            e.returnValue = '¿Estás seguro de salir? Los datos no guardados se perderán.';
        }
    });
});
</script>

</body>
</html>