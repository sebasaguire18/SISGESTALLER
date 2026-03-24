<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}
include '../config/database.php';
include '../helpers/functions.php';

$ot_id = isset($_POST['ot_id']) ? trim($_POST['ot_id']) : '';
$items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];

if ($ot_id === '' || count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'Seleccione al menos un producto o servicio y una orden válida']);
    exit;
}

$ot_id = mysqli_real_escape_string($conexion, $ot_id);

// Para evitar duplicados simples, se eliminan seleccionados previamente (ajustar según lógica de negocio)
$items_escaped = array_map(function($x) use ($conexion) { return "'" . mysqli_real_escape_string($conexion, trim($x)) . "'"; }, $items);
$id_list = implode(',', $items_escaped);
$query_delete = "DELETE FROM orden_items WHERE ot_id='$ot_id' AND ps_id IN ($id_list)";
mysqli_query($conexion, $query_delete);

$success = true;
$errors = [];

foreach ($items as $ps_id_raw) {
    $ps_id = trim($ps_id_raw);
    if ($ps_id === '') continue;
    $ps_id_safe = mysqli_real_escape_string($conexion, $ps_id);
    $query_ps = "SELECT * FROM productos_servicios WHERE id='$ps_id_safe' LIMIT 1";
    $result_ps = mysqli_query($conexion, $query_ps);
    if (!$result_ps || mysqli_num_rows($result_ps) === 0) {
        $errors[] = "ID $ps_id no encontrado";
        continue;
    }
    $row = mysqli_fetch_assoc($result_ps);
    $nombre = mysqli_real_escape_string($conexion, $row['nombre']);
    $referencia_bodega = mysqli_real_escape_string($conexion, $row['referencia_bodega']);
    $tipo = mysqli_real_escape_string($conexion, $row['tipo']);
    $precio = $row['precio'];
    
    // Leer cantidad del formulario
    $cantidad_key = 'cantidad_' . $ps_id;
    $cantidad = isset($_POST[$cantidad_key]) && trim($_POST[$cantidad_key]) !== '' ? floatval($_POST[$cantidad_key]) : null;
    
    $id_item = generarUUID('orden_items', 'id');
    $precio_safe = $precio !== null ? floatval($precio) : 'NULL';
    $cantidad_safe = $cantidad !== null && $cantidad > 0 ? floatval($cantidad) : 'NULL';
    $insert = "INSERT INTO orden_items (id, ot_id, ps_id, nombre, referencia_bodega, tipo, precio, cantidad) VALUES ('{$id_item}', '$ot_id', '$ps_id_safe', '$nombre', '$referencia_bodega', '$tipo', $precio_safe, $cantidad_safe)";
    if (!mysqli_query($conexion, $insert)) {
        $success = false;
        $errors[] = mysqli_error($conexion);
    }
}

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Productos/servicios agregados con éxito.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ocurrieron errores: ' . implode('; ', $errors)]);
}

mysqli_close($conexion);
