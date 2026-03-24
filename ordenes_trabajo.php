<?php
    session_start();
    if($_SESSION['userID']){
    include 'php/config/database.php';
?>
<!DOCTYPE html>
<html lang="en" class="min-h-screen">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Trabajo</title>
    <link rel="icon" type="image/x-icon" href="assets/IIJSINBG.png">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
                    <a href="home.php" class="hover:underline">Panel Principal</a>
                    <a href="about.html" class="hover:underline">Vehiculos</a>
                    <a href="blog.html" class="hover:underline">Empresas</a>
                    <a href="productos_servicios.php" class="hover:underline">Productos y Servicios</a>
                    <a class="flex items-center bg-red-300 gap-2 p-2 px-6 text-black rounded-sm hover:text-white hover:bg-red-400"
                        href="php/controllers/cerrarsesion.php">
                        <i class="bi bi-box-arrow-in-left"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>
    <section class="px-4 py-24">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Todas las Órdenes de Trabajo</h2>
            </div>
            <div class="">
                <table class="w-full mt-4 border border-gray-300 rounded-lg overflow-hidden">
                    <thead class="bg-gray-200">
                        <tr class="text-center">
                            <th class="py-2">#OT</th>
                            <th class="py-2">Placa</th>
                            <th class="py-2">Empresa</th>
                            <th class="py-2">Fecha de Ingreso</th>
                            <th class="py-2">Estado</th>
                            <th class="py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        <?php
                        $query = "SELECT ot_id, ot_placa, ot_empresa, ot_fecha_ingreso, ot_estado FROM orden_trabajos ORDER BY ot_fecha_ingreso DESC";
                        $resultado = mysqli_query($conexion, $query);
                        
                        if (mysqli_num_rows($resultado) > 0) {
                            while ($row = mysqli_fetch_assoc($resultado)) {
                                $fecha_formateada = date('d M H:i', strtotime($row['ot_fecha_ingreso']));
                                
                                // Asignar color y texto según estado
                                switch($row['ot_estado']) {
                                    case 0:
                                        $estado_color = 'bg-red-500';
                                        $estado_texto = 'Eliminada';
                                        break;
                                    case 1:
                                        $estado_color = 'bg-green-500';
                                        $estado_texto = 'Activa';
                                        break;
                                    case 2:
                                        $estado_color = 'bg-blue-900';
                                        $estado_texto = 'Cerrada';
                                        break;
                                    default:
                                        $estado_color = 'bg-gray-500';
                                        $estado_texto = 'Desconocido';
                                }
                                
                                echo '<tr class="text-center hover:bg-gray-100 transition-colors duration-200 py-2">';
                                echo '<td class="py-3">' . htmlspecialchars($row['ot_id']) . '</td>';
                                echo '<td class="py-3">' . htmlspecialchars($row['ot_placa']) . '</td>';
                                echo '<td class="py-3">' . htmlspecialchars($row['ot_empresa']) . '</td>';
                                echo '<td class="py-3">' . htmlspecialchars($fecha_formateada) . '</td>';
                                echo '<td class="py-3"><span class="inline-block w-4 h-4 rounded-full ' . $estado_color . '"></span> ' . $estado_texto . '</td>';
                                echo '<td class="py-3">';
                                echo '<a href="detalles_orden.php?id=' . urlencode($row['ot_id']) . '" class="bg-blue-500 text-white hover:bg-blue-600 px-3 py-1 rounded-sm">Ver Detalles</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr class="text-center py-4"><td colspan="6">No hay órdenes de trabajo registradas</td></tr>';
                        }
                        
                        mysqli_close($conexion);
                        ?>
                    </tbody>
                </table>
            </div>
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
    }else{
        header("location:index.php");
    }
?>