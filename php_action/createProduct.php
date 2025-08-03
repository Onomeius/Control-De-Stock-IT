<?php 	

require_once 'core.php';
require_once 'log_helper.php'; // Include the logging helper

$valid['success'] = array('success' => false, 'messages' => array());

if (isset($_SESSION['userRole']) && $_SESSION['userRole'] == 3) {
    $valid['success'] = false;
    $valid['messages'] = "No tienes permiso para realizar esta acción.";
    echo json_encode($valid);
    exit();
}

if($_POST) {	

	$productName 		= $_POST['productName'];

// Comprobar duplicidad de nombre de producto
$checkSql = "SELECT product_id FROM product WHERE product_name = ?";
$checkStmt = $connect->prepare($checkSql);
$checkStmt->bind_param("s", $productName);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $valid['success'] = false;
    $valid['messages'] = "El nombre del producto ya existe. Por favor, elija otro.";
    $checkStmt->close();
    $connect->close();
    echo json_encode($valid);
    exit(); // Detener la ejecución del script
}
$checkStmt->close();
  // $productImage 	= $_POST['productImage'];
  $quantity 			= $_POST['quantity'];
  $rate 					= $_POST['rate'];
  $brandName 			= $_POST['brandName'];
  $categoryName 	= $_POST['categoryName'];
  $productStatus 	= $_POST['productStatus'];

	$type = explode('.', $_FILES['productImage']['name']);
	$type = $type[count($type)-1];		
	$url = '../assests/images/stock/'.uniqid(rand()).'.'.$type;
	if(in_array($type, array('gif', 'jpg', 'jpeg', 'png', 'JPG', 'GIF', 'JPEG', 'PNG'))) {
		if(is_uploaded_file($_FILES['productImage']['tmp_name'])) {			
			if(move_uploaded_file($_FILES['productImage']['tmp_name'], $url)) {
				
				$sql = "INSERT INTO product (product_name, product_image, brand_id, categories_id, quantity, rate, active, status) 
				VALUES ('$productName', '$url', '$brandName', '$categoryName', '$quantity', '$rate', '$productStatus', 1)";

				if($connect->query($sql) === TRUE) {
					$valid['success'] = true;
					$valid['messages'] = "Creado exitosamente";	

                    // Log de actividad: Creación de producto
                    $loggedInUser = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'Desconocido';
                    log_activity($loggedInUser, 'create', 'product', $connect->insert_id, "Producto creado: $productName");

				} else {
					$valid['success'] = false;
					$valid['messages'] = "Error no se ha podido guardar";
				}

			}	else {
				return false;
			}	// /else	
		} // if
	} // if in_array 		

	$connect->close();

	echo json_encode($valid);
 
} // /if $_POST