<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<aside class="menu-lateral" id="menuLateral">
    <ul>
        <li><a href="index.php"><i class="fas fa-home"></i>Inicio</a></li>
        <li><a href="ListadeCitas.php"><i class="fa-solid fa-stethoscope"></i>Citas Médicas</a></li>
        <li><a href="pacientes.php"><i class="fas fa-hospital-user"></i>Pacientes</a></li>
        <li><a href="horarios.php"><i class="fa-solid fa-calendar"></i>Horarios</a></li>
        <li><a href="documentosmedicos.php"><i class="fa-solid fa-file-medical"></i>Documentos Médicos</a></li>
        <li><a href="expedientesmedicos.php"><i class="fa-solid fa-folder-open"></i>Expedientes Médicos</a></li>
    </ul>
</aside>
<script>
    const menuToggle = document.getElementById("menuToggle");
    const menuLateral = document.getElementById("menuLateral");

    menuToggle.addEventListener("click", () => {
        menuLateral.classList.toggle("activo");
    });
</script>