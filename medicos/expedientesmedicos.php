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
    e.IdExpediente,
    e.idPaciente, 
    CONCAT(u1.nombre, ' ', u1.apellido) AS paciente, 
    e.FechaCreacion,
    e.Antecedentes,
    e.Alergias,
    e.MedicamentosActuales,
    e.EnfermedadesCronicas,
    e.Descripcion,
    e.FechaActualizacion
FROM ExpedienteMedico e
LEFT JOIN [dbo].[Pacientes] p ON e.idPaciente = p.idPaciente
LEFT JOIN [dbo].[Usuarios] u1 ON p.idUsuario = u1.idUsuario  
WHERE 1=1";

if ($paciente_filter) {
    $sql .= " AND u1.nombre LIKE :paciente_filter";
}
if ($cita_filter) {
    $sql .= " AND e.IdExpediente = :cita_filter"; 
}
if ($medico_filter) {
    $sql .= " AND u1.nombre LIKE :medico_filter"; 
}
if ($tipo_filter) {
    $sql .= " AND e.Descripcion LIKE :tipo_filter"; 
}
if ($fecha_filter) {
    $sql .= " AND e.FechaCreacion = :fecha_filter"; 
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
    $html = "<h1>Lista de Expedientes Médicos</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>Paciente</th>
                    <th>Fecha Creación</th>
                    <th>Antecedentes</th>
                    <th>Alergias</th>
                    <th>Medicamentos Actuales</th>
                    <th>Enfermedades Crónicas</th>
                    <th>Descripción</th>
                    <th>Fecha Actualización</th>
                </tr>
              </thead><tbody>";

    foreach ($documentos as $fila) {
        $html .= "<tr>
                    <td>{$fila['paciente']}</td>
                    <td>{$fila['FechaCreacion']}</td>
                    <td>{$fila['Antecedentes']}</td>
                    <td>{$fila['Alergias']}</td>
                    <td>{$fila['MedicamentosActuales']}</td>
                    <td>{$fila['EnfermedadesCronicas']}</td>
                    <td>{$fila['Descripcion']}</td>
                    <td>{$fila['FechaActualizacion']}</td>
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

    $dompdf->stream("expedientes_medicos.pdf", array("Attachment" => true));
    exit;
}

if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Paciente');
    $sheet->setCellValue('B1', 'Fecha Creación');
    $sheet->setCellValue('C1', 'Antecedentes');
    $sheet->setCellValue('D1', 'Alergias');
    $sheet->setCellValue('E1', 'Medicamentos Actuales');
    $sheet->setCellValue('F1', 'Enfermedades Crónicas');
    $sheet->setCellValue('G1', 'Descripción');
    $sheet->setCellValue('H1', 'Fecha Actualización');

    $row = 2;
    foreach ($documentos as $fila) {
        $sheet->setCellValue("A$row", $fila['paciente']);
        $sheet->setCellValue("B$row", $fila['FechaCreacion']);
        $sheet->setCellValue("C$row", $fila['Antecedentes']);
        $sheet->setCellValue("D$row", $fila['Alergias']);
        $sheet->setCellValue("E$row", $fila['MedicamentosActuales']);
        $sheet->setCellValue("F$row", $fila['EnfermedadesCronicas']);
        $sheet->setCellValue("G$row", $fila['Descripcion']);
        $sheet->setCellValue("H$row", $fila['FechaActualizacion']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'expedientes_medicos.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}

if (isset($_GET['export_word'])) {
    try {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText("Lista de Expedientes Médicos", ['bold' => true, 'size' => 16]);
        $table = $section->addTable();

        $table->addRow();
        $table->addCell(2000)->addText("Paciente");
        $table->addCell(2000)->addText("Fecha Creación");
        $table->addCell(2000)->addText("Antecedentes");
        $table->addCell(2000)->addText("Alergias");
        $table->addCell(2000)->addText("Medicamentos Actuales");
        $table->addCell(2000)->addText("Enfermedades Crónicas");
        $table->addCell(2000)->addText("Descripción");
        $table->addCell(2000)->addText("Fecha Actualización");

        foreach ($documentos as $fila) {
            $table->addRow();
            $table->addCell(2000)->addText($fila['paciente']);
            $table->addCell(2000)->addText($fila['FechaCreacion']);
            $table->addCell(2000)->addText($fila['Antecedentes']);
            $table->addCell(2000)->addText($fila['Alergias']);
            $table->addCell(2000)->addText($fila['MedicamentosActuales']);
            $table->addCell(2000)->addText($fila['EnfermedadesCronicas']);
            $table->addCell(2000)->addText($fila['Descripcion']);
            $table->addCell(2000)->addText($fila['FechaActualizacion']);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header("Content-Disposition: attachment;filename=\"expedientes_medicos.docx\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    } catch (Exception $e) {
        echo 'Error al generar el documento Word: ',  $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Expedientes Médicos</title>
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="../css/filter.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <main class="contenido">
            <?php include 'modals/editar-expediente.php'; ?>
            <?php include 'modals/agregar-expediente.php'; ?>

            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>" autocomplete="off">
                    <input type="text" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>" autocomplete="off">
                    <input type="date" name="fecha" value="<?= $fecha_filter ?>">
                    <button type="submit">Filtrar</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Expedientes Médicos</h2>
                <div class="export-buttons">
                    <a href="#" class="add-btn">Agregar Expediente</a>
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
                                <th>Fecha Creación</th>
                                <th>Antecedentes</th>
                                <th>Alergias</th>
                                <th>Medicamentos Actuales</th>
                                <th>Enfermedades Crónicas</th>
                                <th>Descripción</th>
                                <th>Fecha Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($documentos) {
                                foreach ($documentos as $fila) {
                                    echo "<tr>
                                        <td>{$fila['IdExpediente']}</td>
                                        <td>{$fila['paciente']}</td>
                                        <td>{$fila['FechaCreacion']}</td>
                                        <td>{$fila['Antecedentes']}</td>
                                        <td>{$fila['Alergias']}</td>
                                        <td>{$fila['MedicamentosActuales']}</td>
                                        <td>{$fila['EnfermedadesCronicas']}</td>
                                        <td>{$fila['Descripcion']}</td>
                                        <td>{$fila['FechaActualizacion']}</td>
                                        <td>
                                            <a href='#' class='edit-btn' 
                                                data-id='{$fila['IdExpediente']}'
                                                data-idpaciente='{$fila['idPaciente']}'
                                                data-paciente='{$fila['paciente']}'
                                                data-fechacreacion='{$fila['FechaCreacion']}'
                                                data-antecedentes='{$fila['Antecedentes']}'
                                                data-alergias='{$fila['Alergias']}'
                                                data-medicamentos='{$fila['MedicamentosActuales']}'
                                                data-enfermedades='{$fila['EnfermedadesCronicas']}'
                                                data-descripcion='{$fila['Descripcion']}'
                                                data-fechaactualizacion='{$fila['FechaActualizacion']}'
                                            >
                                            <img src='../img/edit.png' width='35' height='35'>
                                            </a>
                                            <a href='#' class='delete-btn' data-id='{$fila['IdExpediente']}'>
                                                <img src='../img/delete.png' width='35' height='35'>
                                            </a>
                                        </td>
                                    </tr>";
                                }
                            }

                            if (empty($documentos)) {
                                echo "<tr><td colspan='10'>No hay expedientes médicos registrados</td></tr>";
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
        const modals = document.querySelectorAll(".modalAgregarExpediente, .modalEditarExpediente");
        const closeButtons = document.querySelectorAll(".close");
        const editButtons = document.querySelectorAll(".edit-btn");
        const addButtons = document.querySelectorAll(".add-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

    addButtons.forEach(btn => {
        btn.addEventListener("click", function(event) {
            event.preventDefault();
            document.getElementById("modalAgregarExpediente").style.display = "block";
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

    // Abrir modal de edición
    editButtons.forEach(btn => {
        btn.addEventListener("click", function(event) {
            event.preventDefault();
            console.log("Botón de edición clickeado"); // Depuración

            const idExpediente = btn.dataset.id;
            const idPaciente = btn.dataset.idpaciente;
            const paciente = btn.dataset.paciente;
            const fechaCreacion = btn.dataset.fechacreacion;
            const antecedentes = btn.dataset.antecedentes;
            const alergias = btn.dataset.alergias;
            const medicamentos = btn.dataset.medicamentos;
            const enfermedades = btn.dataset.enfermedades;
            const descripcion = btn.dataset.descripcion;
            const fechaActualizacion = btn.dataset.fechaactualizacion;

            // Asignar valores al modal
            document.getElementById("edit-idExpediente").value = idExpediente;
            document.getElementById("edit-idPaciente").value = idPaciente;
            document.getElementById("edit-nombrePaciente").value = paciente;
            document.getElementById("edit-fechaCreacion").value = fechaCreacion;
            document.getElementById("edit-antecedentes").value = antecedentes;
            document.getElementById("edit-alergias").value = alergias;
            document.getElementById("edit-medicamentos").value = medicamentos;
            document.getElementById("edit-enfermedades").value = enfermedades;
            document.getElementById("edit-descripcion").value = descripcion;
            document.getElementById("edit-fechaActualizacion").value = fechaActualizacion;

            // Mostrar el modal
            document.getElementById("modalEditarExpediente").style.display = "block";
        });
    });

deleteButtons.forEach(btn => {
    btn.addEventListener("click", async event => {
        event.preventDefault();
        const idExpediente = btn.dataset.id;

     
        const confirmacion = await Swal.fire({
            title: `¿Eliminar el expediente Nº ${idExpediente}?`,
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
            
            const response = await fetch("php/delete-expediente.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `idExpediente=${idExpediente}`
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
                text: "Hubo un problema al eliminar el expediente.",
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