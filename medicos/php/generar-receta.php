<?php
include '../../conexion.php';

if (isset($_GET['idDocumento'])) {
    $idDocumento = intval($_GET['idDocumento']);

    $sql = "SELECT  
                d.idDocumento,
                d.idPaciente, 
                CONCAT(u1.nombre, ' ', u1.apellido) AS paciente, 
                h.fecha as fechaCita, 
                Concat(u2.nombre, ' ', u2.apellido) as Medico, 
                d.descripcion
            FROM DocumentosMedicos d
            LEFT JOIN [dbo].[Pacientes] p ON d.idPaciente = p.idPaciente
            LEFT JOIN Citas c ON d.idCita = c.idCita
            LEFT JOIN HorariosMedicos h ON c.idHorario = h.idHorario
            LEFT JOIN Medicos m ON h.idMedico = m.idMedico
            LEFT JOIN [dbo].[Usuarios] u1 ON p.idUsuario = u1.idUsuario  
            LEFT JOIN Usuarios u2 ON m.idUsuario = u2.idUsuario
            WHERE d.idDocumento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$idDocumento]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($documento) {

        $plantilla = file_get_contents('../../receta.html');

        $plantilla = str_replace('{{paciente}}', $documento['paciente'], $plantilla);
        $plantilla = str_replace('{{fechaCita}}', $documento['fechaCita'], $plantilla);
        $plantilla = str_replace('{{medico}}', $documento['Medico'], $plantilla);
        $plantilla = str_replace('{{descripcion}}', $documento['descripcion'], $plantilla);

        require '../php/vendor/autoload.php'; 
        
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($plantilla);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("receta_medica.pdf", array("Attachment" => true));
    } else {
        echo "Documento no encontrado.";
    }
} else {
    echo "ID de documento no proporcionado.";
}
?>