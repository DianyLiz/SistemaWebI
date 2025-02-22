<aside class="menu-lateral" id="menuLateral">
    <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="ListadeCitas.php">Citas Medicas</a></li>
        <li><a href="pacientes.php">Pacientes</a></li>
        <li><a href="#">Horarios Médicos</a></li>
        <li><a href="#">Documentos Médicos</a></li>
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