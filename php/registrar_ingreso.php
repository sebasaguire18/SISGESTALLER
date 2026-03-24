<?php
session_start();
if (!$_SESSION['userID']) {
    header("location:../index.php");
    exit();
}

include 'config/database.php';
include 'helpers/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ot = generarUUID('orden_trabajos', 'ot_id');
    $placa = strtoupper(mysqli_real_escape_string($conexion, $_POST['placa']));
    $empresa = strtoupper(mysqli_real_escape_string($conexion, $_POST['empresa']));

    // Validar que los campos no estén vacíos
    if (empty($placa) || empty($empresa)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos.']);
        exit();
    }

    // Validar que la placa tenga exactamente 6 caracteres
    if (strlen($placa) != 6) {
        echo json_encode(['success' => false, 'message' => 'La placa debe tener exactamente 6 caracteres.']);
        exit();
    }

    // Insertar en la base de datos (tabla 'orden_trabajos' con columnas: ot_id, ot_placa, ot_empresa)
    $query = "INSERT INTO orden_trabajos (ot_id, ot_placa, ot_empresa) VALUES ('$ot', '$placa', '$empresa')";

    if (mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true, 'message' => 'Ingreso registrado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el ingreso: ' . mysqli_error($conexion)]);
    }

    mysqli_close($conexion);
    exit();
}
?>