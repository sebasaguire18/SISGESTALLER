<?php
    session_start();
    error_reporting(0);
    if($_SESSION['userID']){
        $usu_id=$_SESSION['userID'];
        $usu_Name = $_SESSION['userNAME'];

        header("location: home.php");
    }else{

?>
<!DOCTYPE html>
<html lang="es" class="min-h-screen">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión</title>
    <link rel="icon" type="image/x-icon" href="assets/IIJSINBG.png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            font-family: "Roboto", sans-serif;
        }

        .playfair {
            font-family: "Playfair Display", serif;
        }

        .bg-skew:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 50%;
            transform: skewY(-5deg);
            transform-origin: top left;
            z-index: -1;
            background-color: #ececec;
            min-height: 700px;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col justify-between">
    <header class="p-4 pt-8">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between w-full items-start md:items-center gap-4">
                <a class="playfair font-bold text-2xl">
                    <img src="assets/IIJSINBG.png" alt="SIG IIJ" class="h-6 w-auto">
                </a>
                <nav class="flex items-center gap-8 text-sm">
                    <a class="flex items-center bg-gray-300 gap-2 p-2 px-6 text-black rounded-sm hover:text-white hover:bg-gray-600"
                        href="">
                        <i class="bi bi-house"></i>
                        <span>Inicio</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>
    <section class="px-4 py-24">
        <div class="relative hidden md:block">
            <div class="bg-skew"></div>
        </div>
        <div class="w-full max-w-5xl mx-auto flex flex-col gap-8 max-w-[350px]">
            <div>
                <h1 class="text-2xl playfair font-bold">Iniciar sesión con tu cuenta</h1>
                <span class="text-gray-600 text-sm">
                    Por favor ingresa tus credenciales
                </span>
            </div>
            <form action="php/iniciosesion.php" method="POST" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold" for="email">Email</label>
                    <input type="text" id="email" name="usernameLoging" class="p-2 border border-gray-400 rounded-sm bg-white">
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between gap-4 items-center">
                        <label class="text-sm font-bold" for="password">Contraseña</label>
                        <a href="" class="text-xs underline text-hray-800">Olvidó su contraseña ?</a>
                    </div>
                    <input type="password" id="password" name="passLoging" class="p-2 border border-gray-400 rounded-sm bg-white">
                </div>
                <div class="flex flex-col gap-4">
                    <div class="flex gap-2 items-center">
                        <input id="rememberme" name="rememberme" type="checkbox">
                        <label for="rememberme" class="text-sm">Recuerdame</label>
                    </div>
                    <input type="submit" value="Iniciar Sesión" class="bg-sky-950 text-white hover:bg-sky-800 transition p-2 cursor-pointer rounded-sm">
                </div>
            </form>
        </div>
    </section>
    <footer class="p-4 pb-8">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex gap-4 justify-between items-center">
                <small>Copyright © 2026</small>
                <div class="flex items-center gap-4">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-github"></i></a>
                    <a href=""><i class="bi bi-slack"></i></a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>

<?php
    }
?>