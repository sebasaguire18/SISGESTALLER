<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
include '../config/database.php';

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
$precio = isset($_POST['precio']) && trim($_POST['precio']) !== '' ? trim($_POST['precio']) : null;

if ($id === '') {
    echo json_encode(['success' => false, 'message' => 'ID es obligatorio.']);
    exit;
}

// Verificar que el producto/servicio existe
$query = "SELECT id FROM productos_servicios WHERE id = '$id'";
$result = mysqli_query($conexion, $query);
if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Producto/servicio no encontrado.']);
    exit;
}

$updates = [];
if ($nombre !== null) {
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    if ($nombre === '') {
        echo json_encode(['success' => false, 'message' => 'El nombre no puede estar vacío.']);
        exit;
    }
    $updates[] = "nombre = '$nombre'";
}
if ($precio !== null) {
    $precio = floatval($precio);
    if ($precio < 0) {
        echo json_encode(['success' => false, 'message' => 'El precio no puede ser negativo.']);
        exit;
    }
    $updates[] = "precio = $precio";
}

if (empty($updates)) {
    echo json_encode(['success' => false, 'message' => 'No hay cambios para actualizar.']);
    exit;
}

$update_query = "UPDATE productos_servicios SET " . implode(', ', $updates) . " WHERE id = '$id'";

if (mysqli_query($conexion, $update_query)) {
    echo json_encode(['success' => true, 'message' => 'Producto/servicio actualizado.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
}

mysqli_close($conexion);
?>