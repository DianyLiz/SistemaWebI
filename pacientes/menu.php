<aside class="menu-lateral" id="menuLateral">
    <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
        <li><a href="Reserva.php"><i class="fas fa-calendar-plus"></i> Reservar Cita</a></li>
        <li><a href="especialidades.php"><i class="fas fa-stethoscope"></i> Especialidades</a></li>
        <li><a href="medicos.php"><i class="fas fa-user-md"></i> Médicos</a></li>
        <li><a href="Contacto.php"><i class="fas fa-envelope"></i> Contacto</a></li>
        <li><a href="../cerrar-sesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
    </ul>
</aside>

<script>
    const menuToggle = document.getElementById("menuToggle");
    const menuLateral = document.getElementById("menuLateral");
    if (menuToggle) {
        menuToggle.addEventListener("click", () => {
            menuLateral.classList.toggle("activo");
        });
    }

    document.querySelectorAll(".menu-lateral a").forEach(link => {
        link.addEventListener("click", (e) => {
            e.preventDefault(); // Evita la redirección inmediata
            const destino = link.getAttribute("href"); // Guarda a dónde iba

            // Redirige después de un pequeño retardo (300ms)
            setTimeout(() => {
                window.location.href = destino;
            }, 300);
        });
    });
</script>