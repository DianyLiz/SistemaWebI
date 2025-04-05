<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<aside class="menu-lateral" id="menuLateral">
    <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
        <li><a href="usuarios.php"><i class="fas fa-user"></i> Usuarios</a></li>
        <li><a href="medicos.php"><i class="fas fa-user-doctor"></i>Médicos</a></li>
        <li><a href="pacientes.php"><i class="fas fa-hospital-user"></i>Pacientes</a></li>
        <li><a href="especialidades.php"><i class="fa-solid fa-notes-medical"></i>Especialidades</a></li>
        <li><a href="horarios.php"><i class="fa-solid fa-calendar"></i>Horarios</a></li>
        <li><a href="ListadeCitas.php"><i class="fa-solid fa-stethoscope"></i>Citas</a></li>
        <li><a href="financiamientos.php"><i class="fa-solid fa-wallet"></i>Financiamientos</a></li>
        <li><a href="Auditoria.php"><i class="fa-solid fa-magnifying-glass-chart"></i>Auditoria</a></li>
    </ul>
</aside>
<script>
    const menuToggle = document.getElementById("menuToggle");
    const menuLateral = document.getElementById("menuLateral");

    menuToggle.addEventListener("click", () => {
        menuLateral.classList.toggle("activo");
    });
</script>