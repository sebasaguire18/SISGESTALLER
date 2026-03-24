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
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

if ($ot_id === '' || $observaciones === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$ot_id = mysqli_real_escape_string($conexion, $ot_id);

// Insert o update
$query_check = "SELECT id FROM orden_detalles WHERE ot_id = '$ot_id' LIMIT 1";
$result_check = mysqli_query($conexion, $query_check);
if ($result_check && mysqli_num_rows($result_check) > 0) {
    $row = mysqli_fetch_assoc($result_check);
    $id = mysqli_real_escape_string($conexion, $row['id']);
    $query = "UPDATE orden_detalles SET observaciones='" . mysqli_real_escape_string($conexion,$observaciones) . "' WHERE id='$id'";
} else {
    $newId = generarUUID('orden_detalles', 'id');
    $query = "INSERT INTO orden_detalles (id, ot_id, observaciones) VALUES ('$newId', '$ot_id', '" . mysqli_real_escape_string($conexion,$observaciones) . "')";
}

if (mysqli_query($conexion, $query)) {
    echo json_encode(['success' => true, 'message' => 'Observaciones guardadas correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . mysqli_error($conexion)]);
}

mysqli_close($conexion);
