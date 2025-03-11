<?php
include '../conexion.php';
$especialidad_filter = $_GET['especialidad'] ?? '';
$sql = "SELECT idEspecialidad, nombreEspecialidad, descripcion FROM Especialidades WHERE 1=1";

if ($especialidad_filter) {
    $sql .= " AND nombreEspecialidad LIKE '%$especialidad_filter%'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $especialidades = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['ajax'])) {
    echo json_encode($especialidades);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="../css/filter.css">
    <title>Document</title>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <main class="contenido">
            <?php include 'modals/agregar-especialidad.php' ?>
            <?php include 'modals/editar-especialidad.php' ?>
            <?php include 'alert.php'; ?>
            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" name="especialidad" id="searchEspecialidad" placeholder="Buscar por nombre de especialidad" value="<?= $especialidad_filter ?>" autocomplete="off">
                </form>
            </div>
            <div class="table-container">
                <h2>TABLA DE ESPECIALIDADES</h2>
                <div class="encabezado">
                    <a href="#" class="add-btn">Agregar Especialidad</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE ESPECIALIDAD</th>
                                <th>DESCRIPCIÓN</th>
                                <th>ACCION</th>
                            </tr>
                        </thead>
                        <tbody id="especialidadesTable">
                            <?php
                            if (count($especialidades) > 0) {
                                foreach ($especialidades as $fila) {
                                    echo "<tr>
                                <td>{$fila['idEspecialidad']}</td>
                                <td>{$fila['nombreEspecialidad']}</td>
                                <td>{$fila['descripcion']}</td>
                                <td>
                                    <a href='#' class='edit-btn' 
                                        data-idespecialidad='{$fila['idEspecialidad']}'
                                        data-especialidad='{$fila['nombreEspecialidad']}'
                                        data-descripcion='{$fila['descripcion']}'>
                                        <img src=\"../img/edit.png\" width=\"35\" height=\"35\"></a>
                                    <a href='#' class='delete-btn' data-idespecialidad='{$fila['idEspecialidad']}'><img src=\"../img/delete.png\" width=\"35\" height=\"35\"></a>
                                </td>
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
        const searchEspecialidad = document.getElementById("searchEspecialidad");

        window.fetchEspecialidades = function() {
            const especialidad = searchEspecialidad.value.trim();

            const params = new URLSearchParams({
                especialidad,
                ajax: 1
            });

            fetch(`especialidades.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    especialidadesTable.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach(especialidades => {
                            const row = `
                    <tr>
                        <td>${especialidades.idEspecialidad}</td>
                        <td>${especialidades.nombreEspecialidad}</td>
                        <td>${especialidades.descripcion}</td>
                        <td>
                            <a href="#" class="edit-btn" 
                                data-idespecialidad="${especialidades.idEspecialidad}"
                                data-especialidad="${especialidades.nombreEspecialidad}"
                                data-descripcion="${especialidades.descripcion}">
                                <img src="../img/edit.png" width="35" height="35"></a>
                            <a href="#" class="delete-btn" data-idespecialidad="${especialidades.idespecialidad}">
                                <img src="../img/delete.png" width="35" height="35">
                            </a>
                        </td>
                    </tr>
                `;
                            especialidadesTable.innerHTML += row;
                        });
                    } else {
                        especialidadesTable.innerHTML = "<tr><td colspan='8'>No hay usuarios registrados</td></tr>";
                    }

                    // Vuelve a asignar eventos a los botones después de actualizar la tabla
                    asignarEventosBotones();
                })
                .catch(error => console.error("Error en la búsqueda:", error));
        }
        // Eventos para filtrar en tiempo real
        searchEspecialidad.addEventListener("keyup", fetchEspecialidades);
    });

    // --------------------------------- Funcion para asignar eventos a los botones de editar y eliminar ---------------------------------
    function asignarEventosBotones() {
        const editButtons = document.querySelectorAll(".edit-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

        editButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                document.getElementById("edit-idespecialidad").value = this.dataset.idespecialidad;
                document.getElementById("edit-especialidad").value = this.dataset.especialidad;
                document.getElementById("edit-descripcion").value = this.dataset.descripcion;
                modalEditarEspecialidad.style.display = "block";
            });
        });

        deleteButtons.forEach(btn => {
            btn.addEventListener("click", async event => {
                event.preventDefault();
                const idespecialidad = btn.dataset.idespecialidad;
                const confirmacion = await Swal.fire({
                    title: `¿Realmente quieres eliminar la especialidad Nº ${idespecialidad}?`,
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
                    const response = await fetch("php/delete-especialidad.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idespecialidad=${idespecialidad}`
                    });
                    const data = await response.json();
                    await Swal.fire({
                        title: data.status === "success" ? "Éxito" : "Error",
                        text: data.message,
                        icon: data.status === "success" ? "success" : "error"
                    });
                    if (data.status === "success") {
                        modalEditarEspecialidad.style.display = "none";
                        fetchEspecialidades();
                    }
                } catch (error) {
                    Swal.fire({
                        title: "Error",
                        text: "Hubo un problema al eliminar la especialidad.",
                        icon: "error"
                    });
                    console.error("Error:", error);
                }
            });
        });

    }
    // --------------------------------- Metodos para abrir y cerrar modales ---------------------------------
    const modals = document.querySelectorAll(".modalAgregarEspecialidad, .modalEditarEspecialidad");
    const closeButtons = document.querySelectorAll(".close");
    const addButtons = document.querySelectorAll(".add-btn");

    addButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            event.preventDefault();
            modalAgregarEspecialidad.style.display = "block";
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