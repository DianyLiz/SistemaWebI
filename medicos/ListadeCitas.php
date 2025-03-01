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
       Citas.idPaciente, U1.nombre + ' ' + U1.apellido AS paciente, 
       Citas.idMedico, U2.nombre + ' ' + U2.apellido AS medico, 
       Citas.fecha, 
       Convert (varchar(5), Citas.hora, 108) AS hora,
       Citas.motivo,
       Citas.estado
        FROM Citas 
        INNER JOIN Pacientes ON Citas.idPaciente = Pacientes.idPaciente
        INNER JOIN Usuarios U1 ON Pacientes.idUsuario = U1.idUsuario
        INNER JOIN Medicos ON Citas.idMedico = Medicos.idMedico
        INNER JOIN Usuarios U2 ON Medicos.idUsuario = U2.idUsuario
        WHERE 1=1";

if ($medico_filter) {
    $sql .= " AND U2.nombre LIKE '%$medico_filter%'";
}
if ($paciente_filter) {
    $sql .= " AND U1.nombre LIKE '%$paciente_filter%'";
}
if ($fecha_filter) {
    $sql .= " AND Citas.fecha = '$fecha_filter'";
}
if ($hora_filter) {
    $sql .= " AND Citas.hora = '$hora_filter'";
}
if ($motivo_filter) {
    $sql .= " AND Citas.hora = '$motivo_filter'";
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

            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>" autocomplete="off">
                    <input type="text" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>" autocomplete="off">
                    <input type="date" name="fecha" value="<?= $fecha_filter ?>">
                    <button type="submit">Filtrar</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Lista de Citas Médicas</h2>
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
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                        <td>{$fila['fecha']}</td>
                                        <td>{$hora_formateada}</td>
                                        <td>{$fila['motivo']}</td>
                                        <td class='$claseEstado'>{$fila['estado']}</td>
                                        <td>
                                            <a href='#' class='edit-btn' 
                                                data-idcita='{$fila['idCita']}'
                                                data-idpaciente='{$fila['idPaciente']}'
                                                data-paciente='{$fila['paciente']}'
                                                data-idmedico='{$fila['idMedico']}'
                                                data-medico='{$fila['medico']}'
                                                data-fecha='{$fila['fecha']}'
                                                data-hora='{$fila['hora']}'
                                                data-motivo='{$fila['motivo']}'
                                                data-estado='{$fila['estado']}'>
                                                <img src='../img/edit.png' width='35' height='35'>
                                               <a href='#' class='delete-btn' data-idcita='{$fila['idCita']}'>
                                                <img src='../img/delete.png' width='35' height='35'>
                                            </a>
                                            </a>

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
    <style>
        .add-btn,
        .btn-pdf,
        .btn-excel,
        .btn-word {
            display: inline-block;
            background-color:#0b5471;
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

    <script>
        const modals = document.querySelectorAll(".modalAgregarCita, .modalEditarCita");
        const closeButtons = document.querySelectorAll(".close");
        const editButtons = document.querySelectorAll(".edit-btn");
        const addButtons = document.querySelectorAll(".add-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

        addButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                modalAgregarCita.style.display = "block";
            });
        });

        editButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                document.getElementById("edit-idCita").value = this.dataset.idcita;
                document.getElementById("edit-idpaciente").value = this.dataset.idpaciente;
                document.getElementById("edit-nombrePaciente").value = this.dataset.paciente;
                document.getElementById("edit-idmedico").value = this.dataset.idmedico;
                document.getElementById("edit-nombreMedico").value = this.dataset.medico;
                document.getElementById("edit-fecha").value = this.dataset.fecha;
                document.getElementById("edit-hora").value = this.dataset.hora;
                document.getElementById("edit-motivo").value = this.dataset.motivo;
                document.getElementById("edit-estado").value = this.dataset.estado;
                console.log(this.dataset);
                modalEditarCita.style.display = "block";
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
    </script>
</body>
<?php
include 'alert.php';
?>

</html>