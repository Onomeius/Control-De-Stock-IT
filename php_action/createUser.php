<?php 

require_once 'db_connect.php';
require_once 'log_helper.php'; // Include the logging helper

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
	$username = $_POST['username'];

// Comprobar duplicidad de nombre de usuario
$checkSql = "SELECT user_id FROM users WHERE username = ?";
$checkStmt = $connect->prepare($checkSql);
$checkStmt->bind_param("s", $username);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $valid['success'] = false;
    $valid['messages'] = "El nombre de usuario ya existe. Por favor, elija otro.";
    $checkStmt->close();
    $connect->close();
    echo json_encode($valid);
    exit(); // Detener la ejecución del script
}
$checkStmt->close();
	$password = $_POST['password'];

	$password = md5($password);

	$sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";

	if($connect->query($sql) === TRUE) {
		$valid['success'] = true;
		$valid['messages'] = "Usuario creado exitosamente";	

        // Log de actividad: Creación de usuario
        $loggedInUser = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'Desconocido';
        log_activity($loggedInUser, 'create', 'user', $connect->insert_id, "Usuario creado: $username");

	} else {
		$valid['success'] = false;
		$valid['messages'] = "Error al crear el usuario";
	}

	$connect->close();

	echo json_encode($valid);
 
} // /if $_POST

?>