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
            border: 3px solid rgba(0, 0, 0, .1);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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

        .btn-ocupado {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php include 'header.php';
    include 'menu.php';
    echo $alertScript; ?>

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
                            <td>' . htmlspecialchars($row["nombreEspecialidad"]) . '</td>
                            <td>' . htmlspecialchars($row["descripcion"]) . '</td>
                            <td><button class="btn-seleccionar" data-especialidad="' . htmlspecialchars($row["nombreEspecialidad"]) . '">Seleccionar</button></td>
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
                                <th>Horario de Atención</th>
                                <th>Duración</th>
                                <th>Médico</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="horarios-list"></tbody>
                    </table>
                </div>
            </div>

            <div id="motivo-cita" style="display:none; margin-top:20px;">
                <h3>DETALLES DE LA CITA</h3>
                <div class="form-group">
                    <label for="paciente">Paciente:</label>
                    <input type="text" id="paciente" value="<?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? '') ?> <?= htmlspecialchars($_SESSION['usuario']['apellido'] ?? '') ?>" readonly>

                    <label for="idpaciente">ID Paciente:</label>
                    <input type="text" id="idpaciente" value="<?= htmlspecialchars($_SESSION['usuario']['idPaciente'] ?? '') ?>" readonly>

                    <label for="dni">DNI Paciente:</label>
                    <input type="text" id="dni" name="dni" value="<?= htmlspecialchars($_SESSION['usuario']['dni'] ?? '') ?>" placeholder="Ingresa tu número de DNI">

                    <label for="motivo">Motivo:</label>
                    <textarea id="motivo" name="motivo" rows="10" cols="50" style="resize: none;" placeholder="Describe el motivo de tu cita" required></textarea>

                    <label for="idmedico">ID Médico:</label>
                    <input type="text" id="idmedico" name="idmedico" readonly>

                    <label for="medico">Médico:</label>
                    <input type="text" id="medico" readonly>

                    <label for="fecha">Fecha:</label>
                    <input type="text" id="fecha" name="fecha" readonly>

                    <label for="idhorario">ID Horario:</label>
                    <input type="text" id="idhorario" name="idhorario" readonly>

                    <label for="hora-atencion">Hora de atención:</label>
                    <input type="text" id="hora-atencion" name="hora-atencion" readonly>

                    <label for="hora-llegada">Hora de llegada:</label>
                    <input type="time" id="hora-llegada" name="hora-llegada">

                    <label for="duracion">Duración:</label>
                    <input type="text" id="duracion" name="duracion" value="60 minutos" readonly>


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

                    if (especialidadSeleccionada && fechaSeleccionada) {
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

                const horaAtencion = $(this).closest('tr').find('td:nth-child(1)').text();
                const medico = $(this).closest('tr').find('td:nth-child(3)').text();

                document.getElementById('idhorario').value = $(this).data('idhorario');
                document.getElementById('idmedico').value = $(this).data('idmedico');
                document.getElementById('hora-atencion').value = horaAtencion;
                document.getElementById('medico').value = medico;
                document.getElementById('fecha').value = fechaSeleccionada;
                document.getElementById('dni').value = <?= json_encode($_SESSION['usuario']['dni'] ?? '') ?>;

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
                const horallegada = $('#hora-llegada').val().trim();

                if (!botonSeleccionado.length) {
                    Swal.fire('Error', 'Por favor selecciona un horario disponible', 'error');
                    return;
                }

                if (!dni || !/^\d{13}$/.test(dni)) {
                    Swal.fire('Error', 'Por favor ingresa un DNI válido de 13 dígitos', 'error');
                    return;
                }

                if (!motivo) {
                    Swal.fire('Error', 'Por favor describe el motivo de tu cita', 'error');
                    return;
                }

                if (!horallegada) {
                    Swal.fire('Error', 'Por favor selecciona una hora de llegada', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Verificando disponibilidad',
                    html: 'Por favor espera...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Verificación de disponibilidad
                $.ajax({
                    url: 'verificarDisponibilidad.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        fecha: fechaSeleccionada,
                        hora_llegada: horallegada,
                        id_medico: botonSeleccionado.data('idmedico'),
                        id_horario: botonSeleccionado.data('idhorario')
                    },
                    success: function(response) {
                        console.log('Respuesta AJAX:', response);

                        Swal.close();
                        if (!response.disponible) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Horario no disponible',
                                text: response.mensaje || 'El horario seleccionado ya fue reservado',
                                willClose: () => {
                                    cargarHorariosDisponibles();
                                }
                            });
                            return;
                        }

                        // Confirmar reserva
                        confirmarReserva();

                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'No se pudo verificar la disponibilidad: ' + error, 'error');
                    }
                });
            });

            function confirmarReserva() {
                const botonSeleccionado = $('.btn-reservar.selected');
                const motivo = $('#motivo').val().trim();
                const horaLlegada = $('#hora-llegada').val().trim();
                const dni = $('#dni').val().trim();

                Swal.fire({
                    title: 'Confirmar Cita',
                    html: `<div style="text-align:left;">
                    <p><strong>Fecha:</strong> ${fechaSeleccionada}</p>
                    <p><strong>Hora:</strong> ${horaLlegada}</p>
                    <p><strong>Médico:</strong> ${botonSeleccionado.data('nombremedico')}</p>
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
                                dataType: 'json',
                                data: {
                                    dni: dni,
                                    motivo: motivo,
                                    id_medico: botonSeleccionado.data('idmedico'),
                                    id_horario: botonSeleccionado.data('idhorario'),
                                    hora_inicio: horaLlegada,
                                    fecha: fechaSeleccionada,
                                    duracion: 60
                                },
                                success: function(response) {
                                    if (response.estado === 'exito') {
                                        // Mensaje de confirmacion
                                        Swal.fire({
                                            icon: 'success',
                                            title: '¡Cita enviada correctamente!',
                                            html: 'Tu cita se encuentra pendiente de confirmación.',
                                            willClose: () => {
                                                location.reload();
                                            }
                                        });

                                    } else {
                                        Swal.showValidationMessage(response.mensaje || 'Error al confirmar la cita');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.showValidationMessage('Error al conectar con el servidor: ' + error);
                                    console.log(xhr, status, error);
                                }
                            });
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {

                    }
                });
            }

            function enviarCorreoConfirmacion(dni, fecha, hora, medico, motivo) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: 'enviarCorreoConfirmacion.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            dni: dni,
                            fecha: fecha,
                            hora: hora,
                            medico: medico,
                            motivo: motivo
                        },
                        success: function(response) {
                            if (response.estado === 'exito') {
                                resolve();
                            } else {
                                reject(response.mensaje || 'Error al enviar correo');
                            }
                        },
                        error: function(xhr, status, error) {
                            reject('Error de conexión: ' + error);
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>