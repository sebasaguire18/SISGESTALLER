<?php
include '../config/database.php';

class AuthController {
    public static function login($username, $password) {
        global $conexion;
        $consultaU = "SELECT * FROM usuarios WHERE usuario_email = '$username' AND usuario_estado = 1";
        $resultado = mysqli_query($conexion, $consultaU);
        $filas = mysqli_num_rows($resultado);

        if ($filas == 1) {
            $consulta = "SELECT * FROM usuarios WHERE usuario_email = '$username' AND usuario_clave = '$password' AND usuario_estado = 1";
            $resultado = mysqli_query($conexion, $consulta);
            $fila = mysqli_num_rows($resultado);

            if ($fila == 1) {
                session_start();
                $nombre = "SELECT * FROM usuarios WHERE usuario_email = '$username'";
                $ejecutar_nombre = mysqli_query($conexion, $nombre);
                $mostrar_nombre = mysqli_fetch_array($ejecutar_nombre);
                $_SESSION['userID'] = $mostrar_nombre['usuario_id'];
                $_SESSION['userNAME'] = $mostrar_nombre['usuario_nombre'];
                return true;
            }
        }
        return false;
    }

    public static function logout() {
        session_start();
        session_destroy();
        header('location:../index.php');
        exit;
    }
}
?>