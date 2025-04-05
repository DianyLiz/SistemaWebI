<?php
require '../php/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include '../conexion.php';
$paginaActual = 'pacientes';
$dni_filter = $_GET['dni'] ?? '';
$nombre_apellido_filter = $_GET['nombre_apellido'] ?? '';
$sexo_filter = $_GET['sexo'] ?? '';

$sql = "SELECT
        T1.idUsuario, T1.idPaciente, T2.dni, T2.nombre, T2.apellido, T1.sexo, 
        CONVERT(VARCHAR, T1.fechaNacimiento, 23) AS FechaNacimiento, 
        ISNULL(T1.telefono,'-') AS telefono, 
        T1.direccion
        FROM Pacientes T1
        INNER JOIN Usuarios T2 ON T2.idUsuario = T1.idUsuario WHERE 1=1";

if ($dni_filter) {
    $sql .= " AND T2.dni LIKE '%$dni_filter%'";
}
if ($nombre_apellido_filter) {
    $sql .= " AND T2.nombre LIKE '%$nombre_apellido_filter%' OR T2.apellido LIKE '%$nombre_apellido_filter%'";
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
    
    // Crear el contenido HTML para el PDF
    $html = "<h1>Lista de Pacientes</h1>";
    $html .= "<table border='1' cellpadding='10' cellspacing='0'>";
    $html .= "<thead>
                <tr>
                    <th>#</th>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Sexo</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                </tr>
              </thead><tbody>";

    $contador = 1; // Inicializar el contador
    foreach ($pacientes as $fila) {
        $html .= "<tr>
                    <td>{$contador}</td>
                    <td>{$fila['dni']}</td>
                    <td>{$fila['nombre']}</td>
                    <td>{$fila['apellido']}</td>
                    <td>{$fila['sexo']}</td>
                    <td>{$fila['FechaNacimiento']}</td>
                    <td>{$fila['telefono']}</td>
                    <td>{$fila['direccion']}</td>
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
    $dompdf->stream("pacientes.pdf", ["Attachment" => true]);
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
            <?php include 'modals/agregar-usuario.php'; ?>
            <?php include 'modals/editar-paciente.php'; ?>
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
                <div class="encabezado">
                    <a href="#" class="add-btn">Agregar Paciente</a>
                    <a href="?export_pdf" class="btn-pdf">Exportar a PDF</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th> <!-- Cambiar "ID" a "#" para el número de paciente -->
                                <th>DNI</th>
                                <th>NOMBRE</th>
                                <th>APELLIDO</th>
                                <th>SEXO</th>
                                <th>FECHA DE NACIMIENTO</th>
                                <th>TELEFONO</th>
                                <th>DIRECCION</th>
                                <th>ACCION</th>
                            </tr>
                        </thead>
                        <tbody id="pacientesTable">
                            <?php
                            if (count($pacientes) > 0) {
                                $contador = 1; // Inicializar el contador
                                foreach ($pacientes as $fila) {
                                    echo "<tr>
                                        <td>{$contador}</td> <!-- Mostrar el número de paciente -->
                                        <td>{$fila['dni']}</td>
                                        <td>{$fila['nombre']}</td>
                                        <td>{$fila['apellido']}</td>
                                        <td>{$fila['sexo']}</td>
                                        <td>{$fila['FechaNacimiento']}</td>
                                        <td>{$fila['telefono']}</td>
                                        <td>{$fila['direccion']}</td>
                                        <td>
                                            <a href='#' class='edit-btn'
                                                data-idusuario='{$fila['idUsuario']}'
                                                data-idpaciente='{$fila['idPaciente']}'
                                                data-dni='{$fila['dni']}'
                                                data-nombre='{$fila['nombre']}'
                                                data-apellido='{$fila['apellido']}'
                                                data-sexo='{$fila['sexo']}'
                                                data-fechaNacimiento='{$fila['FechaNacimiento']}'
                                                data-telefono='{$fila['telefono']}'
                                                data-direccion='{$fila['direccion']}'></a>
                                            <a href='#' class='delete-btn' data-idpaciente='{$fila['idPaciente']}' data-idusuario='{$fila['idUsuario']}'></a>
                                        </td>
                                      </tr>";
                                    $contador++; // Incrementar el contador
                                }
                            } else {
                                echo "<tr><td colspan='9'>No hay pacientes registrados</td></tr>";
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
                        let contador = 1; // Inicializar el contador
                        data.forEach(paciente => {
                            const row = `
                            <tr>
                                <td>${contador}</td> <!-- Mostrar el número de paciente -->
                                <td>${paciente.dni}</td>
                                <td>${paciente.nombre}</td>
                                <td>${paciente.apellido}</td>
                                <td>${paciente.sexo}</td>
                                <td>${paciente.FechaNacimiento}</td>
                                <td>${paciente.telefono}</td>
                                <td>${paciente.direccion}</td>
                                <td>
                                    <a href="#" class="edit-btn" 
                                        data-idusuario="${paciente.idUsuario}"
                                        data-idpaciente="${paciente.idPaciente}"
                                        data-dni="${paciente.dni}"
                                        data-nombre="${paciente.nombre}"
                                        data-apellido="${paciente.apellido}"
                                        data-sexo="${paciente.sexo}"
                                        data-fechaNacimiento="${paciente.FechaNacimiento}"
                                        data-telefono="${paciente.telefono}"
                                        data-direccion="${paciente.direccion}"></a>
                                    <a href="#" class="delete-btn" data-idpaciente="${paciente.idPaciente}" data-idusuario="${paciente.idUsuario}"></a>
                                </td>
                            </tr>
                            `;
                            pacientesTable.innerHTML += row;
                            contador++; // Incrementar el contador
                        });
                    } else {
                        pacientesTable.innerHTML = "<tr><td colspan='9'>No hay pacientes registrados</td></tr>";
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

                const edad = calcularEdad(this.dataset.fechanacimiento);
                if(edad < 18) {
                    document.getElementById("camposTutorEditar").style.display = "contents";
                    const idPaciente = this.dataset.idpaciente;

                    fetch("php/buscarTutorPorPaciente.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idPaciente=${idPaciente}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.length > 0) {
                            const tutor = data[0];
                            document.getElementById("edit-nombreTutor").value = tutor.Tutor;
                            document.getElementById("edit-dniTutor").value = tutor.dni;
                            document.getElementById("edit-telefono").value = tutor.telefono;
                            document.getElementById("edit-idTutor").value = tutor.idResponsable;
                        } else {
                            document.getElementById("edit-nombreTutor").value = "";
                            document.getElementById("edit-dniTutor").value = "";
                            document.getElementById("edit-telefono").value = "";
                            document.getElementById("edit-idTutor").value = "";
                        }
                    })
                    .catch(error => console.error("Error al buscar un tutor:", error));

                } else {
                    document.getElementById("camposTutorEditar").style.display = "none";
                }
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

    function calcularEdad(fechaNacimiento) {
        let nacimiento = new Date(fechaNacimiento);
        let hoy = new Date();
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        let mes = hoy.getMonth() - nacimiento.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }

        return edad;
    }
</script>

</html>