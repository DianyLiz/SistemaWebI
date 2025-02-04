<!DOCTYPE html>
<html lang="en" dir="ltr">
   <head>
      <meta charset="utf-8">
      <title>Citas Médicas</title>
      <link rel="stylesheet" href="../css/estilo.css">
      <link rel="stylesheet" href="../css/cuadros.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
         <ul>
            <li><a class="active" href="../principal/header.php">Salir</a></li>
         </ul>
      </nav>
   </body>
   <body>

    <div class="container">
        <div class="box" onclick="alert('Has hecho clic en Médicos')">
            <h2>Citas Médicas</h2>
            <p>Citas asignadas</p>
        </div>
        <div class="box">
            <h2>Pacientes </h2>
            <p>Listado de Pacientes</p>
        </div>
        <div class="box" onclick="mostrarMensaje()">
            <h2>Horarios Médicos</h2>
            <p>Horarios disponibles y de atención</p>
        </div>
        <div class="box">
            <h2>Documentos Médicos</h2>
            <p>Historiales Medicos</p>
        </div>
        <div class="box" onclick="window.location.href='#rostro'">
            <h2>Expedientes Medicos</h2>
            <p>Historiales de Pacientes</p>
        </div>
    </div>

    <script>
        function mostrarMensaje() {
            alert('Has hecho clic en uno de los módulos');
        }

        function cambiarColor(elemento) {
            elemento.style.backgroundColor = '#aaffaa';
        }
    </script>

</body>
</html>