<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<aside class="menu-lateral" id="menuLateral">
    <ul>
        <li><a href="index.php"><i class="fa-solid fa-user"></i>Inicio</a></li>
        <li><a href="ListadeCitas.php">Citas Medicas</a></li>
        <li><a href="pacientes.php">Pacientes</a></li>
        <li><a href="horarios.php">Horarios Médicos</a></li>
        <li><a href="documentosmedicos.php">Documentos Médicos</a></li>
        <li><a href="#">Expedientes Médicos</a></li>
    </ul>
</aside>
<script>
    const menuToggle = document.getElementById("menuToggle");
    const menuLateral = document.getElementById("menuLateral");

    menuToggle.addEventListener("click", () => {
        menuLateral.classList.toggle("activo");
    });
</script>