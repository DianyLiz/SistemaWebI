<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php include 'header.php'; ?>
    <main>
        <section id="citas-medicas" class="container">
            <h2>Citas Médicas</h2>
            <p>Citas Asignadas</p>
            <button type="button" class="btn-reservar" onclick="location.href='ListadeCitas.php'">
                Lista de Citas
            </button>

        </section>

        <section id="pacientes" class="container">
            <h2>Pacientes</h2>
            <p>Citas Asignadas</p>
            <button type="button" class="btn-reservar">
                Lista de Pacientes
            </button>
        </section>

        <section id="horarios" class="container">
            <h2>Horarios de Medicos</h2>
            <p>Horarios disponibles</p>
            <button type="button" class="btn-reservar">
                Crear Horario
            </button>
            <button type="button" class="btn-reservar">
                Eliminar Horario
            </button>
        </section>

        <section id="documentos" class="container">
            <h2>Documentos Medicos</h2>
            <p>Historiales Médicos</p>
            <button type="button" class="btn-reservar">
                Crear Documento
            </button>
            <button type="button" class="btn-reservar">
                Eliminar Documento
            </button>
            <button type="button" class="btn-reservar">
                Modificar Documento
            </button>
        </section>


        <section id="expedientes" class="container">
            <h2>Expedientes Medicos</h2>
            <p>Historiales de Pacientes</p>
            <button type="button" class="btn-reservar">
                Crear Historial
            </button>
            <button type="button" class="btn-reservar">
                Eliminar Historial
            </button>
            <button type="button" class="btn-reservar">
                Modificar Historial
            </button>
        </section>
    </main>
    <footer class="footer">
        <p>&copy; 2025 MediCitas - Todos los derechos reservados</p>
    </footer>
</body>

</html>