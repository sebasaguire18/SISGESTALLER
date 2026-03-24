<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit;
}

include 'php/config/database.php';

$create_catalog = "CREATE TABLE IF NOT EXISTS productos_servicios (id VARCHAR(36) PRIMARY KEY, nombre VARCHAR(255) NOT NULL, tipo ENUM('producto','servicio') NOT NULL, referencia_bodega VARCHAR(100) UNIQUE, precio DECIMAL(10,2) NULL, descripcion TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
mysqli_query($conexion, $create_catalog);

// Verificar si la columna referencia_bodega existe, y agregarla solo si no existe
$check_column = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='productos_servicios' AND COLUMN_NAME='referencia_bodega' AND TABLE_SCHEMA=DATABASE()";
$result = mysqli_query($conexion, $check_column);
if (mysqli_num_rows($result) === 0) {
    @mysqli_query($conexion, "ALTER TABLE productos_servicios ADD COLUMN referencia_bodega VARCHAR(100) UNIQUE AFTER tipo");
}

$query = "SELECT * FROM productos_servicios ORDER BY created_at DESC";
$result = mysqli_query($conexion, $query);
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos y Servicios</title>
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
                    <a href="ordenes_trabajo.php" class="hover:underline">Órdenes de Trabajo</a>
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
                <h2 class="text-2xl font-bold">Catálogo de Productos y Servicios</h2>
            </div>
            <div class="space-y-6">
                <section class="bg-white p-4 rounded-lg shadow-sm">
                    <h2 class="font-semibold text-lg mb-4">Crear nuevo producto/servicio</h2>
            <form id="catalogForm" class="grid grid-cols-1 gap-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="nombre" placeholder="Nombre" required class="p-2 border rounded-sm" maxlength="255">
                    <input type="text" name="referencia_bodega" placeholder="Referencia de Bodega" required class="p-2 border rounded-sm" maxlength="100">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <select name="tipo" required class="p-2 border rounded-sm">
                        <option value="">Selecciona tipo</option>
                        <option value="producto">Producto</option>
                        <option value="servicio">Servicio</option>
                    </select>
                    <input type="number" step="0.01" min="0" name="precio" placeholder="Precio (opcional)" class="p-2 border rounded-sm">
                </div>
                <div class="grid grid-cols-1 gap-3">
                    <input type="text" name="descripcion" placeholder="Descripción (opcional)" class="p-2 border rounded-sm" maxlength="500">
                </div>
                <button type="submit" class="self-start px-4 py-2 bg-green-500 text-white rounded-sm cursor-pointer hover:bg-green-600">Guardar</button>
            </form>
        </section>

        <section class="bg-white p-4 rounded-lg shadow-sm">
            <h2 class="font-semibold text-lg mb-3">Listado</h2>
            <?php if (count($items)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-2 border">ID</th>
                                <th class="p-2 border">Nombre</th>
                                <th class="p-2 border">Referencia Bodega</th>
                                <th class="p-2 border">Tipo</th>
                                <th class="p-2 border">Precio</th>
                                <th class="p-2 border">Descripción</th>
                                <th class="p-2 border">Creado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="p-2 border break-all"><?php echo htmlspecialchars($item['id']); ?></td>
                                    <td class="p-2 border"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                    <td class="p-2 border font-semibold"><?php echo htmlspecialchars($item['referencia_bodega']); ?></td>
                                    <td class="p-2 border"><?php echo htmlspecialchars($item['tipo']); ?></td>
                                    <td class="p-2 border"><?php echo $item['precio'] !== null ? number_format($item['precio'], 2) : '-'; ?></td>
                                    <td class="p-2 border"><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                    <td class="p-2 border"><?php echo htmlspecialchars($item['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No hay productos o servicios creados.</p>
            <?php endif; ?>
        </section>
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

    <script>
        document.getElementById('catalogForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('php/controllers/guardar_producto_servicio.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => alert('Error: '+err));
        });
    </script>
</body>
</html>