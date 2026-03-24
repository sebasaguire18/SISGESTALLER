<?php

include '../config/database.php';

$usernameLoging = $_POST['usernameLoging'];
$passLoging = $_POST['passLoging'];


$consultaU = " SELECT * FROM usuarios WHERE usuario_email = '$usernameLoging' AND usuario_estado = 1 ";

$resultado=mysqli_query($conexion,$consultaU);
$filas=mysqli_num_rows($resultado);

if($filas==1){
    // $passLoging = md5($passLoging);
    $consulta = " SELECT * FROM usuarios WHERE usuario_email = '$usernameLoging' AND usuario_clave = '$passLoging' AND usuario_estado = 1 ";
    
    $resultado=mysqli_query($conexion,$consulta);
    $fila=mysqli_num_rows($resultado);
    
    if($fila==1){
    
        session_start();

        $nombre="SELECT * FROM usuarios WHERE usuario_email = '$usernameLoging'";
        
        $ejecutar_nombre=mysqli_query($conexion, $nombre);
        $mostrar_nombre=mysqli_fetch_array($ejecutar_nombre);
        $_SESSION['userID']     = $mostrar_nombre['usuario_id'];
        $_SESSION['userNAME']    = $mostrar_nombre['usuario_nombre'];
        
        mysqli_free_result($resultado); 
        mysqli_close($conexion);
          
        header("location:../home.php");
    }else{

        mysqli_free_result($resultado); 
        mysqli_close($conexion);     
        
        echo 'errorPassword';
        
    }   
    
}else{
    
    mysqli_free_result($resultado); 
    mysqli_close($conexion);     
    
    echo 'errorUsername';
}   

?>