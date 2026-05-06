<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit;
}

include 'php/config/database.php';

$raw_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$decoded_id = urldecode($raw_id);

// Si llega codificado en base64, intenta decodificar.
if ($decoded_id !== '' && base64_encode(base64_decode($decoded_id, true)) === $decoded_id) {
    $decoded_id = base64_decode($decoded_id);
}

$ot_id = mysqli_real_escape_string($conexion, $decoded_id);
if ($ot_id === '') {
    die('ID de orden inválido.');
}

// Aseguramos la existencia de tablas auxiliares que usaremos.
$create_extra = "CREATE TABLE IF NOT EXISTS orden_detalles (id VARCHAR(36) PRIMARY KEY, ot_id VARCHAR(100) NOT NULL, observaciones TEXT, CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UPDATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
$create_items = "CREATE TABLE IF NOT EXISTS orden_items (id VARCHAR(36) PRIMARY KEY, ot_id VARCHAR(100) NOT NULL, ps_id VARCHAR(36) NULL, nombre VARCHAR(255), referencia_bodega VARCHAR(100), tipo ENUM('producto','servicio') NOT NULL, precio DECIMAL(10,2) NULL, cantidad DECIMAL(10,2) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
$create_catalog = "CREATE TABLE IF NOT EXISTS productos_servicios (id VARCHAR(36) PRIMARY KEY, nombre VARCHAR(255) NOT NULL, tipo ENUM('producto','servicio') NOT NULL, referencia_bodega VARCHAR(100) NOT NULL UNIQUE, precio DECIMAL(10,2) NULL, descripcion TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";

mysqli_query($conexion, $create_extra);
mysqli_query($conexion, $create_items);
mysqli_query($conexion, $create_catalog);

// Migrar datos existentes de info1-info4 a observaciones si es necesario
$check_observaciones = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='orden_detalles' AND COLUMN_NAME='observaciones' AND TABLE_SCHEMA=DATABASE()";
$result_obs = mysqli_query($conexion, $check_observaciones);
if (mysqli_num_rows($result_obs) === 0) {
    // Agregar columna observaciones
    @mysqli_query($conexion, "ALTER TABLE orden_detalles ADD COLUMN observaciones TEXT AFTER ot_id");
    
    // Migrar datos existentes
    $migrate_query = "UPDATE orden_detalles SET observaciones = CONCAT_WS('\n\n', 
        CASE WHEN info1 IS NOT NULL AND info1 != '' THEN CONCAT('Campo 1: ', info1) ELSE NULL END,
        CASE WHEN info2 IS NOT NULL AND info2 != '' THEN CONCAT('Campo 2: ', info2) ELSE NULL END,
        CASE WHEN info3 IS NOT NULL AND info3 != '' THEN CONCAT('Campo 3: ', info3) ELSE NULL END,
        CASE WHEN info4 IS NOT NULL AND info4 != '' THEN CONCAT('Campo 4: ', info4) ELSE NULL END
    ) WHERE observaciones IS NULL OR observaciones = ''";
    @mysqli_query($conexion, $migrate_query);
    
    // Limpiar campos antiguos (opcional, para mantener la BD limpia)
    @mysqli_query($conexion, "ALTER TABLE orden_detalles DROP COLUMN IF EXISTS info1, DROP COLUMN IF EXISTS info2, DROP COLUMN IF EXISTS info3, DROP COLUMN IF EXISTS info4");
}


// Verificar si existen las columnas precio y cantidad en orden_items
$check_precio = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='orden_items' AND COLUMN_NAME='precio' AND TABLE_SCHEMA=DATABASE()";
$result_precio = mysqli_query($conexion, $check_precio);
if (mysqli_num_rows($result_precio) === 0) {
    @mysqli_query($conexion, "ALTER TABLE orden_items ADD COLUMN precio DECIMAL(10,2) AFTER tipo");
}

