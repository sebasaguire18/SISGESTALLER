<?php
session_start();
if (!$_SESSION['userID']) {
    header("location:../index.php");
    exit();
}

include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ot = mysqli_real_escape_string($conexion, $_POST['ot']);

    // Validar que el OT no esté vacío
    if (empty($ot)) {
        echo json_encode(['success' => false, 'message' => 'ID de orden de trabajo requerido.']);
        exit();
    }

    // Cambiar el estado de 1 a 0 (soft delete)
    $query = "UPDATE orden_trabajos SET ot_estado = 0 WHERE ot_id = '$ot'";

    if (mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Orden de trabajo eliminada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la orden de trabajo: ' . mysqli_error($conexion)]);
    }

    mysqli_close($conexion);
    exit();
}
?>