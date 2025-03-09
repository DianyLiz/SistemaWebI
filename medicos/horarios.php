<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../php/vendor/autoload.php';
include '../conexion.php';
$paginaActual = 'horarios';


$mesActual = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anioActual = isset($_GET['anio']) ? $_GET['anio'] : date('Y');
$diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mesActual, $anioActual);

$sqlCupos = "SELECT H.fecha, 
       (H.cupos - COALESCE(COUNT(C.idCita), 0)) AS cuposDisponibles
FROM HorariosMedicos H
LEFT JOIN Citas C 
    ON H.idHorario = C.idHorario 
WHERE MONTH(H.fecha) = :mesActual 
AND YEAR(H.fecha) = :anioActual
GROUP BY H.fecha, H.cupos;
";

$queryCupos = $conn->prepare($sqlCupos);
$queryCupos->bindParam(':mesActual', $mesActual, PDO::PARAM_INT);
$queryCupos->bindParam(':anioActual', $anioActual, PDO::PARAM_INT);
$queryCupos->execute();
$cuposPorFecha = $queryCupos->fetchAll(PDO::FETCH_ASSOC);

$cuposDisponibles = [];
foreach ($cuposPorFecha as $row) {
    $cuposDisponibles[$row['fecha']] = $row['cuposDisponibles'];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios Médicos</title>

    <link rel="stylesheet" href="../css/tabla.css">
    <style>
        .schedule-container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin: 20px auto;
            max-width: 800px;
        }

        .calendar-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .day {
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .day:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .available {
            background: linear-gradient(135deg, #1D6E8E 0%, #2E8DEF 100%);
            color: white;
        }

        .few-cupos {
            background: linear-gradient(135deg, #FFA500 0%, #FF8B00 100%);
            color: white;
        }

        .unavailable {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            box-shadow: none;
        }

        .day h3 {
            margin: 0 0 5px 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .day small {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .month-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .month-selector select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        @media (max-width: 768px) {
            .calendar-container {
                grid-template-columns: repeat(7, 1fr);
            }

            .day {
                padding: 15px;
                min-height: 80px;
            }

            .day h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>

        <style>
            .filter-container {
                background: #ffffff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
                width: 100%;
                max-width: 1200px;
                margin: 20px auto;
            }

            .filter-container form {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .filter-container input,
            .filter-container select {
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-size: 14px;
                flex: 1;
                min-width: 150px;
                transition: border-color 0.3s ease;
            }

            .filter-container input:focus,
            .filter-container select:focus {
                border-color: #0099ff;
                outline: none;
            }

            .filter-container button {
                background-color: #0099ff;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 14px;
                transition: background-color 0.3s ease;
            }

            .filter-container button:hover {
                background-color: #0077cc;
            }

            @media (max-width: 768px) {
                .filter-container form {
                    flex-direction: column;
                }

                .filter-container input,
                .filter-container select,
                .filter-container button {
                    width: 100%;
                    margin-bottom: 10px;
                }
            }

            .add-btn,
            .btn-pdf,
            .btn-excel,
            .btn-word {
                display: inline-block;
                background-color: #0b5471;
                color: white;
                padding: 10px 20px;
                margin-right: 10px;
                margin-bottom: 10px;
                border-radius: 5px;
                text-decoration: none;
                font-size: 14px;
            }

            .add-btn:hover,
            .btn-pdf:hover,
            .btn-excel:hover,
            .btn-word:hover {
                background-color: #9bbdf0;
            }

            @media (max-width: 768px) {
                .export-buttons {
                    flex-direction: column;
                }

                .add-btn,
                .btn-pdf,
                .btn-excel,
                .btn-word {
                    width: 100%;
                    margin-right: 0;
                }
            }
        </style>
        <main class="contenido">
            <?php include 'modals/editar-horario.php'; ?>
            <?php include 'modals/agregar-horario.php'; ?>
            <div class="schedule-container">
                <h2>Horarios Médicos</h2>
                <div class="month-selector">
                    <select id="selectMes" onchange="cargarMes()">
                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                            <option value="<?php echo str_pad($m, 2, "0", STR_PAD_LEFT); ?>" <?php if ($m == $mesActual) echo 'selected'; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <select id="selectAnio" onchange="cargarMes()">
                        <?php for ($y = date('Y'); $y <= date('Y') + 2; $y++) { ?>
                            <option <?php if ($y == $anioActual) echo 'selected'; ?>><?php echo $y; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="calendar-container">
                    <?php
                    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                        $fecha = "$anioActual-$mesActual-" . str_pad($dia, 2, "0", STR_PAD_LEFT);
                        $cupos = isset($cuposDisponibles[$fecha]) ? $cuposDisponibles[$fecha] : 0;

                        if ($cupos > 5) {
                            $clase = "available";
                        } elseif ($cupos > 0) {
                            $clase = "few-cupos";
                        } else {
                            $clase = "unavailable";
                        }

                        echo "<div id='btnverhorarios' class='day $clase' onclick='verHorarios(\"$fecha\")'>
                                <h3>$dia</h3>
                                <small>$cupos cupos</small>
                              </div>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function verHorarios(fecha) {
            //window.location.href = "horarios.php?fecha=" + fecha;
        }

        function cargarMes() {
            const mes = document.getElementById('selectMes').value;
            const anio = document.getElementById('selectAnio').value;
            window.location.href = `horarios.php?mes=${mes}&anio=${anio}`;
        }
    </script>
    <script>
        const modals = document.querySelectorAll(".modalAgregarHorario, .modalEditarHorario");
        const estadocupo = <?php echo $cuposDisponibles[0]; ?>;
        const closeButtons = document.querySelectorAll(".close");
        const editButtons = document.querySelectorAll(".edit-btn");
        const addButtons = document.querySelectorAll("#btnverhorarios");
        const deleteButtons = document.querySelectorAll(".delete-btn");

        addButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();

                if(estadocupo > 1){
                    modalEditarHorario.style.display = "block";
                }
                else
                {
                    modalAgregarHorario.style.display = "block";
                }
                console.log(estadocupo);
            });
        });

        editButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                document.getElementById("edit-idHorario").value = this.dataset.idHorario;
                document.getElementById("edit-idmedico").value = this.dataset.idmedico;
                document.getElementById("edit-nombreMedico").value = this.dataset.medico;
                document.getElementById("edit-dia").value = this.dataset.dia;
                document.getElementById("edit-horaInicio").value = this.dataset.horaInicio;
                document.getElementById("edit-horaFin").value = this.dataset.horaFin;
                document.getElementById("edit-cupos").value = this.dataset.cupos;
                document.getElementById("edit-fecha").value = this.dataset.fecha;
                modalEditarHorario.style.display = "block";
            });
        });


        closeButtons.forEach(button => {
            button.addEventListener("click", function() {
                modals.forEach(modal => modal.style.display = "none");
            });
        });

        window.onclick = function(event) {
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        };

        deleteButtons.forEach(btn => {
            btn.addEventListener("click", async event => {
                event.preventDefault();
                const idHorario = btn.dataset.idhorario;
                const confirmacion = await Swal.fire({
                    title: `¿Eliminar el Horario Nº ${idHorario}?`,
                    text: "Esta acción no se puede deshacer.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Eliminar",
                    cancelButtonText: "Cancelar"
                });
                if (!confirmacion.isConfirmed) return;
                try {
                    const response = await fetch("php/delete-horario.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idHorario=${idHorario}`
                    });
                    const data = await response.json();
                    await Swal.fire({
                        title: data.status === "success" ? "Éxito" : "Error",
                        text: data.message,
                        icon: data.status === "success" ? "success" : "error"
                    });
                    if (data.status === "success") location.reload();
                } catch (error) {
                    Swal.fire({
                        title: "Error",
                        text: "Hubo un problema al eliminar el horario.",
                        icon: "error"
                    });
                    console.error("Error:", error);
                }
            });
        });
    </script>
</body>
<?php
include 'alert.php';
?>

</html>