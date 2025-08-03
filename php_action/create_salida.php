<?php
require_once 'db_connect.php'; // Usar db_connect.php del sistema principal

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientName = isset($_POST['clientName']) ? trim($_POST['clientName']) : '';
    $locationDestination = isset($_POST['locationDestination']) ? trim($_POST['locationDestination']) : '';
    $observation = isset($_POST['observation']) ? trim($_POST['observation']) : '';
    $product_ids = isset($_POST['product_ids']) ? json_decode($_POST['product_ids'], true) : [];
    $quantities_out = isset($_POST['quantities_out']) ? json_decode($_POST['quantities_out'], true) : [];

    error_log("create_salida.php: POST data received: " . print_r($_POST, true));

    if (empty($clientName) || empty($locationDestination) || empty($observation)) {
        $response['message'] = 'Todos los campos principales (Quien realiza la salida, Localidad de Destino, Observación) son obligatorios.';
        error_log("create_salida.php: Validation failed - missing required fields.");
        echo json_encode($response);
        exit;
    }

    if (empty($product_ids) || empty($quantities_out) || count($product_ids) !== count($quantities_out)) {
        $response['message'] = 'Debe seleccionar al menos un producto y una cantidad válida.';
        error_log("create_salida.php: Validation failed - product items invalid.");
        echo json_encode($response);
        exit;
    }

    // Iniciar transacción
    $connect->begin_transaction(); // Usar $connect
    error_log("create_salida.php: Transaction started.");

    try {
        // 1. Insertar la orden principal en la tabla 'orders'
        // Asegurarse de que todos los campos NOT NULL tengan un valor
        $sql_order = "INSERT INTO orders (
            order_date, 
            client_name, 
            client_contact, 
            location_destination, 
            sub_total, 
            vat, 
            total_amount, 
            discount, 
            grand_total, 
            paid, 
            due, 
            payment_type, 
            payment_status, 
            order_status, 
            observation
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_order = $connect->prepare($sql_order);
        if (!$stmt_order) {
            error_log("create_salida.php: Error preparando order statement: " . $connect->error);
            throw new Exception("Error al preparar la consulta de orden: " . $connect->error);
        }

        // Valores por defecto para campos no capturados en este formulario
        $default_client_contact = ''; // Vacío para este tipo de salida
        $default_sub_total = '0.00';
        $default_vat = '0.00';
        $default_total_amount = '0.00';
        $default_discount = '0.00';
        $default_grand_total = '0.00';
        $default_paid = '0.00';
        $default_due = '0.00';
        $default_payment_type = 0; // O un valor por defecto adecuado (ej. 0 para 'Salida')
        $default_payment_status = 0; // O un valor por defecto adecuado (ej. 0 para 'Pendiente')
        $default_order_status = 1; // Siempre 1 para salidas exitosas

        $stmt_order->bind_param("ssssssssssiiiss", 
            date('Y-m-d'), // order_date
            $clientName, 
            $default_client_contact, // client_contact
            $locationDestination, 
            $default_sub_total, // sub_total
            $default_vat, // vat
            $default_total_amount, // total_amount
            $default_discount, // discount
            $default_grand_total, // grand_total
            $default_paid, // paid
            $default_due, // due
            $default_payment_type, // payment_type
            $default_payment_status, // payment_status
            $default_order_status, // order_status
            $observation
        );

        if (!$stmt_order->execute()) {
            error_log("create_salida.php: Error executing order statement: " . $stmt_order->error);
            throw new Exception("Error al insertar la orden: " . $stmt_order->error);
        }
        $order_id = $connect->insert_id;
        $stmt_order->close();
        error_log("create_salida.php: Order inserted with ID: " . $order_id);

        // 2. Procesar los ítems de la salida y actualizar el stock
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = (int)$product_ids[$i];
            $quantity_out_item = (int)$quantities_out[$i];

            error_log("create_salida.php: Processing product ID: " . $product_id . ", Quantity: " . $quantity_out_item);

            // Obtener stock actual del producto con bloqueo de fila
            $stmt_product = $connect->prepare("SELECT quantity, product_name FROM product WHERE product_id = ? FOR UPDATE");
            if (!$stmt_product) {
                error_log("create_salida.php: Error preparing product stock query: " . $connect->error);
                throw new Exception("Error al preparar la consulta de stock: " . $connect->error);
            }
            $stmt_product->bind_param("i", $product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();
            $product_data = $result_product->fetch_assoc();
            $stmt_product->close();

            if (!$product_data) {
                error_log("create_salida.php: Product ID " . $product_id . " not found.");
                throw new Exception("Producto con ID $product_id no encontrado.");
            }

            $current_stock = $product_data['quantity'];
            $product_name = $product_data['product_name'];

            if ($current_stock < $quantity_out_item) {
                error_log("create_salida.php: Insufficient stock for product '" . $product_name . "'. Disponible: " . $current_stock . ", Solicitado: " . $quantity_out_item);
                throw new Exception("Stock insuficiente para el producto '" . $product_name . "'. Disponible: " . $current_stock . ", Solicitado: " . $quantity_out_item);
            }

            // Actualizar stock del producto
            $new_stock = $current_stock - $quantity_out_item;
            $stmt_update_stock = $connect->prepare("UPDATE product SET quantity = ? WHERE product_id = ?");
            if (!$stmt_update_stock) {
                error_log("create_salida.php: Error preparing stock update statement: " . $connect->error);
                throw new Exception("Error al preparar la actualización de stock: " . $connect->error);
            }
            $stmt_update_stock->bind_param("ii", $new_stock, $product_id);
            if (!$stmt_update_stock->execute()) {
                error_log("create_salida.php: Error executing stock update: " . $stmt_update_stock->error);
                throw new Exception("Error al actualizar el stock del producto ID $product_id: " . $stmt_update_stock->error);
            }
            $stmt_update_stock->close();

            // Opcional: Actualizar el estado del producto si el stock llega a 0
            if ($new_stock <= 0) {
                $stmt_update_status = $connect->prepare("UPDATE product SET active = 2, status = 2 WHERE product_id = ?");
                $stmt_update_status->bind_param("i", $product_id);
                $stmt_update_status->execute();
                $stmt_update_status->close();
                error_log("create_salida.php: Product ID " . $product_id . " status updated to inactive.");
            }

            // 3. Registrar la salida en la tabla 'salidas'
            $stmt_salida = $connect->prepare("INSERT INTO salidas (product_id, quantity_out) VALUES (?, ?)");
            if (!$stmt_salida) {
                throw new Exception("Error al preparar la inserción en salidas: " . $connect->error);
            }
            $stmt_salida->bind_param("ii", $product_id, $quantity_out_item);
            if (!$stmt_salida->execute()) {
                error_log("create_salida.php: Error executing salidas insert: " . $stmt_salida->error);
                throw new Exception("Error al insertar en salidas para producto ID $product_id: " . $stmt_salida->error);
            }
            $stmt_salida->close();
            error_log("create_salida.php: Salida recorded for product ID " . $product_id);

            // También registrar en order_item para compatibilidad con el sistema original
            // Asumimos rate y total son 0 para estas salidas simples
            $stmt_order_item = $connect->prepare("INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status) VALUES (?, ?, ?, 0, 0, 1)");
            if (!$stmt_order_item) {
                throw new Exception("Error al preparar la inserción en order_item: " . $connect->error);
            }
            $stmt_order_item->bind_param("iii", $order_id, $product_id, $quantity_out_item);
            if (!$stmt_order_item->execute()) {
                throw new Exception("Error al insertar en order_item para producto ID $product_id: " . $stmt_order_item->error);
            }
            $stmt_order_item->close();
            error_log("create_salida.php: Order item recorded for product ID " . $product_id);
        }

        // Confirmar transacción
        $connect->commit();
        error_log("create_salida.php: Transaction committed successfully.");

        $response['success'] = true;
        $response['message'] = 'Salida registrada y stock actualizado con éxito.';

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $connect->rollback();
        error_log("create_salida.php: Transaction rolled back. Error: " . $e->getMessage());
        $response['message'] = 'Error en la operación: ' . $e->getMessage();
    }
}

$connect->close();
echo json_encode($response);
?>