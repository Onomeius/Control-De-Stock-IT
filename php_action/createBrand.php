<?php 	

require_once 'core.php';
require_once 'log_helper.php'; // Include the logging helper

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	

	$brandName = $_POST['brandName'];
  $rif = $_POST['rif'];
  $brandStatus = $_POST['brandStatus']; 

// Comprobar duplicidad de nombre de proveedor
$checkNameSql = "SELECT brand_id FROM brands WHERE brand_name = ?";
$checkNameStmt = $connect->prepare($checkNameSql);
$checkNameStmt->bind_param("s", $brandName);
$checkNameStmt->execute();
$checkNameResult = $checkNameStmt->get_result();

if ($checkNameResult->num_rows > 0) {
    $valid['success'] = false;
    $valid['messages'] = "El nombre del proveedor ya existe. Por favor, elija otro.";
    $checkNameStmt->close();
    $connect->close();
    echo json_encode($valid);
    exit();
}
$checkNameStmt->close();

// Comprobar duplicidad de RIF de proveedor
$checkRifSql = "SELECT brand_id FROM brands WHERE rif = ?";
$checkRifStmt = $connect->prepare($checkRifSql);
$checkRifStmt->bind_param("s", $rif);
$checkRifStmt->execute();
$checkRifResult = $checkRifStmt->get_result();

if ($checkRifResult->num_rows > 0) {
    $valid['success'] = false;
    $valid['messages'] = "El RIF del proveedor ya existe. Por favor, verifique.";
    $checkRifStmt->close();
    $connect->close();
    echo json_encode($valid);
    exit();
}
$checkRifStmt->close(); 

	$sql = "INSERT INTO brands (brand_name, rif, brand_active, brand_status) VALUES ('$brandName', '$rif', '$brandStatus', 1)";

	if($connect->query($sql) === TRUE) {
	 	$valid['success'] = true;
		$valid['messages'] = "Creado exitosamente";	

        // Log de actividad: Creación de marca
        $loggedInUser = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'Desconocido';
        log_activity($loggedInUser, 'create', 'brand', $connect->insert_id, "Marca creada: $brandName");

	} else {
	 	$valid['success'] = false;
	 	$valid['messages'] = "Error no se ha podido guardar";
	}
	 

	$connect->close();

	echo json_encode($valid);
 
} // /if $_POST