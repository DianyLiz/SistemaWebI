<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php';
$medico_filter = $_GET['medico'] ?? '';
$paciente_filter = $_GET['paciente'] ?? '';
$fecha_filter = $_GET['fecha'] ?? '';
$hora_filter = $_GET['hora'] ?? '';
$motivo_filter = $_GET['motivo'] ?? '';
$estado_filter = $_GET['estado'] ?? '';

$sql = "SELECT Citas.idCita,
       U1.dni AS dnipaciente, 
       Citas.idPaciente, U1.nombre + ' ' + U1.apellido AS paciente, 
       U2.dni AS dnimedico,
       Citas.idMedico, U2.nombre + ' ' + U2.apellido AS medico, 
       HorariosMedicos.fecha AS FechaAtencion, 
       CONVERT(VARCHAR, Citas.hora, 108) AS hora,
       Citas.motivo,
       Citas.estado,
       Citas.idHorario
        FROM Citas 
        INNER JOIN Pacientes ON Citas.idPaciente = Pacientes.idPaciente
        INNER JOIN Usuarios U1 ON Pacientes.idUsuario = U1.idUsuario
        INNER JOIN Medicos ON Citas.idMedico = Medicos.idMedico
        INNER JOIN Usuarios U2 ON Medicos.idUsuario = U2.idUsuario
		INNER JOIN HorariosMedicos ON Citas.idHorario = HorariosMedicos.idHorario
        WHERE 1=1";

if ($medico_filter) {
    $sql .= " AND U2.nombre LIKE '%$medico_filter%'";
}
if ($paciente_filter) {
    $sql .= " AND U1.nombre LIKE '%$paciente_filter%'";
}
if ($fecha_filter) {
    $sql .= " AND HorariosMedicos.fecha = '$fecha_filter'";
}
if ($hora_filter) {
    $sql .= " AND Citas.hora = '$hora_filter'";
}
if ($estado_filter) {
    $sql .= " AND Citas.estado = '$estado_filter'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $citas = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['ajax'])) {
    echo json_encode($citas);
    exit;
}

if (isset($_GET['export_pdf'])) {
    $html = "<h1>Lista de Citas Médicas</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                </tr>
              </thead><tbody>";

    foreach ($citas as $fila) {
        $hora_formateada = date("H:i", strtotime($fila['hora']));
        $html .= "<tr>
                    <td>{$fila['paciente']}</td>
                    <td>{$fila['medico']}</td>
                    <td>{$fila['fecha']}</td>
                    <td>{$hora_formateada}</td>
                    <td>{$fila['estado']}</td>
                  </tr>";
    }
    $html .= "</tbody></table>";

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("citas_medicas.pdf", array("Attachment" => false));
    exit;
}

