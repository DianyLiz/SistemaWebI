<?php
include '../conexion.php';
$medico_filter = $_GET['medico'] ?? '';
$paciente_filter = $_GET['paciente'] ?? '';
$fecha_filter = $_GET['fecha'] ?? '';

$sql = "SELECT T1.idPago, T1.idCita, T7.fecha, 
        T5.dni AS dniPaciente,
        CONCAT(T5.nombre, ' ',T5.apellido) AS Paciente,
        CONCAT(T6.nombre, ' ',T6.apellido) AS Medico,
        T1.monto, T1.metodoPago, FORMAT(T1.fechaPago, 'yyyy-MM-dd') AS fechaPago
        FROM Pagos T1
        INNER JOIN Citas T2 ON T2.idCita = T1.idCita
        INNER JOIN Pacientes T3 ON T3.idPaciente = T2.idPaciente
        INNER JOIN Medicos T4 ON T4.idMedico = T2.idMedico
        INNER JOIN Usuarios T5 ON T5.idUsuario = T3.idUsuario
        INNER JOIN Usuarios T6 ON T6.idUsuario = T4.idUsuario
        INNER JOIN HorariosMedicos T7 ON T7.idHorario = T2.idHorario
        WHERE 1=1";

if ($medico_filter) {
    $sql .= " AND T6.nombre LIKE '%$medico_filter%'";
}
if ($paciente_filter) {
    $sql .= " AND T5.nombre LIKE '%$paciente_filter%'";
}
if ($fecha_filter) {
    $sql .= " AND T7.fecha = '$fecha_filter'";
}

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $pagos = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}

