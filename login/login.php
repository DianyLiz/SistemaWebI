<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <form action="php/session.php" method="POST">
        <br><img src="../img/logo-medicitas.png" alt="logo" class="imagen"></br>
        <label>
            <i class="fa-solid fa-user"></i>
            <input placeholder="usuario" type="text" name="usuario">
        </label>
        <label>
            <i class="fa-solid fa-lock"></i>
            <input placeholder="contraseña" type="password" name="password">
        </label>
        <a href="#" class="link">¿Olvidó su contraseña?</a>

        <button id="session">Iniciar sesión</button>
        <br><button id="register">Registrarse</button></br>
    </form>
</body>
</html>
<?php
    session_start();
    if (isset($_SESSION['error'])) {
        echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Error",
                        text: "' . $_SESSION['error'] . '",
                        icon: "error"
                    });
                });
            </script>';
        unset($_SESSION['error']);
    }
?>
