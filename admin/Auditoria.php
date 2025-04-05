<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Auditoría del Sistema</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/estilo-admin.css">
    <link rel="stylesheet" href="../css/tabla.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <style>
        .filtros-auditoria {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .filtro-group {
            margin-bottom: 15px;
        }

        .filtro-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #0b5471;
        }

        .filtro-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
        }

        
        .btn-filtrar,
        .btn-limpiar {
            display: inline-block;
            background-color: #0b5471;
            color: white;
            margin-right: 10px;
            margin-bottom: 10px;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-filtrar:hover,
        .btn-limpiar:hover {
            background-color: rgb(10, 60, 80);
        }

        .btn-limpiar {
            background-color: #6c757d;
        }

        .btn-limpiar:hover {
            background-color: #5a6268;
        }

       
        @media (min-width: 768px) {
            .filtros-row {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }

            .filtro-group {
                flex: 1;
                min-width: 180px;
            }
        }

        @media (max-width: 768px) {
            .btn-filtrar,
            .btn-limpiar {
                width: 100%;
                margin-right: 0;
            }
        }

        
        .table-auditoria {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-auditoria th {
            background-color: #0b5471;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .table-auditoria td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
        }

        
        .accion-insert { color: #28a745; }
        .accion-update { color: #17a2b8; }
        .accion-delete { color: #dc3545; }
        
        
        .no-records {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>

    <main class="contenido">
        <div class="table-container">
            <h2>REGISTROS DE AUDITORÍA</h2>
            
            <div class="filtros-auditoria">
                <form method="get" action="">
                    <div class="filtros-row">
                        <div class="filtro-group">
                            <label for="fecha-desde">Desde:</label>
                            <input type="text" id="fecha-desde" name="fecha-desde" class="filtro-control fecha-input" placeholder="Seleccione fecha" value="<?= isset($_GET['fecha-desde']) ? htmlspecialchars($_GET['fecha-desde']) : '' ?>">
                        </div>
                        
                        <div class="filtro-group">
                            <label for="fecha-hasta">Hasta:</label>
                            <input type="text" id="fecha-hasta" name="fecha-hasta" class="filtro-control fecha-input" placeholder="Seleccione fecha" value="<?= isset($_GET['fecha-hasta']) ? htmlspecialchars($_GET['fecha-hasta']) : '' ?>">
                        </div>
                        
                        <div class="filtro-group">
                            <label for="tabla">Tabla:</label>
                            <select id="tabla" name="tabla" class="filtro-control">
                                <option value="">Todas</option>
                                <?php
                                include '../conexion.php';
                                $sqlTablas = "SELECT DISTINCT tablaAfectada FROM Auditoria ORDER BY tablaAfectada";
                                $queryTablas = $conn->prepare($sqlTablas);
                                $queryTablas->execute();
                                $tablas = $queryTablas->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($tablas as $tabla): ?>
                                    <option value="<?= htmlspecialchars($tabla['tablaAfectada']) ?>" <?= (isset($_GET['tabla']) && $_GET['tabla'] == $tabla['tablaAfectada']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tabla['tablaAfectada']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filtro-group">
                            <label for="usuario">Usuario:</label>
                            <select id="usuario" name="usuario" class="filtro-control">
                                <option value="">Todos</option>
                                <?php
                                $sqlUsuarios = "SELECT DISTINCT usuario FROM Auditoria ORDER BY usuario";
                                $queryUsuarios = $conn->prepare($sqlUsuarios);
                                $queryUsuarios->execute();
                                $usuarios = $queryUsuarios->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($usuarios as $usuario): ?>
                                    <option value="<?= htmlspecialchars($usuario['usuario']) ?>" <?= (isset($_GET['usuario']) && $_GET['usuario'] == $usuario['usuario']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usuario['usuario']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filtro-group">
                            <label for="accion">Acción:</label>
                            <select id="accion" name="accion" class="filtro-control">
                                <option value="">Todas</option>
                                <option value="INSERT" <?= (isset($_GET['accion']) && $_GET['accion'] == 'INSERT') ? 'selected' : '' ?>>Creación</option>
                                <option value="UPDATE" <?= (isset($_GET['accion']) && $_GET['accion'] == 'UPDATE') ? 'selected' : '' ?>>Modificación</option>
                                <option value="DELETE" <?= (isset($_GET['accion']) && $_GET['accion'] == 'DELETE') ? 'selected' : '' ?>>Eliminación</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <button type="submit" class="btn-filtrar">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <button type="button" onclick="window.location.href='auditoria.php'" class="btn-limpiar">
                            <i class="fas fa-broom"></i> Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table-auditoria">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Tabla</th>
                            <th>ID Registro</th>
                            <th>Acción</th>
                            <th>Usuario</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //Filtros
                        $sql = "SELECT * FROM Auditoria WHERE 1=1";
                        $params = array();
                        
                        // Filtro por fecha
                        if (!empty($_GET['fecha-desde'])) {
                            $sql .= " AND fechaAccion >= ?";
                            $params[] = $_GET['fecha-desde'] . ' 00:00:00';
                        }
                        if (!empty($_GET['fecha-hasta'])) {
                            $sql .= " AND fechaAccion <= ?";
                            $params[] = $_GET['fecha-hasta'] . ' 23:59:59';
                        }
                        
                        // Filtro por tabla
                        if (!empty($_GET['tabla'])) {
                            $sql .= " AND tablaAfectada = ?";
                            $params[] = $_GET['tabla'];
                        }
                        
                        // Filtro por usuario
                        if (!empty($_GET['usuario'])) {
                            $sql .= " AND usuario = ?";
                            $params[] = $_GET['usuario'];
                        }
                        
                        // Filtro por acción
                        if (!empty($_GET['accion'])) {
                            $sql .= " AND tipoAccion = ?";
                            $params[] = $_GET['accion'];
                        }
                        
                        $sql .= " ORDER BY fechaAccion DESC";
                        
                        $query = $conn->prepare($sql);
                        $query->execute($params);
                        $registros = $query->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($registros) > 0): 
                            foreach ($registros as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['fechaAccion']))) ?></td>
                                    <td><?= htmlspecialchars($row['tablaAfectada']) ?></td>
                                    <td><?= htmlspecialchars($row['idRegistroAfectado']) ?></td>
                                    <td class="accion-<?= strtolower($row['tipoAccion']) ?>">
                                        <?php 
                                        switch($row['tipoAccion']) {
                                            case 'INSERT': echo 'Creación'; break;
                                            case 'UPDATE': echo 'Modificación'; break;
                                            case 'DELETE': echo 'Eliminación'; break;
                                            default: echo htmlspecialchars($row['tipoAccion']);
                                        }
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                                    <td>
                                        <?php if ($row['campoAfectado'] && $row['campoAfectado'] != 'Todos'): ?>
                                            <strong><?= htmlspecialchars($row['campoAfectado']) ?>:</strong>
                                            <?= htmlspecialchars($row['valorAnterior']) ?> → <?= htmlspecialchars($row['valorPosterior']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($row['valorAnterior'] ?: $row['valorPosterior']) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; 
                        else: ?>
                            <tr>
                                <td colspan="6" class="no-records">No se encontraron registros de auditoría con los filtros seleccionados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Datepickers para filtros de fecha
        flatpickr(".fecha-input", {
            dateFormat: "Y-m-d",
            locale: "es",
            allowInput: true
        });
    </script>
</body>
</html>