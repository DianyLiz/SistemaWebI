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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <?php include 'header.php'; include 'menu.php'; echo $alertScript; ?>

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
                    <p><strong>Duración:</strong> <span id="duracion-cita">60</span> minutos</p>
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

        $('.btn-seleccionar').click(function() {
            especialidadSeleccionada = $(this).data('especialidad');
            $('.table-container').fadeOut(300, function() {
                $('#cita-container').fadeIn(300);
            });
        });

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
                },
                error: function() {
                    $('#horarios-list').html('<tr><td colspan="4" class="error">Error al cargar horarios</td></tr>');
                }
            });
        }

        $(document).on('click', '.btn-reservar:not([disabled])', function() {
            $('.btn-reservar').removeClass('selected');
            $(this).addClass('selected');
            
            const horaInicio = $(this).data('hora-inicio');
            const medico = $(this).closest('tr').find('td:nth-child(2)').text();
            
            $('#hora-seleccionada').text(horaInicio);
            $('#medico-seleccionado').text(medico);
            $('#fecha-seleccionada').text(fechaSeleccionada);
            
            $('#motivo-cita').fadeIn();
        });

        $('#btn-regresar').click(function() {
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
        });

        $('#btn-confirmar').click(function() {
            const botonSeleccionado = $('.btn-reservar.selected');
            const motivo = $('#motivo').val().trim();
            const dni = $('#dni').val().trim();
            
            if(!botonSeleccionado.length) {
                Swal.fire('Error', 'Por favor selecciona un horario disponible', 'error');
                return;
            }

            if(!dni || !/^\d{13}$/.test(dni)) {
                Swal.fire('Error', 'Por favor ingresa un DNI válido de 13 dígitos', 'error');
                return;
            }
            
            if(!motivo) {
                Swal.fire('Error', 'Por favor describe el motivo de tu cita', 'error');
                return;
            }

            Swal.fire({
                title: 'Verificando disponibilidad',
                html: 'Por favor espera...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Verificación de disponibilidad
            $.ajax({
                url: 'verificarDisponibilidad.php',
                type: 'POST',
                data: {
                    fecha: fechaSeleccionada,
                    hora_inicio: botonSeleccionado.data('hora-inicio'),
                    id_medico: botonSeleccionado.data('id-medico'),
                    id_horario: botonSeleccionado.data('id-horario')
                },
                success: function(response) {
                    Swal.close();
                    
                    if(!response.disponible) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Horario no disponible',
                            text: response.mensaje || 'El horario seleccionado ya fue reservado',
                            willClose: () => { cargarHorariosDisponibles(); }
                        });
                        return;
                    }

                    // Confirmar reserva
                    confirmarReserva();
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo verificar la disponibilidad', 'error');
                }
            });
        });

        function confirmarReserva() {
            const botonSeleccionado = $('.btn-reservar.selected');
            const motivo = $('#motivo').val().trim();
            const dni = $('#dni').val().trim();

            Swal.fire({
                title: 'Confirmar Cita',
                html: `<div style="text-align:left;">
                    <p><strong>Fecha:</strong> ${fechaSeleccionada}</p>
                    <p><strong>Hora:</strong> ${botonSeleccionado.data('hora-inicio')}</p>
                    <p><strong>Médico:</strong> ${$('#medico-seleccionado').text()}</p>
                    <p><strong>DNI:</strong> ${dni}</p>
                    <p><strong>Motivo:</strong> ${motivo}</p>
                </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: 'InsertarCitas.php',
                            type: 'POST',
                            data: {
                                dni: dni,
                                motivo: motivo,
                                id_medico: botonSeleccionado.data('id-medico'),
                                id_horario: botonSeleccionado.data('id-horario'),
                                hora_inicio: botonSeleccionado.data('hora-inicio'),
                                fecha: fechaSeleccionada,
                                duracion: 60
                            },
                            success: function(response) {
                                if(response.estado === 'exito') {
                                    // Enviar correo de confirmación
                                    enviarCorreoConfirmacion(dni, fechaSeleccionada, 
                                        botonSeleccionado.data('hora-inicio'), 
                                        $('#medico-seleccionado').text(), motivo)
                                    .then(() => resolve())
                                    .catch(() => {
                                        Swal.showValidationMessage('Cita confirmada pero error al enviar correo');
                                        resolve(); // Resolver igual para no bloquear
                                    });
                                } else {
                                    Swal.showValidationMessage(response.mensaje || 'Error al confirmar la cita');
                                }
                            },
                            error: function() {
                                Swal.showValidationMessage('Error al conectar con el servidor');
                            }
                        });
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cita confirmada!',
                        html: 'Tu cita ha sido registrada exitosamente.<br>' + 
                              (result.value === 'mail_error' ? 
                               '<span style="color:orange;">Nota: Hubo un problema al enviar el correo de confirmación.</span>' : 
                               'Se ha enviado un correo de confirmación.'),
                        willClose: () => { location.reload(); }
                    });
                }
            });
        }

        function enviarCorreoConfirmacion(dni, fecha, hora, medico, motivo) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: 'confirmación.php',
                    type: 'POST',
                    data: {
                        dni: dni,
                        fecha: fecha,
                        hora: hora,
                        medico: medico,
                        motivo: motivo
                    },
                    success: function(response) {
                        if(response.estado === 'exito') {
                            resolve();
                        } else {
                            reject();
                        }
                    },
                    error: function() {
                        reject();
                    }
                });
            });
        }
    });
    </script>
</body>
</html>