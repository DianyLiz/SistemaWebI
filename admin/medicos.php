<?php

require '../php/vendor/autoload.php';

    use Dompdf\Dompdf;
    use Dompdf\Options;
    
include '../conexion.php';
$paginaActual = 'medicos';
$dni_filter = $_GET['dni'] ?? '';
$nombre_apellido_filter = $_GET['nombre_apellido'] ?? '';
$especialidad_filter = $_GET['especialidad'] ?? '';

$sql = "SELECT T1.idUsuario, T1.idMedico, T2.dni, T2.nombre, T2.apellido, 
T1.idEspecialidad, T3.nombreEspecialidad, T1.numeroLicenciaMedica, T1.anosExperiencia, T1.telefono AS telefonoMedico
FROM Medicos T1
INNER JOIN Usuarios T2 ON T2.idUsuario = T1.idUsuario
INNER JOIN Especialidades T3 on T3.idEspecialidad = T1.idEspecialidad WHERE 1=1";

if ($dni_filter) {
    $sql .= " AND T2.dni LIKE '%$dni_filter%'";
}
if ($nombre_apellido_filter) {
    $sql .= " AND T2.nombre LIKE '%$nombre_apellido_filter%' OR T2.apellido LIKE '%$nombre_apellido_filter%'";
}
if ($especialidad_filter) {
    $sql .= " AND T1.idEspecialidad LIKE '%$especialidad_filter%'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $medicos = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['ajax'])) {
    echo json_encode($medicos);
    exit;
}

