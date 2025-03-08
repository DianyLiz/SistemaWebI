<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php';

$paciente_filter = $_GET['paciente'] ?? '';
$medico_filter = $_GET['medico'] ?? '';
$tipo_filter = $_GET['tipo'] ?? '';
$fecha_filter = $_GET['fecha'] ?? '';

$sql = "SELECT 
    d.idDocumento,
    d.idPaciente,
    CONCAT(u1.nombre, ' ', u1.apellido) AS paciente,
    d.idMedico,
    CONCAT(u2.nombre, ' ', u2.apellido) AS Medico,
    d.idCita,
    d.fechaSubida,
    d.tipoDocumento,
    d.urlDocumento,
    d.descripcion
FROM [dbo].[DocumentosMedicos] d
LEFT JOIN [dbo].[Pacientes] p ON d.idPaciente = p.idPaciente
LEFT JOIN [dbo].[Medicos] m ON d.idMedico = m.idMedico
LEFT JOIN [dbo].[Usuarios] u1 ON p.idUsuario = u1.idUsuario  
LEFT JOIN [dbo].[Usuarios] u2 ON m.idUsuario = u2.idUsuario 
LEFT JOIN [dbo].[Citas] c ON d.idCita = c.idCita
WHERE d.idDocumento IS NOT NULL ";
 
if ($paciente_filter) {
    $sql .= " AND u1.nombre LIKE :paciente_filter";
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

if (isset($_GET['export_pdf'])) {
    $stmt = $conn->prepare($sql);
    if ($paciente_filter) {
        $stmt->bindValue(':paciente_filter', '%' . $paciente_filter . '%');
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
    $documento = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = "<h1>Lista de Documentos Médicos</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Fecha Subida</th>
                </tr>
              </thead><tbody>";

    foreach ($documento as $fila) {
        $html .= "<tr>
                    <td>{$fila['paciente']}</td>
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
    $stmt = $conn->prepare($sql);
    if ($paciente_filter) {
        $stmt->bindValue(':paciente_filter', '%' . $paciente_filter . '%');
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

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Paciente');
    $sheet->setCellValue('B1', 'Médico');
    $sheet->setCellValue('C1', 'Tipo');
    $sheet->setCellValue('D1', 'Descripción');
    $sheet->setCellValue('E1', 'Fecha Subida');

    $row = 2;
    foreach ($documentos as $fila) {
        $sheet->setCellValue("A$row", $fila['paciente']);
        $sheet->setCellValue("B$row", $fila['Medico']);
        $sheet->setCellValue("C$row", $fila['tipoDocumento']);
        $sheet->setCellValue("D$row", $fila['descripcion']);
        $sheet->setCellValue("E$row", $fila['fechaSubida']);
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
    $stmt = $conn->prepare($sql);
    if ($paciente_filter) {
        $stmt->bindValue(':paciente_filter', '%' . $paciente_filter . '%');
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

    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    $section->addText("Lista de Documentos Médicos", ['bold' => true, 'size' => 16]);
    $table = $section->addTable();

    $table->addRow();
    $table->addCell(2000)->addText("Paciente");
    $table->addCell(2000)->addText("Médico");
    $table->addCell(2000)->addText("Tipo");
    $table->addCell(2000)->addText("Descripción");
    $table->addCell(2000)->addText("Fecha Subida");

    foreach ($documentos as $fila) {
        $table->addRow();
        $table->addCell(2000)->addText($fila['paciente']);
        $table->addCell(2000)->addText($fila['Medico']);
        $table->addCell(2000)->addText($fila['tipoDocumento']);
        $table->addCell(2000)->addText($fila['descripcion']);
        $table->addCell(2000)->addText($fila['fechaSubida']);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment;filename=\"documentos_medicos.docx\"");
    $phpWord->save('php://output');
    exit;
}

$documentos = [];
$stmt = $conn->prepare($sql);
if ($paciente_filter) {
    $stmt->bindValue(':paciente_filter', '%' . $paciente_filter . '%');
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
                                        <td>{$fila['Medico']}</td>
                                        <td>{$fila['tipoDocumento']}</td>
                                        <td>{$fila['descripcion']}</td>
                                        <td>{$fila['fechaSubida']}</td>
                                        <td>
                                            <a href='#' class='edit-btn' 
                                                data-id='{$fila['idDocumento']}'
                                                data-paciente='{$fila['paciente']}'
                                                data-medico='{$fila['Medico']}'
                                                data-tipo='{$fila['tipoDocumento']}'
                                                data-descripcion='{$fila['descripcion']}'
                                                data-fecha='{$fila['fechaSubida']}'
                                            >
                                            <img src='../img/edit.png' width='35' height='35'>
                                            <a href='#' class='delete-btn' data-idcita='{$fila['idDocumento']}'>
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
                modalEditarCita.style.display = "block";
            });
        });

        closeButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                modals.forEach(modal => {
                    modal.style.display = "none";
                });
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
    </script>
</body>
</html>
