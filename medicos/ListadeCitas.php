<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php'; // Asegúrate de que la ruta es correcta

// Variables de filtro
$medico_filter = $_GET['medico'] ?? '';
$paciente_filter = $_GET['paciente'] ?? '';
$fecha_filter = $_GET['fecha'] ?? '';
$hora_filter = $_GET['hora'] ?? '';
$estado_filter = $_GET['estado'] ?? '';

// Consulta SQL con filtros
$sql = "SELECT U1.nombre + ' ' + U1.apellido AS paciente, 
               U2.nombre + ' ' + U2.apellido AS medico, 
               Citas.fecha, 
               Citas.hora, 
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

// Generar PDF
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

// Exportar a Excel
if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Paciente');
    $sheet->setCellValue('B1', 'Médico');
    $sheet->setCellValue('C1', 'Fecha');
    $sheet->setCellValue('D1', 'Hora');
    $sheet->setCellValue('E1', 'Estado');

    $row = 2;
    foreach ($citas as $fila) {
        $sheet->setCellValue("A$row", $fila['paciente']);
        $sheet->setCellValue("B$row", $fila['medico']);
        $sheet->setCellValue("C$row", $fila['fecha']);
        $sheet->setCellValue("D$row", $fila['hora']);
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

// Exportar a Word
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
    $table->addCell(2000)->addText("Estado");

    foreach ($citas as $fila) {
        $table->addRow();
        $table->addCell(2000)->addText($fila['paciente']);
        $table->addCell(2000)->addText($fila['medico']);
        $table->addCell(2000)->addText($fila['fecha']);
        $table->addCell(2000)->addText($fila['hora']);
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
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
</head>
<body>
<nav>
    <div class="logo">
        MediCitas
    </div>
    <input type="checkbox" id="click">
    <label for="click" class="menu-btn">
        <i class="fas fa-bars"></i>
    </label>
    <ul class="menu">
        <li><a class="active" href="../medicos/header.php">Salir</a></li>
    </ul>
</nav>

<main>
    <div class="filter-container">
        <form method="GET" action="">
            <input type="text" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>">
            <input type="text" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>">
            <input type="date" name="fecha" value="<?= $fecha_filter ?>">
            <input type="time" name="hora" value="<?= $hora_filter ?>">
            <select name="estado">
                <option value="">Estado</option>
                <option value="Confirmada" <?= $estado_filter == 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                <option value="Pendiente" <?= $estado_filter == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="Cancelada" <?= $estado_filter == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
            </select>
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Tabla de Citas Médicas</h2>
        <div class="export-buttons">
            <a href="?export_pdf=true" class="btn-pdf">Exportar a PDF</a>
            <a href="?export_excel=true" class="btn-excel">Exportar a Excel</a>
            <a href="?export_word=true" class="btn-word">Exportar a Word</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mapeo de estados a clases CSS
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
                                <td>{$fila['paciente']}</td>
                                <td>{$fila['medico']}</td>
                                <td>{$fila['fecha']}</td>
                                <td>{$hora_formateada}</td>
                                <td><span class='status $claseEstado'>" . ucfirst($fila['estado']) . "</span></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay citas registradas</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<?php
// Cerrar conexión
$conn = null;
?>

</body>
</html>