<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=UTF-8');
// Evita salida previa problemática
ob_start();

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    ob_end_flush();
    exit;
}
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ot_id = isset($_POST['ot_id']) ? trim($_POST['ot_id']) : '';
    if (empty($ot_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de orden inválido']);
        exit;
    }

    // Actualizar estado de la orden a 2
    $query_ot = "UPDATE orden_trabajos SET ot_estado = 2 WHERE ot_id = '$ot_id'";
    $result_ot = mysqli_query($conexion, $query_ot);

    if ($result_ot) {
        // Actualizar relacionados si existen (asumiendo campos estado)
        // Ej. orden_items, orden_detalles
        $query_items = "UPDATE orden_items SET estado = 2 WHERE ot_id = '$ot_id'";
        mysqli_query($conexion, $query_items);

        $query_detalles = "UPDATE orden_detalles SET estado = 2 WHERE ot_id = '$ot_id'";
        mysqli_query($conexion, $query_detalles);

        echo json_encode(['success' => true, 'message' => 'Orden de trabajo cerrada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cerrar la orden: ' . mysqli_error($conexion)]);
    }

    mysqli_close($conexion);
    exit();
}
?>