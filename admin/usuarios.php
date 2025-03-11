<?php
include '../conexion.php';
$paginaActual = 'usuarios';
$dni_filter = $_GET['dni'] ?? '';
$nombre_apellido_filter = $_GET['nombre_apellido'] ?? '';
$rol_filter = $_GET['rol'] ?? '';
$sql = "SELECT idusuario, dni, nombre, apellido, usuario, correo, rol FROM usuarios WHERE 1=1";

if ($dni_filter) {
    $sql .= " AND dni LIKE '%$dni_filter%'";
}
if ($nombre_apellido_filter) {
    $sql .= " AND nombre LIKE '%$nombre_apellido_filter%' OR apellido LIKE '%$nombre_apellido_filter%'";
}
if ($rol_filter) {
    $sql .= " AND rol LIKE '%$rol_filter%'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['ajax'])) {
    echo json_encode($usuarios);
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
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="contenedor">
        <?php include 'menu.php'; ?>
        <main class="contenido">
            <?php include 'modals/editar-usuario.php'; ?>
            <?php include 'modals/agregar-usuario.php'; ?>
            <?php include 'alert.php'; ?>
            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" id="searchDNI" name="dni" placeholder="Buscar por DNI" value="<?= $dni_filter ?>" autocomplete="off">
                    <input type="text" id="searchNombre" name="nombre_apellido" placeholder="Buscar por Nombre/Apellido" value="<?= $nombre_apellido_filter ?>" autocomplete="off">
                    <select name="rol" id="searchRol">
                        <option value="">Permiso de Usuario</option>
                        <option value="Administrador" <?= $rol_filter == 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="Médico" <?= $rol_filter == 'Médico' ? 'selected' : '' ?>>Médico</option>
                        <option value="Paciente" <?= $rol_filter == 'Paciente' ? 'selected' : '' ?>>Paciente</option>
                    </select>
                </form>
            </div>
            <div class="table-container">
                <h2>TABLA DE USUARIOS</h2>
                <div class="encabezado">
                    <a href="#" class="add-btn">Agregar Usuario</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DNI</th>
                                <th>NOMBRE</th>
                                <th>APELLIDO</th>
                                <th>USUARIO</th>
                                <th>CORREO</th>
                                <th>PERMISOS</th>
                                <th>ACCION</th>
                            </tr>
                        </thead>
                        <tbody id="usuariosTable">
                            <?php
                            if (count($usuarios) > 0) {
                                foreach ($usuarios as $fila) {
                                    echo "<tr>
                                <td>{$fila['idusuario']}</td>
                                <td>{$fila['dni']}</td>
                                <td>{$fila['nombre']}</td>
                                <td>{$fila['apellido']}</td>
                                <td>{$fila['usuario']}</td>
                                <td>{$fila['correo']}</td>
                                <td>{$fila['rol']}</td>
                                <td>
                                    <a href='#' class='edit-btn' 
                                        data-idusuario='{$fila['idusuario']}'
                                        data-dni='{$fila['dni']}'
                                        data-nombre='{$fila['nombre']}' 
                                        data-apellido='{$fila['apellido']}' 
                                        data-usuario='{$fila['usuario']}' 
                                        data-correo='{$fila['correo']}'>
                                        <img src=\"../img/edit.png\" width=\"35\" height=\"35\"></a>
                                    <a href='#' class='delete-btn' data-idusuario='{$fila['idusuario']}'><img src=\"../img/delete.png\" width=\"35\" height=\"35\"></a>
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
    <script>
// --------------------------------- Script para filtrar en tiempo real y actualizar la tabla dinamicamente ---------------------------------
        document.addEventListener("DOMContentLoaded", function() {
            const searchDNI = document.getElementById("searchDNI");
            const searchNombre = document.getElementById("searchNombre");
            const searchRol = document.getElementById("searchRol");
            const usuariosTable = document.getElementById("usuariosTable");

            window.fetchUsuarios = function() {
                const dni = searchDNI.value.trim();
                const nombre_apellido = searchNombre.value.trim();
                const rol = searchRol.value;

                const params = new URLSearchParams({
                    dni,
                    nombre_apellido,
                    rol,
                    ajax: 1
                });

                fetch(`usuarios.php?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        usuariosTable.innerHTML = "";
                        if (data.length > 0) {
                            data.forEach(usuario => {
                                const row = `
                    <tr>
                        <td>${usuario.idusuario}</td>
                        <td>${usuario.dni}</td>
                        <td>${usuario.nombre}</td>
                        <td>${usuario.apellido}</td>
                        <td>${usuario.usuario}</td>
                        <td>${usuario.correo}</td>
                        <td>${usuario.rol}</td>
                        <td>
                            <a href="#" class="edit-btn" 
                                data-idusuario="${usuario.idusuario}"
                                data-dni="${usuario.dni}"
                                data-nombre="${usuario.nombre}"
                                data-apellido="${usuario.apellido}"
                                data-usuario="${usuario.usuario}"
                                data-correo="${usuario.correo}">
                                <img src="../img/edit.png" width="35" height="35"></a>
                            <a href="#" class="delete-btn" data-idusuario="${usuario.idusuario}">
                                <img src="../img/delete.png" width="35" height="35">
                            </a>
                        </td>
                    </tr>
                `;
                                usuariosTable.innerHTML += row;
                            });
                        } else {
                            usuariosTable.innerHTML = "<tr><td colspan='8'>No hay usuarios registrados</td></tr>";
                        }

                        // Vuelve a asignar eventos a los botones después de actualizar la tabla
                        asignarEventosBotones();
                    })
                    .catch(error => console.error("Error en la búsqueda:", error));
            }
            // Eventos para filtrar en tiempo real
            searchDNI.addEventListener("keyup", fetchUsuarios);
            searchNombre.addEventListener("keyup", fetchUsuarios);
            searchRol.addEventListener("change", fetchUsuarios);
        });

// --------------------------------- Funcion para asignar eventos a los botones de editar y eliminar ---------------------------------
        function asignarEventosBotones() {
            const editButtons = document.querySelectorAll(".edit-btn");
            const deleteButtons = document.querySelectorAll(".delete-btn");

            editButtons.forEach(btn => {
                btn.addEventListener("click", function(event) {
                    event.preventDefault();
                    document.getElementById("edit-idusuario").value = this.dataset.idusuario;
                    document.getElementById("edit-dni").value = this.dataset.dni;
                    document.getElementById("edit-nombre").value = this.dataset.nombre;
                    document.getElementById("edit-apellido").value = this.dataset.apellido;
                    document.getElementById("edit-correo").value = this.dataset.correo;
                    document.getElementById("edit-usuario").value = this.dataset.usuario;

                    modalEditarUsuario.style.display = "block";
                });
            });

            deleteButtons.forEach(btn => {
                btn.addEventListener("click", async event => {
                    event.preventDefault();
                    const idusuario = btn.dataset.idusuario;
                    const confirmacion = await Swal.fire({
                        title: `¿Realmente quieres eliminar el Usuario Nº ${idusuario}?`,
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
                        const response = await fetch("php/delete-user.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `idusuario=${idusuario}`
                        });

                        const data = await response.json();

                        await Swal.fire({
                            title: data.status === "success" ? "Éxito" : "Error",
                            text: data.message,
                            icon: data.status === "success" ? "success" : "error"
                        });

                        if (data.status === "success") {
                            modalEditarUsuario.style.display = "none";
                            fetchUsuarios();
                        }

                    } catch (error) {
                        Swal.fire({
                            title: "Error",
                            text: "Hubo un problema al eliminar el usuario.",
                            icon: "error"
                        });
                        console.error("Error:", error);
                    }
                });
            });

        }

// --------------------------------- Metodos para abrir y cerrar modales ---------------------------------
        const modals = document.querySelectorAll(".modalAgregarUsuario, .modalEditarUsuario");
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
</body>
</html>