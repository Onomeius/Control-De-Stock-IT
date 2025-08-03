<?php 	

require_once 'core.php';
require_once 'log_helper.php'; // Incluir el archivo de ayuda para logs

$valid['success'] = array('success' => false, 'messages' => array(), 'order_id' => '');

if($_POST) {	

	$orderDate 						= date('Y-m-d', strtotime($_POST['orderDate']));	
  $clientName 					= $_POST['clientName'];
  $locationDestination    = $_POST['locationDestination'];
  $subTotalValue 				= $_POST['subTotalValue'];
  $vatValue 						= 	$_POST['vatValue'];
  $totalAmountValue     = $_POST['totalAmountValue'];
  $discount 						= $_POST['discount'];
  $grandTotalValue 			= $_POST['grandTotalValue'];
  $paid 								= $_POST['paid'];
  $dueValue 						= $_POST['dueValue'];
  $paymentType 					= $_POST['paymentType'];
  $paymentStatus 				= $_POST['paymentStatus'];
  $observation            = $_POST['observation'];

    error_log("createOrder.php: Datos recibidos: " . print_r($_POST, true));

    // Iniciar transacción
    $connect->begin_transaction();

    try {
        // 1. Insertar la orden principal
        $sql = "INSERT INTO orders (order_date, client_name, location_destination, sub_total, vat, total_amount, discount, grand_total, paid, due, payment_type, payment_status, order_status, observation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de orden: " . $connect->error);
        }
        $stmt->bind_param("sssdssssddiiss", 
            $orderDate, 
            $clientName, 
            $locationDestination, 
            $subTotalValue, 
            $vatValue, 
            $totalAmountValue, 
            $discount, 
            $grandTotalValue, 
            $paid, 
            $dueValue, 
            $paymentType, 
            $paymentStatus, 
            1, // order_status
            $observation
        );
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar la orden: " . $stmt->error);
        }
        $order_id = $connect->insert_id;
        $stmt->close();

        // Log de actividad: Creación de orden
        $loggedInUser = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'Desconocido';
        log_activity($loggedInUser, 'create', 'order', $order_id, "Orden creada para cliente: $clientName, Localidad: $locationDestination, Observación: $observation");

        // 2. Procesar los ítems de la orden y actualizar el stock
        if (isset($_POST['productName']) && is_array($_POST['productName'])) {
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $product_id = $_POST['productName'][$x];
                $orderedQuantity = $_POST['quantity'][$x];
                $rateValue = $_POST['rateValue'][$x];
                $totalValue = $_POST['totalValue'][$x];

                // Obtener stock actual del producto con bloqueo de fila
                $stmt = $connect->prepare("SELECT quantity FROM product WHERE product_id = ? FOR UPDATE");
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta de stock: " . $connect->error);
                }
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                $stmt->close();

                if (!$product) {
                    throw new Exception("Producto con ID $product_id no encontrado.");
                }

                $availableQuantity = $product['quantity'];

                if ($orderedQuantity > $availableQuantity) {
                    throw new Exception("Stock insuficiente para el producto ID $product_id. Disponible: $availableQuantity, Solicitado: $orderedQuantity");
                }

                // Actualizar stock del producto
                $new_stock = $availableQuantity - $orderedQuantity;
                $stmt = $connect->prepare("UPDATE product SET quantity = ? WHERE product_id = ?");
                if (!$stmt) {
                    throw new Exception("Error al preparar la actualización de stock: " . $connect->error);
                }
                $stmt->bind_param("ii", $new_stock, $product_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error al actualizar el stock del producto ID $product_id: " . $stmt->error);
                }
                $stmt->close();

                // Actualizar estado del producto si el stock es 0
                if ($new_stock <= 0) {
                    $stmt = $connect->prepare("UPDATE product SET active = 2, status = 2 WHERE product_id = ?");
                    if (!$stmt) {
                        throw new Exception("Error al preparar la actualización de estado del producto: " . $connect->error);
                    }
                    $stmt->bind_param("i", $product_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error al actualizar el estado del producto ID $product_id: " . $stmt->error);
                    }
                    $stmt->close();
                }

                // Insertar ítem de la orden
                $stmt = $connect->prepare("INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Error al preparar la inserción de ítem de orden: " . $connect->error);
                }
                $stmt->bind_param("iiisdi", $order_id, $product_id, $orderedQuantity, $rateValue, $totalValue, 1);
                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar ítem de orden para producto ID $product_id: " . $stmt->error);
                }
                $stmt->close();
            } // for
        }

        // Confirmar transacción
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Salida registrada y stock actualizado con éxito.";
        $valid['order_id'] = $order_id;

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error en la operación: " . $e->getMessage();
        error_log("createOrder.php: Error en la transacción: " . $e->getMessage());
    }
}

$connect->close();
echo json_encode($valid);
?>