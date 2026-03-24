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
    <title>Inicio de sesión</title>
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

    <body class="min-h-screen flex flex-col justify-between" x-data="{ modalOpen: false }">
    <header class="p-4 pt-8">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between w-full items-start md:items-center gap-4">
                <a class="playfair font-bold text-2xl">
                    <img src="assets/IIJSINBG.png" alt="SIG IIJ" class="h-6 w-auto">
                </a>
                <nav class="flex items-center gap-8 text-sm">
                    <a href="ordenes_trabajo.php" class="hover:underline">Órdenes de Trabajo</a>
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
                <h2 class="text-2xl font-bold">Ingreso Vehículos</h2>
                <button @click="modalOpen = true" class="hover:bg-gray-800 bg-black p-2 mt-4 px-6 text-white rounded-sm cursor-pointer">
                    nuevo ingreso
                </button>
            </div>
            <div class="">
                <table class="w-full mt-4 border border-gray-300 rounded-lg overflow-hidden">
                    <thead class="bg-gray-200">
                        <tr class="text-center">
                            <th class="py-2">#OT</th>
                            <th class="py-2">Placa</th>
                            <th class="py-2">Empresa</th>
                            <th class="py-2">Fecha de Ingreso</th>
                            <th class="py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300">
                        <?php
                        $query = "SELECT ot_id, ot_placa, ot_empresa, ot_fecha_ingreso FROM orden_trabajos WHERE ot_estado = 1 ORDER BY ot_fecha_ingreso DESC";
                        $resultado = mysqli_query($conexion, $query);
                        
                        if (mysqli_num_rows($resultado) > 0) {
                            while ($row = mysqli_fetch_assoc($resultado)) {
                                $fecha_formateada = date('d M H:i', strtotime($row['ot_fecha_ingreso']));
                                echo '<tr class="text-center hover:bg-gray-100 transition-colors duration-200 py-2">';
                                echo '<td class="py-3">' . htmlspecialchars($row['ot_id']) . '</td>';
                                echo '<td class="py-3">' . htmlspecialchars($row['ot_placa']) . '</td>';
                                echo '<td class="py-3">' . htmlspecialchars($row['ot_empresa']) . '</td>';
                                echo '<td class="py-3">' . htmlspecialchars($fecha_formateada) . '</td>';
                                echo '<td class="py-3">';
                                echo '<a href="detalles_orden.php?id=' . urlencode($row['ot_id']) . '" class="bg-blue-500 text-white hover:bg-blue-600 px-3 py-1 rounded-sm">Ver Detalles</a>';
                                echo '<a href="#" onclick="cerrarOrdenConfirm(\'' . htmlspecialchars($row['ot_id']) . '\')" class="bg-orange-500 text-white hover:bg-orange-600 px-3 py-1 rounded-sm ml-4">Cerrar Orden de Trabajo</a>';
                                echo '<a href="#" onclick="eliminarOrden(\'' . htmlspecialchars($row['ot_id']) . '\')" class="bg-red-500 text-white hover:bg-red-600 px-3 py-1 rounded-sm ml-4">Eliminar</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr class="text-center py-4"><td colspan="5">No hay órdenes de trabajo registradas</td></tr>';
                        }
                        
                        mysqli_close($conexion);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div x-show="modalOpen" x-transition class="fixed inset-0 bg-sky-50 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-sm w-full max-w-md mx-4 shadow-lg">
            <h3 class="text-lg font-bold mb-4">Registrar Nuevo Ingreso</h3>
            <form id="ingresoForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Placa</label>
                    <input type="text" name="placa" maxlength="6" required class="w-full p-2 border border-gray-300 rounded-sm" placeholder="máx 6 caracteres">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Empresa</label>
                    <input type="text" name="empresa" required class="w-full p-2 border border-gray-300 rounded-sm">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-gray-300 rounded-sm cursor-pointer hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-sm cursor-pointer hover:bg-blue-400">Registrar</button>
                </div>
            </form>
        </div>
    </div>

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
    <script>
        function eliminarOrden(ot) {
            if (confirm('¿Está seguro de que desea eliminar esta orden de trabajo?')) {
                fetch('php/controllers/eliminar_ingreso.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'ot=' + encodeURIComponent(ot)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload(); // Recargar para actualizar la tabla
                    }
                })
                .catch(error => {
                    alert('Error en la solicitud: ' + error);
                });
            }
        }

        function cerrarOrdenConfirm(ot) {
            if (confirm('¿Estás seguro de que deseas cerrar la orden de trabajo ' + ot + '? Esta acción no se puede deshacer.')) {
                fetch('php/controllers/cerrar_orden.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'ot_id=' + encodeURIComponent(ot)
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        alert(data.message);
                        if (data.success) {
                            location.reload();
                        }
                    } catch (e) {
                        console.error('Respuesta inválida de cerrar_orden:', text);
                        alert('Error en la solicitud: respuesta no válida del servidor. Revisa la consola.');
                    }
                })
                .catch(error => {
                    alert('Error en la solicitud: ' + error);
                });
            }
        }

        document.getElementById('ingresoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('php/controllers/registrar_ingreso.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    modalOpen = false;
                    location.reload(); // Recargar para actualizar la tabla
                }
            })
            .catch(error => {
                alert('Error en la solicitud: ' + error);
            });
        });
    </script>
</body>

</html>


<?php
    }else{
        header("location:index.php");
    }
?>