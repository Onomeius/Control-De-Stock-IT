<?php require_once 'includes/header.php'; ?>

<?php 

$sql = "SELECT * FROM product WHERE status IN (1, 2)";
$query = $connect->query($sql);
$countProduct = $query->num_rows;

$orderSql = "SELECT * FROM orders WHERE order_status = 1";
$orderQuery = $connect->query($orderSql);
$countOrder = $orderQuery->num_rows;

$totalRevenue = 0; // Changed from "" to 0
while ($orderResult = $orderQuery->fetch_assoc()) {
    $totalRevenue += $orderResult['paid'];
}

$lowStockSql = "SELECT * FROM product WHERE quantity <= 3 AND status IN (1, 2)";
$lowStockQuery = $connect->query($lowStockSql);
$countLowStock = $lowStockQuery->num_rows;

// Calculate total inventory value
$totalInventoryValueSql = "SELECT SUM(rate * quantity) FROM product WHERE status = 1";
$totalInventoryValueResult = $connect->query($totalInventoryValueSql);
$totalInventoryValue = $totalInventoryValueResult->fetch_row()[0];

// Calculate total replenishment value
$replenishmentValue = 0;
$productSql = "SELECT product_id, quantity, rate FROM product WHERE status IN (1, 2)";
$productResult = $connect->query($productSql);
while ($productRow = $productResult->fetch_assoc()) {
    $currentQuantity = $productRow['quantity'];
    $rate = $productRow['rate'];
    $maxStock = 8;

    if ($currentQuantity < $maxStock) {
        $needed = $maxStock - $currentQuantity;
        $replenishmentValue += ($needed * $rate);
        error_log("Producto ID: " . $productRow['product_id'] . ", Cantidad actual: " . $currentQuantity . ", Necesario: " . $needed . ", Tasa: " . $rate . ", Costo de reposición: " . ($needed * $rate));
    }
}

$connect->close();

?>

<div class="dashboard-content-container">
    <div class="row">
        <div class="col-md-12">
            
            <!-- Total de productos Table -->
            <div class="dashboard-table-section">
                <h3><i class="glyphicon glyphicon-th-large"></i> Total de productos: <?php echo $countProduct; ?></h3>
                <table class="table table-striped table-bordered" id="productsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre del Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>

            <!-- Total Salidas Table -->
            <div class="dashboard-table-section">
                <h3><i class="glyphicon glyphicon-export"></i> Total Salidas: <?php echo $countOrder; ?></h3>
                <table class="table table-striped table-bordered" id="ordersTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Fecha de Salida</th>
                            <th>Persona que realiza la salida</th>
                            <th>Destino</th>
                            <th>Observación</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>

            <!-- Valor para reponer stock Table -->
            <div class="dashboard-table-section">
                <h3><i class="glyphicon glyphicon-refresh"></i> Valor para reponer stock: $<?php echo number_format($replenishmentValue, 2); ?></h3>
                <table class="table table-striped table-bordered" id="replenishmentTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre del Producto</th>
                            <th>Cantidad Actual</th>
                            <th>Cantidad Necesaria</th>
                            <th>Costo de Reposición</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>

            <!-- Inventario bajo Table -->
            <div class="dashboard-table-section">
                <h3><i class="glyphicon glyphicon-warning-sign"></i> Inventario bajo: <?php echo $countLowStock; ?></h3>
                <table class="table table-striped table-bordered" id="lowStockTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre del Producto</th>
                            <th>Cantidad Actual</th>
                            <th>Umbral Bajo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>

            <!-- Valor Total Inventario Table -->
            <div class="dashboard-table-section">
                <h3><i class="glyphicon glyphicon-usd"></i> Valor Total Inventario: $<?php echo number_format($totalInventoryValue, 2); ?></h3>
                <table class="table table-striped table-bordered" id="totalInventoryTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Nombre del Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize DataTables for each table
    var productsTable = $('#productsTable').DataTable({
        'ajax': 'php_action/fetchProducts.php',
        'order': [],
        'columns': [
            { 'data': 0 }, // product_name
            { 'data': 1 }, // quantity
            { 'data': 2 }, // rate
            { 'data': 3 }  // status
        ]
    });

    var ordersTable = $('#ordersTable').DataTable({
        'ajax': 'php_action/fetchOrders.php',
        'order': [],
        'columns': [
            { 'data': 0 }, // salida_date
            { 'data': 1 }, // client_name
            { 'data': 2 }, // location_destination
            { 'data': 3 }, // observation
            { 'data': 4 }  // quantity_out
        ]
    });

    var replenishmentTable = $('#replenishmentTable').DataTable({
        'ajax': 'php_action/fetchReplenishmentProducts.php',
        'order': [],
        'columns': [
            { 'data': 0 }, // product_name
            { 'data': 1 }, // current quantity
            { 'data': 2 }, // needed quantity
            { 'data': 3 }  // replenishment cost
        ]
    });

    var lowStockTable = $('#lowStockTable').DataTable({
        'ajax': 'php_action/fetchLowStockProducts.php',
        'order': [],
        'columns': [
            { 'data': 0 }, // product_name
            { 'data': 1 }, // quantity
            { 'data': 2 }  // low stock threshold
        ]
    });

    var totalInventoryTable = $('#totalInventoryTable').DataTable({
        'ajax': 'php_action/fetchProducts.php',
        'order': [],
        'columns': [
            { 'data': 0 }, // product_name
            { 'data': 1 }, // quantity
            { 'data': 2 }, // rate
            { 
                'data': null, // Use null to indicate that data will be generated
                'render': function ( data, type, row ) {
                    // Calculate total value for each product (quantity * rate)
                    var totalValue = row[1] * row[2];
                    return '$' + totalValue.toFixed(2); // Format as currency
                }
            }
        ]
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>