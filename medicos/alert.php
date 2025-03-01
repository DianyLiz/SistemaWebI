<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php 
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
} else if (isset($_SESSION['success'])) {
    echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Éxito",
                        text: "' . $_SESSION['success'] . '",
                        icon: "success"
                    });
                });
            </script>';
    unset($_SESSION['success']);
}
?>