$check_cantidad = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='orden_items' AND COLUMN_NAME='cantidad' AND TABLE_SCHEMA=DATABASE()";
$result_cantidad = mysqli_query($conexion, $check_cantidad);
if (mysqli_num_rows($result_cantidad) === 0) {
    @mysqli_query($conexion, "ALTER TABLE orden_items ADD COLUMN cantidad DECIMAL(10,2) AFTER precio");
}

// Verificar si la columna referencia_bodega existe en productos_servicios, agregar solo si no existe
$check_col2 = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='productos_servicios' AND COLUMN_NAME='referencia_bodega' AND TABLE_SCHEMA=DATABASE()";
$result_col2 = mysqli_query($conexion, $check_col2);
if (mysqli_num_rows($result_col2) === 0) {
    @mysqli_query($conexion, "ALTER TABLE productos_servicios ADD COLUMN referencia_bodega VARCHAR(100) UNIQUE AFTER tipo");
}

// Orden trabajo
$query_ot = "SELECT * FROM orden_trabajos WHERE ot_id = '$ot_id' LIMIT 1";
$result_ot = mysqli_query($conexion, $query_ot);
if (!$result_ot || mysqli_num_rows($result_ot) === 0) {
    die('Orden no encontrada.');
}
$ot = mysqli_fetch_assoc($result_ot);

// Detalles adicionales registrados
$query_extra = "SELECT * FROM orden_detalles WHERE ot_id = '$ot_id' ORDER BY id DESC LIMIT 1";
$result_extra = mysqli_query($conexion, $query_extra);
$extra = mysqli_fetch_assoc($result_extra);

// Productos/servicios para elegir
$query_catalog = "SELECT * FROM productos_servicios ORDER BY nombre";
$result_catalog = mysqli_query($conexion, $query_catalog);
$catalogo = [];
while ($row = mysqli_fetch_assoc($result_catalog)) {
    $catalogo[] = $row;
}

// Productos/servicios ya asociados
$query_items = "SELECT * FROM orden_items WHERE ot_id = '$ot_id' ORDER BY id";
$result_items = mysqli_query($conexion, $query_items);
$items = [];
$total = 0;
while ($row = mysqli_fetch_assoc($result_items)) {
    $items[] = $row;
    if ($row['precio'] && $row['cantidad']) {
        $total += $row['precio'] * $row['cantidad'];
    }
}

