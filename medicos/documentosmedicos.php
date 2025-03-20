<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php';

$paciente_filter = $_GET['paciente'] ?? '';
$cita_filter = $_GET['cita'] ?? '';
$medico_filter = $_GET['medico'] ?? '';
$tipo_filter = $_GET['tipo'] ?? '';
$fecha_filter = $_GET['fecha'] ?? '';

$sql = "SELECT  
    d.idDocumento,
    d.idPaciente, 
    CONCAT(u1.nombre, ' ', u1.apellido) AS paciente, 
    h.fecha as fechaCita, 
    d.IdMedico,
    Concat(u2.nombre, ' ', u2.apellido) as Medico, 
    d.tipoDocumento, 
    d.descripcion, 
    d.fechaSubida,
    c.idCita  
FROM DocumentosMedicos d
LEFT JOIN [dbo].[Pacientes] p ON d.idPaciente = p.idPaciente
LEFT JOIN Citas c ON d.idCita = c.idCita
LEFT JOIN HorariosMedicos h ON c.idHorario = h.idHorario
LEFT JOIN Medicos m ON h.idMedico = m.idMedico
LEFT JOIN [dbo].[Usuarios] u1 ON p.idUsuario = u1.idUsuario  
LEFT JOIN Usuarios u2 ON m.idUsuario = u2.idUsuario
WHERE 1=1";

if ($paciente_filter) {
    $sql .= " AND u1.nombre LIKE :paciente_filter";
}
if ($cita_filter) {
    $sql .= " AND h.idHorario = :cita_filter";
}
if ($medico_filter) {
    $sql .= " AND u2.nombre LIKE :medico_filter";
}
if ($tipo_filter) {
    $sql .= " AND d.tipoDocumento LIKE :tipo_filter";
}
if ($fecha_filter) {
    $sql .= " AND d.fechaSubida = :fecha_filter";
}

