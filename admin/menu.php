<aside class="menu-lateral" id="menuLateral">
    <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="usuarios.php">Usuarios</a></li>
        <li><a href="medicos.php">Médicos</a></li>
        <li><a href="pacientes.php">Pacientes</a></li>
        <li><a href="#">Especialidades</a></li>
        <li><a href="#">Horarios</a></li>
        <li><a href="#">Citas</a></li>
        <li><a href="#">Financiamientos</a></li>
    </ul>
</aside>
<script>
    const menuToggle = document.getElementById("menuToggle");
    const menuLateral = document.getElementById("menuLateral");

    menuToggle.addEventListener("click", () => {
        menuLateral.classList.toggle("activo");
    });
</script>