?>
<!DOCTYPE html>
<html lang="es" class="min-h-screen">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles Orden #<?php echo htmlspecialchars($ot_id); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-100" x-data="{ modalExtra:false, modalCatalog:false, search:'' }">
    <header class="bg-white shadow-sm p-4">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Detalle orden #<?php echo htmlspecialchars($ot_id); ?></h1>
            <a href="home.php" class="text-blue-600 hover:underline">Volver</a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto p-4 space-y-6">
        <section class="bg-white p-4 rounded-lg shadow-sm">
            <h2 class="font-semibold text-lg mb-3">Datos de la orden</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div><strong>OT ID:</strong> <?php echo htmlspecialchars($ot['ot_id']); ?></div>
                <div><strong>Placa:</strong> <?php echo htmlspecialchars($ot['ot_placa']); ?></div>
                <div><strong>Empresa:</strong> <?php echo htmlspecialchars($ot['ot_empresa']); ?></div>
                <div><strong>Fecha ingreso:</strong> <?php echo htmlspecialchars($ot['ot_fecha_ingreso']); ?></div>
                <div><strong>Estado:</strong> <?php echo isset($ot['ot_estado']) ? ($ot['ot_estado']==1 ? 'Activo' : 'Inactivo') : 'N/A'; ?></div>
            </div>
        </section>

        <section class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold text-lg">Observaciones adicionales</h2>
                <button @click="modalExtra = true" <?php echo $ot['ot_estado'] != 1 ? 'disabled' : ''; ?> class="px-4 py-2 bg-blue-500 text-white rounded-sm cursor-pointer hover:bg-blue-600 <?php echo $ot['ot_estado'] != 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">Agregar/Editar</button>
            </div>
            <?php if ($extra && !empty($extra['observaciones'])): ?>
            <div class="text-sm">
                <p class="whitespace-pre-line"><?php echo htmlspecialchars($extra['observaciones']); ?></p>
                <p class="text-gray-500 text-xs mt-2"><em>Última actualización: <?php echo htmlspecialchars($extra['UPDATED_AT']); ?></em></p>
            </div>
            <?php else: ?>
            <p class="text-sm text-gray-600">No hay información adicional registrada.</p>
            <?php endif; ?>
        </section>

        <section class="bg-white p-4 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold text-lg">Productos / Servicios asociados</h2>
                <button @click="modalCatalog=true" <?php echo $ot['ot_estado'] != 1 ? 'disabled' : ''; ?> class="px-4 py-2 bg-green-500 text-white rounded-sm cursor-pointer hover:bg-green-600 <?php echo $ot['ot_estado'] != 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">Agregar productos/servicios</button>
            </div>

            <?php if (count($items)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-2 border text-left">Referencia</th>
                            <th class="p-2 border text-left">Nombre</th>
                            <th class="p-2 border text-left">Tipo</th>
                            <th class="p-2 border text-right">Precio</th>
                            <th class="p-2 border text-right">Cantidad</th>
                            <th class="p-2 border text-right">Subtotal</th>
                            <th class="p-2 border text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="p-2 border font-semibold"><?php echo htmlspecialchars($item['referencia_bodega']); ?></td>
                                <td class="p-2 border"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td class="p-2 border"><?php echo htmlspecialchars($item['tipo']); ?></td>
                                <td class="p-2 border text-right cursor-pointer hover:bg-blue-50" onclick="editarPrecio('<?php echo htmlspecialchars($item['id']); ?>', <?php echo $item['precio'] !== null ? $item['precio'] : 'null'; ?>)" title="Clic para editar">
                                    <?php echo $item['precio'] !== null ? number_format($item['precio'], 2) : '<span class="text-gray-400">-</span>'; ?>
                                </td>
                                <td class="p-2 border text-right cursor-pointer hover:bg-blue-50" onclick="editarCantidad('<?php echo htmlspecialchars($item['id']); ?>', <?php echo $item['cantidad'] !== null ? $item['cantidad'] : 'null'; ?>)" title="Clic para editar">
                                    <?php echo $item['cantidad'] !== null ? number_format($item['cantidad'], 2) : '<span class="text-gray-400">-</span>'; ?>
                                </td>
                                <td class="p-2 border text-right font-semibold"><?php echo ($item['precio'] && $item['cantidad']) ? number_format($item['precio'] * $item['cantidad'], 2) : '-'; ?></td>
                                <td class="p-2 border text-center">
                                    <button onclick="eliminarItemOrden('<?php echo htmlspecialchars($item['id']); ?>')" <?php echo $ot['ot_estado'] != 1 ? 'disabled' : ''; ?> class="px-2 py-1 bg-red-500 text-white rounded-sm text-xs cursor-pointer hover:bg-red-600 <?php echo $ot['ot_estado'] != 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($items) > 0): ?>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="5" class="p-2 border text-right">Total:</td>
                            <td class="p-2 border text-right"><?php echo number_format($total, 2); ?></td>
                            <td class="p-2 border"></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-sm text-gray-600">No hay productos ni servicios asociados a esta orden.</p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal Extra Info -->
    <div x-show="modalExtra" x-transition class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Observaciones adicionales</h3>
                <button @click="modalExtra=false" class="text-gray-500 hover:text-gray-700"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="extraForm">
                <input type="hidden" name="ot_id" value="<?php echo htmlspecialchars($ot_id); ?>">
                <div class="mb-3">
                    <textarea name="observaciones" rows="6" placeholder="Escribe aquí las observaciones adicionales de la orden..." class="w-full border p-3 rounded-sm resize-vertical" maxlength="2000"><?php echo htmlspecialchars($extra['observaciones'] ?? ''); ?></textarea>
                </div>
                <div class="flex justify-end mt-4 gap-2">
                    <button type="button" @click="modalExtra=false" class="px-4 py-2 border rounded-sm cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-sm cursor-pointer hover:bg-blue-600">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catalog -->
    <div x-show="modalCatalog" x-transition class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-5 max-h-[768px] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 sticky top-0 bg-white">
                <h3 class="text-lg font-semibold">Agregar productos y servicios</h3>
                <button @click="modalCatalog=false" class="text-gray-500 hover:text-gray-700"><i class="bi bi-x-lg"></i></button>
            </div>
            <form id="catalogForm">
                <input type="hidden" name="ot_id" value="<?php echo htmlspecialchars($ot_id); ?>">
                <div class="mb-4">
                    <input x-model="search" type="text" placeholder="Buscar por referencia o nombre..." class="w-full p-2 border rounded-sm">
                </div>
                <?php if (count($catalogo)): ?>
                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-sm border-collapse">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-2 border text-left">Referencia</th>
                                    <th class="p-2 border text-left">Nombre (Tipo)</th>
                                    <th class="p-2 border text-right">Precio</th>
                                    <th class="p-2 border text-right">Cantidad</th>
                                    <th class="p-2 border text-center">Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($catalogo as $idx => $item): ?>
                                    <tr x-show="!search || '<?php echo htmlspecialchars(strtolower($item['nombre'])); ?>'.includes(search.toLowerCase()) || '<?php echo htmlspecialchars(strtolower($item['referencia_bodega'])); ?>'.includes(search.toLowerCase())">
                                        <td class="p-2 border font-semibold"><?php echo htmlspecialchars($item['referencia_bodega']); ?></td>
                                        <td class="p-2 border"><?php echo htmlspecialchars($item['nombre'] . ' (' . $item['tipo'] . ')'); ?></td>
                                        <td class="p-2 border text-right"><?php echo $item['precio'] !== null ? number_format($item['precio'], 2) : '-'; ?></td>
                                        <td class="p-2 border">
                                            <input type="number" name="cantidad_<?php echo htmlspecialchars($item['id']); ?>" placeholder="Qty" step="0.01" min="0" class="w-20 p-1 border rounded-sm text-right">
                                        </td>
                                        <td class="p-2 border text-center">
                                            <input type="checkbox" name="items[]" value="<?php echo htmlspecialchars($item['id']); ?>" class="w-4 h-4">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No hay productos/servicios en el catálogo. Agrega primero desde la base de datos.</p>
                <?php endif; ?>
                <div class="flex justify-end gap-2 sticky bottom-0 bg-white pt-4">
                    <button type="button" @click="modalCatalog=false" class="px-4 py-2 border rounded-sm cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-sm cursor-pointer hover:bg-green-600">Agregar seleccionados</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('extraForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('php/controllers/guardar_informacion_adicional.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => alert('Error: ' + error));
        });

        document.getElementById('catalogForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('php/controllers/agregar_producto_servicio.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => alert('Error: ' + error));
        });

        function eliminarItemOrden(itemId) {
            if (!confirm('¿Eliminar este producto/servicio de la orden?')) return;

            const data = new FormData();
            data.append('item_id', itemId);

            fetch('php/controllers/eliminar_item_orden.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    window.location.reload();
                }
            })
            .catch(error => alert('Error: ' + error));
        }

        function editarPrecio(itemId, currentPrice) {
            const newPrice = prompt('Ingresa el nuevo precio:', currentPrice || '');
            if (newPrice === null) return;
            
            const data = new FormData();
            data.append('item_id', itemId);
            data.append('precio', newPrice);
            
            fetch('php/controllers/actualizar_item_orden.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    window.location.reload();
                }
            })
            .catch(error => alert('Error: ' + error));
        }

        function editarCantidad(itemId, currentQuantity) {
            const newQuantity = prompt('Ingresa la nueva cantidad:', currentQuantity || '');
            if (newQuantity === null) return;
            
            const data = new FormData();
            data.append('item_id', itemId);
            data.append('cantidad', newQuantity);
            
            fetch('php/actualizar_item_orden.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                alert(result.message);
                if (result.success) {
                    window.location.reload();
                }
            })
            .catch(error => alert('Error: ' + error));
        }
    </script>
</body>
</html>