if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Paciente');
    $sheet->setCellValue('B1', 'Médico');
    $sheet->setCellValue('C1', 'Fecha');
    $sheet->setCellValue('D1', 'Hora');
    $sheet->setCellValue('E1', 'Motivo');
    $sheet->setCellValue('F1', 'Estado');

    $row = 2;
    foreach ($citas as $fila) {
        $sheet->setCellValue("A$row", $fila['paciente']);
        $sheet->setCellValue("B$row", $fila['medico']);
        $sheet->setCellValue("C$row", $fila['fecha']);
        $sheet->setCellValue("D$row", $fila['hora']);
        $sheet->setCellValue("E$row", $fila['motivo']);
        $sheet->setCellValue("E$row", $fila['estado']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'citas_medicas.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    $writer->save('php://output');
    exit;
}

if (isset($_GET['export_word'])) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    $section->addText("Lista de Citas Médicas", ['bold' => true, 'size' => 16]);
    $table = $section->addTable();

    $table->addRow();
    $table->addCell(2000)->addText("Paciente");
    $table->addCell(2000)->addText("Médico");
    $table->addCell(2000)->addText("Fecha");
    $table->addCell(2000)->addText("Hora");
    $table->addCell(2000)->addText("Motivo");
    $table->addCell(2000)->addText("Estado");

    foreach ($citas as $fila) {
        $table->addRow();
        $table->addCell(2000)->addText($fila['paciente']);
        $table->addCell(2000)->addText($fila['medico']);
        $table->addCell(2000)->addText($fila['fecha']);
        $table->addCell(2000)->addText($fila['hora']);
        $table->addCell(2000)->addText($fila['motivo']);
        $table->addCell(2000)->addText($fila['estado']);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment;filename=\"citas_medicas.docx\"");
    $phpWord->save('php://output');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<style>
    .add-btn,
    .btn-pdf,
    .btn-excel,
    .btn-word {
        display: inline-block;
        background-color: #0b5471;
        color: white;
        margin-right: 10px;
        margin-bottom: 10px;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
    }

    .add-btn:hover,
    .btn-pdf:hover,
    .btn-excel:hover,
    .btn-word:hover {
        background-color: rgb(10, 60, 80);
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

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas</title>
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="../css/filter.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <main class="contenido">
            <?php include 'modals/editar-cita.php'; ?>
            <?php include 'modals/agregar-cita.php'; ?>
            <?php include 'alert.php'; ?>

            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" id="searchPaciente" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>" autocomplete="off">
                    <input type="text" id="searchMedico" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>" autocomplete="off">
                    <input type="date" id="searchFecha" name="fecha" value="<?= $fecha_filter ?>">
                </form>
            </div>

            <div class="table-container">
                <h2>LISTA DE CITAS MÉDICAS</h2>
                <div class="export-buttons">
                    <a href="#" class="add-btn">Agregar Cita</a>
                    <a href="?export_pdf" class="btn-pdf">Exportar a PDF</a>
                    <a href="?export_excel" class="btn-excel">Exportar a Excel</a>
                    <a href="?export_word" class="btn-word">Exportar a Word</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Paciente</th>
                                <th>Médico</th>
                                <th>Hora</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="citasTable">
                            <?php
                            $estadoClases = [
                                'Confirmada' => 'confirmed',
                                'Pendiente' => 'pending',
                                'Cancelada' => 'cancelled',
                            ];

                            if (count($citas) > 0) {
                                foreach ($citas as $fila) {
                                    $hora_formateada = date("H:i", strtotime($fila['hora']));
                                    $claseEstado = $estadoClases[$fila['estado']] ?? '';
                                    echo "<tr>
                                        <td>{$fila['idCita']}</td>
                                        <td>{$fila['paciente']}</td>
                                        <td>{$fila['medico']}</td>
                                        <td>{$hora_formateada}</td>
                                        <td>{$fila['motivo']}</td>
                                        <td class='$claseEstado'>{$fila['estado']}</td>
                                        <td>{$fila['FechaAtencion']}</td>
                                        <td>
                                            <a href='#' class='edit-btn'
                                                data-idcita='{$fila['idCita']}'
                                                data-idpaciente='{$fila['idPaciente']}'
                                                data-dnipaciente='{$fila['dnipaciente']}'
                                                data-paciente='{$fila['paciente']}'
                                                data-idmedico='{$fila['idMedico']}'
                                                data-dnimedico='{$fila['dnimedico']}'
                                                data-medico='{$fila['medico']}'
                                                data-fecha='{$fila['FechaAtencion']}'
                                                data-hora='{$fila['hora']}'
                                                data-motivo='{$fila['motivo']}'
                                                data-estado='{$fila['estado']}'
                                                data-idhorario='{$fila['idHorario']}'></a>
                                            <a href='#' class='delete-btn' data-idcita='{$fila['idCita']}'></a>

                                        </td>
                                      </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>No hay citas registradas</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
<script>
    // --------------------------------- Script para filtrar en tiempo real y actualizar la tabla dinamicamente ---------------------------------
    document.addEventListener("DOMContentLoaded", function() {
        const searchPaciente = document.getElementById("searchPaciente");
        const searchMedico = document.getElementById("searchMedico");
        const searchFecha = document.getElementById("searchFecha");
        const citasTable = document.getElementById("citasTable");

        window.fetchCitas = function() {
            const paciente = searchPaciente.value.trim();
            const medico = searchMedico.value.trim();
            const fecha = searchFecha.value.trim();

            const params = new URLSearchParams({
                paciente,
                medico,
                fecha,
                ajax: 1
            });

            fetch(`ListadeCitas.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    citasTable.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach(citas => {
                            const row = `
                    <tr>
                        <td>${citas.idCita}</td>
                        <td>${citas.paciente}</td>
                        <td>${citas.medico}</td>
                        <td>${citas.hora}</td>
                        <td>${citas.motivo}</td>
                        <td class="${citas.estado}">${citas.estado}</td>
                        <td>${citas.FechaAtencion}</td>
                        <td>
                            <a href="#" class="edit-btn" 
                                data-idcita="${citas.idCita}"
                                data-idpaciente="${citas.idPaciente}"
                                data-dnipaciente="${citas.dnipaciente}"
                                data-paciente="${citas.paciente}"
                                data-idmedico="${citas.idMedico}"
                                data-dnimedico="${citas.dnimedico}"
                                data-medico="${citas.medico}"
                                data-fecha="${citas.FechaAtencion}"
                                data-hora="${citas.hora}"
                                data-motivo="${citas.motivo}"
                                data-estado="${citas.estado}"
                                data-idhorario="${citas.idHorario}"></a>
                            <a href="#" class="delete-btn" data-idcita="${citas.idCita}"></a>
                        </td>
                    </tr>
                `;
                            citasTable.innerHTML += row;
                        });
                    } else {
                        citasTable.innerHTML = "<tr><td colspan='8'>No hay usuarios registrados</td></tr>";
                    }

                    // Vuelve a asignar eventos a los botones después de actualizar la tabla
                    asignarEventosBotones();
                })
                .catch(error => console.error("Error en la búsqueda:", error));
        }
        // Eventos para filtrar en tiempo real
        searchPaciente.addEventListener("keyup", fetchCitas);
        searchMedico.addEventListener("keyup", fetchCitas);
        searchFecha.addEventListener("change", fetchCitas);
    });
    // --------------------------------- Funcion para asignar eventos a los botones de editar y eliminar ---------------------------------
    function asignarEventosBotones() {
        const editButtons = document.querySelectorAll(".edit-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

        editButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                document.getElementById("edit-idCita").value = this.dataset.idcita;
                document.getElementById("edit-idpaciente").value = this.dataset.idpaciente;
                document.getElementById("edit-dnipaciente").value = this.dataset.dnipaciente;
                document.getElementById("edit-buscarpaciente").value = this.dataset.paciente;
                document.getElementById("edit-idmedico").value = this.dataset.idmedico;
                document.getElementById("edit-dnimedico").value = this.dataset.dnimedico;
                document.getElementById("edit-medico").value = this.dataset.medico;
                document.getElementById("edit-fecha").value = this.dataset.fecha;
                document.getElementById("edit-hora").value = this.dataset.hora;
                document.getElementById("edit-motivo").value = this.dataset.motivo;
                document.getElementById("edit-estado").value = this.dataset.estado;
                document.getElementById("edit-idhorario").value = this.dataset.idhorario;
                console.log(this.dataset);
                modalEditarCita.style.display = "block";
            });
        });

        deleteButtons.forEach(btn => {
            btn.addEventListener("click", async event => {
                event.preventDefault();
                const idCita = btn.dataset.idcita;
                const confirmacion = await Swal.fire({
                    title: `¿Eliminar la cita Nº ${idCita}?`,
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
                    const response = await fetch("php/delete-cita.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idCita=${idCita}`
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
                        text: "Hubo un problema al eliminar la cita.",
                        icon: "error"
                    });
                    console.error("Error:", error);
                }
            });
        });
    }
    // --------------------------------- Metodos para abrir y cerrar modales ---------------------------------
    const modals = document.querySelectorAll(".modalAgregarCita, .modalEditarCita");
    const closeButtons = document.querySelectorAll(".close");
    const addButtons = document.querySelectorAll(".add-btn");

    addButtons.forEach(btn => {
        btn.addEventListener("click", function(event) {
            event.preventDefault();
            modalAgregarCita.style.display = "block";
            document.getElementById("add-dnimedico").value = "";
            document.getElementById("add-idmedico").value = "";
            document.getElementById("add-medico").value = "";
            document.getElementById("add-idhorario").value = "";
        });
    });

    // Función para ocultar modales y resetear la tabla
    function cerrarModal() {
        modals.forEach(modal => {
            modal.style.display = "none";
        });

        // Ocultar las tablas de citas disponibles
        document.querySelectorAll(".tabla-container").forEach(tabla => {
            tabla.style.display = "none";
        });
    }

    closeButtons.forEach(button => {
        button.addEventListener("click", cerrarModal);
    });

    asignarEventosBotones();

    window.onclick = function(event) {
        modals.forEach(modal => {
            if (event.target == modal) {
                cerrarModal();
            }
        });
    };
</script>

</html>