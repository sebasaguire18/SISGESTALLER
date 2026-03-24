<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
include 'config/database.php';

$item_id = isset($_POST['item_id']) ? trim($_POST['item_id']) : '';
$precio = isset($_POST['precio']) ? $_POST['precio'] : null;
$cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : null;

if ($item_id === '') {
    echo json_encode(['success' => false, 'message' => 'ID de item inválido']);
    exit;
}

$item_id_safe = mysqli_real_escape_string($conexion, $item_id);

// Determinar qué campo actualizar
if ($precio !== null && trim($precio) !== '') {
    $precio_safe = floatval($precio);
    $query = "UPDATE orden_items SET precio = $precio_safe WHERE id = '$item_id_safe'";
} elseif ($cantidad !== null && trim($cantidad) !== '') {
    $cantidad_safe = floatval($cantidad);
    $query = "UPDATE orden_items SET cantidad = $cantidad_safe WHERE id = '$item_id_safe'";
} else {
    echo json_encode(['success' => false, 'message' => 'Debe proporcionar un valor válido']);
    exit;
}

if (mysqli_query($conexion, $query)) {
    echo json_encode(['success' => true, 'message' => 'Item actualizado con éxito']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
}

mysqli_close($conexion);