if (isset($_GET['ajax'])) {
    echo json_encode($pagos);
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
            <?php include 'modals/agregar-pago.php'; ?>
            <?php include 'modals/editar-pago.php'; ?>
            <?php include 'alert.php'; ?>
            <div class="filter-container">
                <form method="GET" action="">
                    <input type="text" id="searchPaciente" name="paciente" placeholder="Buscar por Paciente" value="<?= $paciente_filter ?>" autocomplete="off">
                    <input type="text" id="searchMedico" name="medico" placeholder="Buscar por Médico" value="<?= $medico_filter ?>" autocomplete="off">
                    <input type="date" id="searchFecha" name="fecha" value="<?= $fecha_filter ?>">
                </form>
            </div>
            <div class="table-container">
                <h2>TABLA DE PAGOS POR CITA</h2>
                <div class="encabezado">
                    <a href="#" class="add-btn">Agregar Pago</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nº CITA</th>
                                <th>FECHA DE CITA</th>
                                <th>PACIENTE</th>
                                <th>MÉDICO</th>
                                <th>MONTO</th>
                                <th>METODO DE PAGO</th>
                                <th>FECHA DE PAGO</th>
                                <th>ACCION</th>
                            </tr>
                        </thead>
                        <tbody id="pagosTable">
                            <?php
                            if (count($pagos) > 0) {
                                foreach ($pagos as $fila) {
                                    echo "<tr>
                                <td>{$fila['idPago']}</td>
                                <td>{$fila['idCita']}</td>
                                <td>{$fila['fecha']}</td>
                                <td>{$fila['Paciente']}</td>
                                <td>{$fila['Medico']}</td>
                                <td>L. {$fila['monto']}</td>
                                <td>{$fila['metodoPago']}</td>
                                <td>{$fila['fechaPago']}</td>
                                <td>
                                    <a href='#' class='edit-btn' 
                                        data-idpago='{$fila['idPago']}'
                                        data-idcita='{$fila['idCita']}'
                                        data-fecha='{$fila['fecha']}' 
                                        data-paciente='{$fila['Paciente']}' 
                                        data-dnipaciente='{$fila['dniPaciente']}'
                                        data-medico='{$fila['Medico']}' 
                                        data-monto='{$fila['monto']}'
                                        data-metodoPago='{$fila['metodoPago']}'
                                        data-fechaPago='{$fila['fechaPago']}'></a>
                                    <a href='#' class='delete-btn' data-idpago='{$fila['idPago']}'></a>
                                </td>
                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No hay financiamientos registrados</td></tr>";
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
        const searchPaciente = document.getElementById("searchPaciente");
        const searchMedico = document.getElementById("searchMedico");
        const searchFecha = document.getElementById("searchFecha");
        const pagosTable = document.getElementById("pagosTable");

        window.fetchPagos = function() {
            const paciente = searchPaciente.value.trim();
            const medico = searchMedico.value.trim();
            const fecha = searchFecha.value.trim();

            const params = new URLSearchParams({
                paciente,
                medico,
                fecha,
                ajax: 1
            });

            fetch(`financiamientos.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    pagosTable.innerHTML = "";
                    if (data.length > 0) {
                        data.forEach(pagos => {
                            const row = `
                    <tr>
                        <td>${pagos.idPago}</td>
                        <td>${pagos.idCita}</td>
                        <td>${pagos.fecha}</td>
                        <td>${pagos.Paciente}</td>
                        <td>${pagos.Medico}</td>
                        <td>${pagos.monto}</td>
                        <td>${pagos.metodoPago}</td>
                        <td>${pagos.fechaPago}</td>
                        <td>
                            <a href="#" class="edit-btn" 
                                data-idpago="${pagos.idPago}"
                                data-idcita="${pagos.idCita}"
                                data-fecha="${pagos.fecha}"
                                data-paciente="${pagos.Paciente}"
                                data-dnipaciente="${pagos.dniPaciente}"
                                data-monto="${pagos.monto}"
                                data-metodoPago="${pagos.metodoPago}"
                                data-fechaPago="${pagos.fechaPago}"></a>
                            <a href="#" class="delete-btn" data-idpago="${pagos.idPago}"></a>
                        </td>
                    </tr>
                `;
                            pagosTable.innerHTML += row;
                        });
                    } else {
                        pagosTable.innerHTML = "<tr><td colspan='8'>No hay usuarios registrados</td></tr>";
                    }

                    // Vuelve a asignar eventos a los botones después de actualizar la tabla
                    asignarEventosBotones();
                })
                .catch(error => console.error("Error en la búsqueda:", error));
        }
        // Eventos para filtrar en tiempo real
        searchPaciente.addEventListener("keyup", fetchPagos);
        searchMedico.addEventListener("keyup", fetchPagos);
        searchFecha.addEventListener("change", fetchPagos);
    });

    // --------------------------------- Funcion para asignar eventos a los botones de editar y eliminar ---------------------------------
    function asignarEventosBotones() {
        const editButtons = document.querySelectorAll(".edit-btn");
        const deleteButtons = document.querySelectorAll(".delete-btn");

        editButtons.forEach(btn => {
            btn.addEventListener("click", function(event) {
                event.preventDefault();
                document.getElementById("edit-idPago").value = this.dataset.idpago;
                document.getElementById("edit-idCita").value = this.dataset.idcita;
                document.getElementById("edit-dnipaciente").value = this.dataset.dnipaciente;
                document.getElementById("edit-buscarpaciente").value = this.dataset.paciente;
                document.getElementById("edit-fecha").value = this.dataset.fecha;
                document.getElementById("edit-monto").value = this.dataset.monto;
                document.getElementById("edit-metodoPago").value = this.dataset.metodoPago;
                modalEditarPago.style.display = "block";
            });
        });

        deleteButtons.forEach(btn => {
            btn.addEventListener("click", async event => {
                event.preventDefault();
                const idPago = btn.dataset.idpago;
                const confirmacion = await Swal.fire({
                    title: `¿Eliminar el Pago Nº ${idpago}?`,
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
                    const response = await fetch("php/delete-pago.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `idPago=${idPago}`
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
                        text: "Hubo un problema al eliminar el pago.",
                        icon: "error"
                    });
                    console.error("Error:", error);
                }
            });
        });
    }

    // --------------------------------- Metodos para abrir y cerrar modales ---------------------------------
    const modals = document.querySelectorAll(".modalAgregarPago , .modalEditarPago");
    const closeButtons = document.querySelectorAll(".close");
    const addButtons = document.querySelectorAll(".add-btn");

    addButtons.forEach(btn => {
        btn.addEventListener("click", function(event) {
            event.preventDefault();
            modalAgregarPago.style.display = "block";
        });
    });

    // Función para ocultar modales y resetear la tabla
    function cerrarModal() {
        modals.forEach(modal => {
            modal.style.display = "none";
        });

        document.querySelectorAll(".tabla-container").forEach(tabla => {
            tabla.style.display = "none";
        });
    }

    closeButtons.forEach(button => {
        button.addEventListener("click", cerrarModal);
    });

    asignarEventosBotones();

    window.onclick = function(event) {
        modals.forEach(modal => {
            if (event.target == modal) {
                cerrarModal();
            }
        });
    };
</script>

</html>