$stmt = $conn->prepare($sql);
if ($paciente_filter) {
    $stmt->bindValue(':paciente_filter', '%' . $paciente_filter . '%');
}
if ($cita_filter) {
    $stmt->bindValue(':cita_filter', $cita_filter);
}
if ($medico_filter) {
    $stmt->bindValue(':medico_filter', '%' . $medico_filter . '%');
}
if ($tipo_filter) {
    $stmt->bindValue(':tipo_filter', '%' . $tipo_filter . '%');
}
if ($fecha_filter) {
    $stmt->bindValue(':fecha_filter', $fecha_filter);
}
$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['export_pdf'])) {
    $html = "<h1>Lista de Documentos Médicos</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>Paciente</th>
                    <th>Cita</th>
                    <th>Médico</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Fecha Subida</th>
                </tr>
              </thead><tbody>";

    foreach ($documentos as $fila) {
        $html .= "<tr>
                    <td>{$fila['paciente']}</td>
                    <td>{$fila['fechaCita']} {$fila['horaCita']}</td>
                    <td>{$fila['Medico']}</td>
                    <td>{$fila['tipoDocumento']}</td>
                    <td>{$fila['descripcion']}</td>
                    <td>{$fila['fechaSubida']}</td>
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

    $dompdf->stream("documentos_medicos.pdf", array("Attachment" => true));
    exit;
}

if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Paciente');
    $sheet->setCellValue('B1', 'Cita');
    $sheet->setCellValue('C1', 'Médico');
    $sheet->setCellValue('D1', 'Tipo');
    $sheet->setCellValue('E1', 'Descripción');
    $sheet->setCellValue('F1', 'Fecha Subida');

    $row = 2;
    foreach ($documentos as $fila) {
        $sheet->setCellValue("A$row", $fila['paciente']);
        $sheet->setCellValue("B$row", $fila['fechaCita'] . ' ' . $fila['horaCita']);
        $sheet->setCellValue("C$row", $fila['Medico']);
        $sheet->setCellValue("D$row", $fila['tipoDocumento']);
        $sheet->setCellValue("E$row", $fila['descripcion']);
        $sheet->setCellValue("F$row", $fila['fechaSubida']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'documentos_medicos.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    $writer->save('php://output');
    exit;
}

if (isset($_GET['export_word'])) {
    try {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText("Lista de Documentos Médicos", ['bold' => true, 'size' => 16]);
        $table = $section->addTable();

        $table->addRow();
        $table->addCell(2000)->addText("Paciente");
        $table->addCell(2000)->addText("Cita");
        $table->addCell(2000)->addText("Médico");
        $table->addCell(2000)->addText("Tipo");
        $table->addCell(2000)->addText("Descripción");
        $table->addCell(2000)->addText("Fecha Subida");

        foreach ($documentos as $fila) {
            $table->addRow();
            $table->addCell(2000)->addText($fila['paciente']);
            $table->addCell(2000)->addText($fila['fechaCita'] . ' ' . $fila['horaCita']);
            $table->addCell(2000)->addText($fila['Medico']);
            $table->addCell(2000)->addText($fila['tipoDocumento']);
            $table->addCell(2000)->addText($fila['descripcion']);
            $table->addCell(2000)->addText($fila['fechaSubida']);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header("Content-Disposition: attachment;filename=\"documentos_medicos.docx\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    } catch (Exception $e) {
        echo 'Error al generar el documento Word: ',  $e->getMessage();
    }
}
$documentos = [];
$stmt = $conn->prepare($sql);
if ($paciente_filter) {
    $stmt->bindValue(':paciente_filter', '%' . $paciente_filter . '%');
}
if ($cita_filter) {
    $stmt->bindValue(':cita_filter', $cita_filter);
}
if ($medico_filter) {
    $stmt->bindValue(':medico_filter', '%' . $medico_filter . '%');
}
if ($tipo_filter) {
    $stmt->bindValue(':tipo_filter', '%' . $tipo_filter . '%');
}
if ($fecha_filter) {
    $stmt->bindValue(':fecha_filter', $fecha_filter);
}
$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <?php include 'modals/editar-documento.php'; ?>
            <?php include 'modals/agregar-documento.php'; ?>

            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>" autocomplete="off">
                    <input type="text" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>" autocomplete="off">
                    <input type="date" name="fecha" value="<?= $fecha_filter ?>">
                    <button type="submit">Filtrar</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Documentos Médicos</h2>
                <div class="export-buttons">
                    <a href="#" class="add-btn">Agregar Documento</a>
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
                                <th>Cita</th>
                                <th>Médico</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Fecha Subida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($documentos) {
                                foreach ($documentos as $fila) {
                                    echo "<tr>
                                        <td>{$fila['idDocumento']}</td>
                                        <td>{$fila['paciente']}</td>
                                        <td>{$fila['fechaCita']}</td>
                                        <td>{$fila['Medico']}</td>
                                        <td>{$fila['tipoDocumento']}</td>
                                        <td>{$fila['descripcion']}</td>
                                        <td>{$fila['fechaSubida']}</td>
                                        <td>
                                            <a href='#' class='edit-btn' 
                                                data-id='{$fila['idDocumento']}'
                                                data-idpaciente='{$fila['idPaciente']}'
                                                data-paciente='{$fila['paciente']}'
                                                data-idcita='{$fila['idCita']}'
                                                data-cita='{$fila['fechaCita']}'
                                                data-medico='{$fila['Medico']}'
                                                data-tipo='{$fila['tipoDocumento']}'
                                                data-descripcion='{$fila['descripcion']}'
                                                data-fecha='{$fila['fechaSubida']}'
                                                data-idmedico='{$fila['IdMedico']}'
                                            >
                                            <img src='../img/edit.png' width='35' height='35'>
                                            </a>
                                            <a href='#' class='delete-btn' data-id='{$fila['idDocumento']}'>
                                                <img src='../img/delete.png' width='35' height='35'>
                                            </a>
                                        </td>
                                    </tr>";
                                }
                            }

                            if (empty($documentos)) {
                                echo "<tr><td colspan='7'>No hay documentos medicos registrados</td></tr>";
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
    const modals = document.querySelectorAll(".modalAgregarDocumento, .modalEditarDocumento");
    const closeButtons = document.querySelectorAll(".close");
    const editButtons = document.querySelectorAll(".edit-btn");
    const addButtons = document.querySelectorAll(".add-btn");
    const deleteButtons = document.querySelectorAll(".delete-btn");

    addButtons.forEach(btn => {
        btn.addEventListener("click", function(event) {
            event.preventDefault();
            document.getElementById("modalAgregarDocumento").style.display = "block";
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

    editButtons.forEach(btn => {
    btn.addEventListener("click", function(event) {
        event.preventDefault();
        const idDocumento = btn.dataset.id;
        const idPaciente = btn.dataset.idpaciente; 
        const paciente = btn.dataset.paciente;
        const idCita = btn.dataset.idcita;  
        const cita = btn.dataset.cita;  
        const medico = btn.dataset.medico;
        const tipo = btn.dataset.tipo;
        const descripcion = btn.dataset.descripcion;
        const fecha = btn.dataset.fecha;
        const idMedico = btn.dataset.idmedico;

        document.getElementById("edit-idDocumento").value = idDocumento;
        document.getElementById("edit-idPaciente").value = idPaciente; 
        document.getElementById("edit-nombrePaciente").value = paciente;
        document.getElementById("edit-idCita").value = idCita; 
        document.getElementById("edit-fechaCita").value = cita;  
        document.getElementById("edit-tipoDocumento").value = tipo;
        document.getElementById("edit-descripcion").value = descripcion;
        document.getElementById("edit-fechaSubida").value = fecha;
        document.getElementById("edit-idMedico").value = idMedico;
        document.getElementById("edit-nombreMedico").value = medico;

        document.getElementById("modalEditarDocumento").style.display = "block";
    });
});

deleteButtons.forEach(btn => {
    btn.addEventListener("click", async event => {
        event.preventDefault();
        const idDocumento = btn.dataset.id;

        // Confirmación de eliminación
        const confirmacion = await Swal.fire({
            title: `¿Eliminar el documento Nº ${idDocumento}?`,
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
            
            const response = await fetch("php/delete-documento.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `idDocumento=${idDocumento}`
            });

            const data = await response.json();

            if (data.status === "success") {
                await Swal.fire({
                    title: "Éxito",
                    text: data.message,
                    icon: "success"
                });
                location.reload(); 
            } else {
                await Swal.fire({
                    title: "Error",
                    text: data.message,
                    icon: "error"
                });
            }
        } catch (error) {
            console.error("Error:", error);
            await Swal.fire({
                title: "Error",
                text: "Hubo un problema al eliminar el documento.",
                icon: "error"
            });
        }
    });
});
});

    </script>
</body>
<?php include 'alert.php'; ?>

</html>