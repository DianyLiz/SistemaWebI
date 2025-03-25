<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

include '../conexion.php';
$dni_filter = $_GET['dni'] ?? '';
$nombre_apellido_filter = $_GET['nombre_apellido'] ?? '';
$sexo_filter = $_GET['sexo'] ?? '';
$paginaActual = 'pacientes';

$sql = "SELECT
T1.idPaciente, T2.dni, T2.nombre, T2.apellido, T1.sexo, T1.fechaNacimiento, T1.telefono, T1.direccion
FROM Pacientes T1
INNER JOIN Usuarios T2 ON T2.idUsuario = T1.idUsuario WHERE 1=1";

if ($dni_filter) {
    $sql .= " AND T2.dni LIKE '%$dni_filter%'";
}
if ($nombre_apellido_filter) {
    $sql .= " AND (T2.nombre LIKE '%$nombre_apellido_filter%' OR T2.apellido LIKE '%$nombre_apellido_filter%')";
}
if ($sexo_filter) {
    $sql .= " AND T1.sexo LIKE '%$sexo_filter%'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $pacientes = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($pacientes);
    exit;
}

if (isset($_GET['export_pdf'])) {
    $html = "<h1>Lista de Pacientes Registrados</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Sexo</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                </tr>
              </thead><tbody>";

    foreach ($pacientes as $fila) {
        $html .= "<tr>
                    <td>{$fila['dni']}</td>
                    <td>{$fila['nombre']}</td>
                    <td>{$fila['apellido']}</td>
                    <td>{$fila['sexo']}</td>
                    <td>{$fila['fechaNacimiento']}</td>
                    <td>{$fila['telefono']}</td>
                    <td>{$fila['direccion']}</td>
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
    $dompdf->stream("pacientes_registrados.pdf", array("Attachment" => false));
    exit;
}

if (isset($_GET['export_excel'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'DNI');
    $sheet->setCellValue('C1', 'Nombre');
    $sheet->setCellValue('D1', 'Apellido');
    $sheet->setCellValue('E1', 'Sexo');
    $sheet->setCellValue('F1', 'Fecha de Nacimiento');
    $sheet->setCellValue('G1', 'Teléfono');
    $sheet->setCellValue('H1', 'Dirección');

    $row = 2;
    foreach ($pacientes as $fila) {
        $sheet->setCellValue("A$row", $fila['idPaciente']);
        $sheet->setCellValue("B$row", $fila['dni']);
        $sheet->setCellValue("C$row", $fila['nombre']);
        $sheet->setCellValue("D$row", $fila['apellido']);
        $sheet->setCellValue("E$row", $fila['sexo']);
        $sheet->setCellValue("F$row", $fila['fechaNacimiento']);
        $sheet->setCellValue("G$row", $fila['telefono']);
        $sheet->setCellValue("H$row", $fila['direccion']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'pacientes_registrados.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$filename\"");
    $writer->save('php://output');
    exit;
}

if (isset($_GET['export_word'])) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    $section->addText("Lista de Pacientes Registrados", ['bold' => true, 'size' => 16]);
    $table = $section->addTable();

    $table->addRow();
    $table->addCell(1000)->addText("ID");
    $table->addCell(1500)->addText("DNI");
    $table->addCell(1500)->addText("Nombre");
    $table->addCell(1500)->addText("Apellido");
    $table->addCell(1500)->addText("Sexo");
    $table->addCell(2000)->addText("Fecha de Nacimiento");
    $table->addCell(1500)->addText("Teléfono");
    $table->addCell(2000)->addText("Dirección");

    foreach ($pacientes as $fila) {
        $table->addRow();
        $table->addCell(1000)->addText($fila['idPaciente']);
        $table->addCell(1500)->addText($fila['dni']);
        $table->addCell(1500)->addText($fila['nombre']);
        $table->addCell(1500)->addText($fila['apellido']);
        $table->addCell(1500)->addText($fila['sexo']);
        $table->addCell(2000)->addText($fila['fechaNacimiento']);
        $table->addCell(1500)->addText($fila['telefono']);
        $table->addCell(2000)->addText($fila['direccion']);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment;filename=\"pacientes_registrados.docx\"");
    $phpWord->save('php://output');
    exit;
}
?>
<style>
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

    .btn-pdf:hover,
    .btn-excel:hover,
    .btn-word:hover {
        background-color: rgb(10, 60, 80);
    }

    @media (max-width: 768px) {
        .export-buttons {
            flex-direction: column;
        }

        .btn-pdf,
        .btn-excel,
        .btn-word {
            width: 100%;
            margin-right: 0;
        }
    }
</style>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="../css/filter.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <main class="contenido">
            <?php include 'alert.php'; ?>
            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" id="searchDNI" name="dni" placeholder="Buscar por DNI" value="<?= $dni_filter ?>" autocomplete="off">
                    <input type="text" id="searchNombre" name="nombre_apellido" placeholder="Buscar por Nombre/Apellido" value="<?= $nombre_apellido_filter ?>" autocomplete="off">
                    <select name="sexo" id="searchSexo">
                        <option value="">Sexo</option>
                        <option value="Masculino" <?= $sexo_filter == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Femenino" <?= $sexo_filter == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </form>
            </div>
            <div class="table-container">
                <h2>TABLA DE PACIENTES</h2>
                <div class="export-buttons">
                    <a href="?export_pdf" class="btn-pdf">Exportar a PDF</a>
                    <a href="?export_excel" class="btn-excel">Exportar a Excel</a>
                    <a href="?export_word" class="btn-word">Exportar a Word</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DNI</th>
                                <th>NOMBRE</th>
                                <th>APELLIDO</th>
                                <th>SEXO</th>
                                <th>FECHA DE NACIMIENTO</th>
                                <th>TELEFONO</th>
                                <th>DIRECCION</th>
                            </tr>
                        </thead>
                        <tbody id="pacientesTable">
                            <?php
                            if (count($pacientes) > 0) {
                                foreach ($pacientes as $fila) {
                                    echo "<tr>
                                <td>{$fila['idPaciente']}</td>
                                <td>{$fila['dni']}</td>
                                <td>{$fila['nombre']}</td>
                                <td>{$fila['apellido']}</td>
                                <td>{$fila['sexo']}</td>
                                <td>{$fila['fechaNacimiento']}</td>
                                <td>{$fila['telefono']}</td>
                                <td>{$fila['direccion']}</td>
                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No hay usuarios registrados</td></tr>";
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
        const searchDNI = document.getElementById("searchDNI");
        const searchNombre = document.getElementById("searchNombre");
        const searchSexo = document.getElementById("searchSexo");
        const pacientesTable = document.getElementById("pacientesTable");

        window.fetchPacientes = function() {
            const dni = searchDNI.value.trim();
            const nombre_apellido = searchNombre.value.trim();
            const sexo = searchSexo.value.trim();

            const params = new URLSearchParams({
                dni,
                nombre_apellido,
                sexo,
                ajax: 1
            });

            fetch(`pacientes.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    pacientesTable.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach(pacientes => {
                            const row = `
                    <tr>
                        <td>${pacientes.idPaciente}</td>
                        <td>${pacientes.dni}</td>
                        <td>${pacientes.nombre}</td>
                        <td>${pacientes.apellido}</td>
                        <td>${pacientes.sexo}</td>
                        <td>${pacientes.fechaNacimiento}</td>
                        <td>${pacientes.telefono}</td>
                        <td>${pacientes.direccion}</td>
                    </tr>
                `;
                            pacientesTable.innerHTML += row;
                        });
                    } else {
                        pacientesTable.innerHTML = "<tr><td colspan='8'>No hay usuarios registrados</td></tr>";
                    }

                    // Vuelve a asignar eventos a los botones después de actualizar la tabla
                    asignarEventosBotones();
                })
                .catch(error => console.error("Error en la búsqueda:", error));
        }
        // Eventos para filtrar en tiempo real
        searchDNI.addEventListener("keyup", fetchPacientes);
        searchNombre.addEventListener("keyup", fetchPacientes);
        searchSexo.addEventListener("change", fetchPacientes);
    });
    // --------------------------------- Funcion para asignar eventos a los botones de editar y eliminar ---------------------------------
    function asignarEventosBotones() {
        const editButtons = document.querySelectorAll(".edit-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

        editButtons.forEach(btn => {
            btn.addEventListener("click", function() {
                event.preventDefault();
                document.getElementById("edit-idusuario").value = this.dataset.idusuario;
                document.getElementById("edit-idpaciente").value = this.dataset.idpaciente;
                document.getElementById("edit-dni").value = this.dataset.dni;
                document.getElementById("edit-nombre").value = this.dataset.nombre;
                document.getElementById("edit-apellido").value = this.dataset.apellido;
                document.getElementById("edit-sexo").value = this.dataset.sexo;
                document.getElementById("edit-fechaNacimiento").value = this.dataset.fechanacimiento;
                document.getElementById("edit-telefono").value = this.dataset.telefono;
                document.getElementById("edit-direccion").value = this.dataset.direccion;
                modalEditarPaciente.style.display = "block";
            });
        });

        deleteButtons.forEach(btn => {
            btn.addEventListener("click", async event => {
                event.preventDefault();
                const idpaciente = btn.dataset.idpaciente;
                const idusuario = btn.dataset.idusuario;
                const confirmacion = await Swal.fire({
                    title: `¿Realmente quieres eliminar el Paciente Nº ${idpaciente}?`,
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
                    const response = await fetch("php/delete-paciente.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idpaciente=${idpaciente}&idusuario=${idusuario}`
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
                        text: "Hubo un problema al eliminar el paciente.",
                        icon: "error"
                    });
                    console.error("Error:", error);
                }
            });
        });
    }
    // --------------------------------- Metodos para abrir y cerrar modales ---------------------------------
    const modals = document.querySelectorAll(".modalAgregarUsuario, .modalEditarPaciente");
    const closeButtons = document.querySelectorAll(".close");
    const addButtons = document.querySelectorAll(".add-btn");

    addButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            event.preventDefault();
            modalAgregarUsuario.style.display = "block";
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener("click", function() {
            modals.forEach(modal => modal.style.display = "none");
        });
    });

    asignarEventosBotones();

    window.onclick = function(event) {
        modals.forEach(modal => {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    };
</script>

</html>