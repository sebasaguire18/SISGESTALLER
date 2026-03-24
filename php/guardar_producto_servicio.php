<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
include 'config/database.php';
include 'helpers/functions.php';

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$referencia_bodega = isset($_POST['referencia_bodega']) ? trim($_POST['referencia_bodega']) : '';
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
$precio = isset($_POST['precio']) && trim($_POST['precio']) !== '' ? trim($_POST['precio']) : null;
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

if ($nombre === '' || $referencia_bodega === '' || ($tipo !== 'producto' && $tipo !== 'servicio')) {
    echo json_encode(['success' => false, 'message' => 'Nombre, referencia de bodega y tipo son obligatorios.']);
    exit;
}

$nombre = mysqli_real_escape_string($conexion, $nombre);
$referencia_bodega = mysqli_real_escape_string($conexion, $referencia_bodega);
$tipo = mysqli_real_escape_string($conexion, $tipo);
$descripcion = mysqli_real_escape_string($conexion, $descripcion);
$id = generarUUID('productos_servicios', 'id');

$insert = "INSERT INTO productos_servicios (id, nombre, referencia_bodega, tipo, precio, descripcion) VALUES ('$id', '$nombre', '$referencia_bodega', '$tipo', " . ($precio !== null ? floatval($precio) : 'NULL') . ", '$descripcion')";

if (mysqli_query($conexion, $insert)) {
    echo json_encode(['success' => true, 'message' => 'Producto/servicio guardado.']);
} else {
    $error = mysqli_error($conexion);
    // Detectar error de duplicado de referencia
    if (strpos($error, 'Duplicate entry') !== false && strpos($error, 'referencia_bodega') !== false) {
        echo json_encode(['success' => false, 'message' => 'Error: La referencia de bodega ya existe. Por favor usa una referencia única.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $error]);
    }
}

mysqli_close($conexion);
