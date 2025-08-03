<?php 	

require_once 'core.php';
require_once 'log_helper.php'; // Include the logging helper

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) { 	

	$categoriesName = $_POST['categoriesName'];
  $categoriesStatus = $_POST['categoriesStatus']; // Este es el valor del select (1 o 2)

// Comprobar duplicidad de nombre de categoría
$checkSql = "SELECT categories_id FROM categories WHERE categories_name = ?";
$checkStmt = $connect->prepare($checkSql);
$checkStmt->bind_param("s", $categoriesName);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $valid['success'] = false;
    $valid['messages'] = "El nombre de la categoría ya existe. Por favor, elija otro.";
    $checkStmt->close();
    $connect->close();
    echo json_encode($valid);
    exit(); // Detener la ejecución del script
}
$checkStmt->close();

	// Insertar en categories_status en lugar de categories_active
	$sql = "INSERT INTO categories (categories_name, categories_active, categories_status) 
	VALUES (?, ?, ?)";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sii", $categoriesName, $categoriesStatus, $categoriesStatus);

	if($stmt->execute()) {
	 	$valid['success'] = true;
		$valid['messages'] = "Creado exitosamente";	

        // Log de actividad: Creación de categoría
        $loggedInUser = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'Desconocido';
        log_activity($loggedInUser, 'create', 'category', $connect->insert_id, "Categoría creada: $categoriesName, Estado: $categoriesStatus");

	} else {
	 	$valid['success'] = false;
	 	$valid['messages'] = "Error no se ha podido guardar: " . $stmt->error;
	}

	$stmt->close();
	$connect->close();

	echo json_encode($valid);
 
} // /if $_POST