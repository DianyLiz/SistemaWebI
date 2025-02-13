<?php 
    session_start();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCitas - Citas Médicas</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css"> 
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
        <li class="btn"><?php echo $_SESSION['usuario']['nombre'] . " " . $_SESSION['usuario']['apellido']; ?></li>
        </ul>

         <ul>
            <li><a class="" href="../principal/index.php">Salir</a></li>
         </ul>
      </nav>
    
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

    <footer class="footer">
        <p>&copy; 2025 MediCitas - Todos los derechos reservados</p>
    </footer>
</body>
</html>
