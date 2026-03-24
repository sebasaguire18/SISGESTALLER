<?php
function generar_uuid() {
    if (function_exists('com_create_guid')) {
        return trim(com_create_guid(), '{}');
    }
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function generarUUID($table, $column) {
    global $conexion;
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $id = '';
    
    do {
        $id = '';
        for ($i = 0; $i < 11; $i++) {
            $id .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        // Verificar si ya existe en la base de datos
        $queryCheck = "SELECT COUNT(*) as count FROM $table WHERE $column = '$id'";
        $result = mysqli_query($conexion, $queryCheck);
        $row = mysqli_fetch_assoc($result);
    } while ($row['count'] > 0);
    
    return $id;
}
?>