if (isset($_GET['export_pdf'])) {
    

    // Crear el contenido HTML para el PDF
    $html = "<h1>Lista de Médicos</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>#</th>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Especialidad</th>
                    <th>Licencia Médica</th>
                    <th>Experiencia</th>
                    <th>Teléfono</th>
                </tr>
              </thead><tbody>";

    $contador = 1; // Inicializar el contador
    foreach ($medicos as $fila) {
        $html .= "<tr>
                    <td>{$contador}</td>
                    <td>{$fila['dni']}</td>
                    <td>{$fila['nombre']}</td>
                    <td>{$fila['apellido']}</td>
                    <td>{$fila['nombreEspecialidad']}</td>
                    <td>{$fila['numeroLicenciaMedica']}</td>
                    <td>{$fila['anosExperiencia']} años</td>
                    <td>{$fila['telefonoMedico']}</td>
                  </tr>";
        $contador++;
    }
    $html .= "</tbody></table>";

    // Configurar Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);

    // Cargar el contenido HTML
    $dompdf->loadHtml($html);

    // Configurar el tamaño y la orientación del papel
    $dompdf->setPaper('A4', 'landscape');

    // Renderizar el PDF
    $dompdf->render();

    // Enviar el PDF al navegador para su descarga
    $dompdf->stream("medicos.pdf", ["Attachment" => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="../css/filter.css">
    <style>
        .btn-pdf {
            display: inline-block;
            background-color: #d9534f;
            color: white;
            margin-right: 10px;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-pdf:hover {
            background-color: #c9302c;
        }

        .encabezado {
            display: flex;
            justify-content: flex-start; /* Alinear los botones a la izquierda */
            gap: 10px; /* Espacio entre los botones */
            margin-bottom: 20px; /* Espacio debajo del contenedor */
        }

        .btn-pdf,
        .add-btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            color: white;
        }

        .btn-pdf {
            background-color: #d9534f;
        }

        .btn-pdf:hover {
            background-color: #c9302c;
        }

        .add-btn {
            background-color: #5cb85c;
        }

        .add-btn:hover {
            background-color: #4cae4c;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <main class="contenido">
            <?php include 'modals/editar-medico.php'; ?>
            <?php include 'modals/agregar-usuario.php'; ?>
            <?php include 'alert.php'; ?>
            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" id="searchDNI" name="dni" placeholder="Buscar por DNI" value="<?= $dni_filter ?>" autocomplete="off">
                    <input type="text" id="searchNombre" name="nombre_apellido" placeholder="Buscar por Nombre/Apellido" value="<?= $nombre_apellido_filter ?>" autocomplete="off">
                    <select name="especialidad" id="searchEspecialidad">
                        <option value="">Especialidad</option>
                        <?php
                        include '../conexion.php';
                        $consulta = "SELECT * FROM Especialidades";
                        $statement = $conn->prepare($consulta);
                        $statement->execute();
                        $resultset = $statement->fetchAll();
                        foreach ($resultset as $especialidad) {
                            $isselected = $especialidad_filter == $especialidad['idEspecialidad'] ? 'selected' : '';
                            echo '<option value="' . $especialidad['idEspecialidad'] . '" ' . $isselected . '>' . $especialidad['nombreEspecialidad'] . '</option>';
                        }
                        ?>
                    </select>
                </form>
            </div>
            <div class="table-container">
                <h2>TABLA DE MÉDICOS</h2>
                <div class="encabezado">
                    <a href="#" class="add-btn">Agregar Usuario</a>
                    <a href="?export_pdf" class="btn-pdf">Exportar a PDF</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th> <!-- Cambiar "ID" a "#" para el número de médico -->
                                <th>DNI</th>
                                <th>NOMBRE</th>
                                <th>APELLIDO</th>
                                <th>ESPECIALIDAD</th>
                                <th>LICENCIA MEDICA</th>
                                <th>EXPERIENCIA</th>
                                <th>TELEFONO</th>
                                <th>ACCION</th>
                            </tr>
                        </thead>
                        <tbody id="medicosTable">
                            <?php
                            if (count($medicos) > 0) {
                                $contador = 1; // Inicializar el contador
                                foreach ($medicos as $fila) {
                                    echo "<tr>
                                        <td>{$contador}</td> <!-- Mostrar el número de médico -->
                                        <td>{$fila['dni']}</td>
                                        <td>{$fila['nombre']}</td>
                                        <td>{$fila['apellido']}</td>
                                        <td>{$fila['nombreEspecialidad']}</td>
                                        <td>{$fila['numeroLicenciaMedica']}</td>
                                        <td>{$fila['anosExperiencia']} años</td>
                                        <td>{$fila['telefonoMedico']}</td>
                                        <td>
                                            <a href='#' class='edit-btn'
                                                data-idusuario='{$fila['idUsuario']}'
                                                data-idmedico='{$fila['idMedico']}'
                                                data-dni='{$fila['dni']}'
                                                data-nombre='{$fila['nombre']}'
                                                data-apellido='{$fila['apellido']}'
                                                data-especialidad='{$fila['idEspecialidad']}'
                                                data-licencia='{$fila['numeroLicenciaMedica']}'
                                                data-experiencia='{$fila['anosExperiencia']}'
                                                data-telefonomedico='{$fila['telefonoMedico']}'></a>
                                            <a href='#' class='delete-btn' data-idmedico='{$fila['idMedico']}' data-idusuario='{$fila['idUsuario']}'></a>
                                        </td>
                                      </tr>";
                                    $contador++; // Incrementar el contador
                                }
                            } else {
                                echo "<tr><td colspan='9'>No hay médicos registrados</td></tr>";
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
        const searchEspecialidad = document.getElementById("searchEspecialidad");
        const medicosTable = document.getElementById("medicosTable");

        window.fetchMedicos = function() {
            const dni = searchDNI.value.trim();
            const nombre_apellido = searchNombre.value.trim();
            const especialidad = searchEspecialidad.value.trim();

            const params = new URLSearchParams({
                dni,
                nombre_apellido,
                especialidad,
                ajax: 1
            });

            fetch(`medicos.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    medicosTable.innerHTML = "";
                    if (data.length > 0) {
                        let contador = 1; // Inicializar el contador
                        data.forEach(medico => {
                            const row = `
                            <tr>
                                <td>${contador}</td> <!-- Mostrar el número de médico -->
                                <td>${medico.dni}</td>
                                <td>${medico.nombre}</td>
                                <td>${medico.apellido}</td>
                                <td>${medico.nombreEspecialidad}</td>
                                <td>${medico.numeroLicenciaMedica}</td>
                                <td>${medico.anosExperiencia} años</td>
                                <td>${medico.telefonoMedico}</td>
                                <td>
                                    <a href="#" class="edit-btn" 
                                        data-idusuario="${medico.idUsuario}"
                                        data-idmedico="${medico.idMedico}"
                                        data-dni="${medico.dni}"
                                        data-nombre="${medico.nombre}"
                                        data-apellido="${medico.apellido}"
                                        data-especialidad="${medico.idEspecialidad}"
                                        data-licencia="${medico.numeroLicenciaMedica}"
                                        data-experiencia="${medico.anosExperiencia}"
                                        data-telefonomedico="${medico.telefonoMedico}"></a>
                                    <a href="#" class="delete-btn" data-idmedico="${medico.idMedico}" data-idusuario="${medico.idUsuario}"></a>
                                </td>
                            </tr>
                            `;
                            medicosTable.innerHTML += row;
                            contador++; // Incrementar el contador
                        });
                    } else {
                        medicosTable.innerHTML = "<tr><td colspan='9'>No hay médicos registrados</td></tr>";
                    }
                })
                .catch(error => console.error("Error en la búsqueda:", error));
        }
        // Eventos para filtrar en tiempo real
        searchDNI.addEventListener("keyup", fetchMedicos);
        searchNombre.addEventListener("keyup", fetchMedicos);
        searchEspecialidad.addEventListener("change", fetchMedicos);
    });

    // --------------------------------- Metodos para abrir y cerrar modales ---------------------------------
    const modals = document.querySelectorAll(".modalAgregarUsuario, .modalEditarMedico");
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

    // --------------------------------- Funcion para asignar eventos a los botones de editar y eliminar ---------------------------------
    function asignarEventosBotones() {
        const editButtons = document.querySelectorAll(".edit-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

        editButtons.forEach(btn => {
            btn.addEventListener("click", function() {
                event.preventDefault();
                document.getElementById("edit-idusuario").value = this.dataset.idusuario;
                document.getElementById("edit-idmedico").value = this.dataset.idmedico;
                document.getElementById("edit-dni").value = this.dataset.dni;
                document.getElementById("edit-nombre").value = this.dataset.nombre;
                document.getElementById("edit-apellido").value = this.dataset.apellido;
                document.getElementById("edit-idespecialidad").value = this.dataset.especialidad;
                document.getElementById("edit-licenciaMedica").value = this.dataset.licencia;
                document.getElementById("edit-aniosExperiencia").value = this.dataset.experiencia;
                document.getElementById("edit-telefonoMedico").value = this.dataset.telefonomedico;
                modalEditarMedico.style.display = "block";
            });
        });

        deleteButtons.forEach(btn => {
            btn.addEventListener("click", async event => {
                event.preventDefault();
                const idmedico = btn.dataset.idmedico;
                const idusuario = btn.dataset.idusuario;
                const confirmacion = await Swal.fire({
                    title: `¿Realmente quieres eliminar el Médico Nº ${idmedico}?`,
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
                    const response = await fetch("php/delete-doctor.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idmedico=${idmedico}&idusuario=${idusuario}`
                    });
                    const data = await response.json();
                    await Swal.fire({
                        title: data.status === "success" ? "Éxito" : "Error",
                        text: data.message,
                        icon: data.status === "success" ? "success" : "error"
                    });
                    if (data.status === "success") {
                        modalEditarMedico.style.display = "none";
                        fetchMedicos();
                    }
                } catch (error) {
                    Swal.fire({
                        title: "Error",
                        text: "Hubo un problema al eliminar el médico.",
                        icon: "error"
                    });
                    console.error("Error:", error);
                }
            });
        });

    }
</script>

</html>