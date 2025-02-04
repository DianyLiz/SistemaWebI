<!DOCTYPE html>
<html lang="es">
<head>
      <meta charset="utf-8">
      <title>Citas Médicas</title>
      <link rel="stylesheet" href="../css/cuadros.css">
   </head>
<body>

    <div class="container">
        <div class="box" onclick="alert('Has hecho clic en API LOGAN')">
            <h2>API LOGAN</h2>
            <p>Application de aplicación</p>
        </div>
        <div class="box" onclick="window.location.href='https://ejemplo.com'">
            <h2>Web Design</h2>
            <p>Graphics Design y contenido</p>
        </div>
        <div class="box" onclick="mostrarMensaje()">
            <h2>Web Tools</h2>
            <p>Skills & Abilities</p>
        </div>
        <div class="box" onclick="window.open('https://ejemplo.com', '_blank')">
            <h2>Rednuder</h2>
            <p>PERSONAL · PLANTILLA</p>
        </div>
        <div class="box" onclick="cambiarColor(this)">
            <h2>WELCOME TO FACE 🔵</h2>
            <p>Lorem ipsum eratibus velis</p>
        </div>
        <div class="box" onclick="window.location.href='#rostro'">
            <h2>Rostro</h2>
            <p>NEGOCIOS · PLANTILLA</p>
        </div>
    </div>

    <script>
        function mostrarMensaje() {
            alert('Has hecho clic en Web Tools');
        }

        function cambiarColor(elemento) {
            elemento.style.backgroundColor = '#aaffaa';
        }
    </script>

</body>
</html>