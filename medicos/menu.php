<aside class="menu-lateral" id="menuLateral">
    <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="usuarios.php">Citas Medicas</a></li>
        <li><a href="#">Pacientes</a></li>
        <li><a href="#">Horarios Medicos</a></li>
        <li><a href="#">Documentos Medicos</a></li>
        <li><a href="#">Expedientes Medicos</a></li>
    </ul>
</aside>
<script>
    const menuToggle = document.getElementById("menuToggle");
    const menuLateral = document.getElementById("menuLateral");

    menuToggle.addEventListener("click", () => {
        menuLateral.classList.toggle("activo");
    });
</script>