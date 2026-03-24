<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
include '../config/database.php';

$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
if ($item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de item inválido']);
    exit;
}

$query = "DELETE FROM orden_items WHERE id = $item_id";
if (mysqli_query($conexion, $query)) {
    echo json_encode(['success' => true, 'message' => 'Item eliminado con éxito']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysqli_error($conexion)]);
}

mysqli_close($conexion);
