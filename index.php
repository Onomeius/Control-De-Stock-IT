<?php 
require_once 'php_action/db_connect.php';

session_start();

if(isset($_SESSION['userId'])) {
	header('location: dashboard.php');	
}

$errors = array();

if($_POST) {		

	$username = $connect->real_escape_string($_POST['username']); // Escapando caracteres especiales
	$password = $_POST['password'];

	if(empty($username) || empty($password)) {
		if($username == "") {
			$errors[] = "Se requiere nombre de usuario";
		} 

			if($password == "") {
			$errors[] = "Se requiere contraseña";
		}
	} else {
		$sql = "SELECT * FROM users WHERE username = '$username'";
		$result = $connect->query($sql);

		if($result->num_rows == 1) {
			$user_data = $result->fetch_assoc();
			if($user_data['active'] == 0) {
				$errors[] = "Tu cuenta está desactivada. Por favor, contacta al administrador.";
			} else {
				$password = md5($password);
				// exists
				$mainSql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
				$mainResult = $connect->query($mainSql);

				if($mainResult->num_rows == 1) {
					$value = $mainResult->fetch_assoc();
					$user_id = $value['user_id'];
					$_SESSION['userName'] = $value['username'];
					$_SESSION['userRole'] = $value['role']; // Store user role

					// set session
					$_SESSION['userId'] = $user_id;

					header('location: dashboard.php');	
				} else{
					
					$errors[] = "Combinación incorrecta de nombre de usuario y/o contraseña";
				} // /else
			}
		} else {		
			$errors[] = "El nombre de usuario no existe";		
		} // /else
	} // /else not empty username // password
	
} // /if $_POST
?>

<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <link rel="shortcut icon" href="assests/images/logo.png" /> 
	<title>Control de Stock IT</title>

	<!-- bootstrap -->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
	<!-- bootstrap theme-->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
	<!-- font awesome -->
	<link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">

  <!-- custom css -->
  <link rel="stylesheet" href="custom/css/custom.css">	

  <!-- jquery -->
	<script src="assests/jquery/jquery.min.js"></script>
  <!-- jquery ui -->  
  <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
  <script src="assests/jquery-ui/jquery-ui.min.js"></script>

  <!-- bootstrap js -->
	<script src="assests/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
	
	<div class="container login-container">
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<div class="panel panel-default" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); border-radius: 8px; overflow: hidden;">
					<div class="panel-heading" style="background-color: #245580; color: #ffffff; text-align: center; padding: 15px;">
                        <img src="assests/images/logo.png" alt="Logo" style="max-width: 150px; margin-bottom: 10px;">
					  <h3 class="panel-title" style="margin: 0;">Inicio de sesión</h3>
					</div>
					<div class="panel-body" style="padding: 20px;">

						<div class="messages">
							<?php if($errors) {
								foreach ($errors as $key => $value) {
									echo '<div class="alert alert-warning" role="alert">
									<i class="glyphicon glyphicon-exclamation-sign"></i>
									'.$value.'</div>';										
									}
								} ?>
						</div>

						<form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" id="loginForm">
							<fieldset>
							  <div class="form-group">
									<label for="username" class="col-sm-3 control-label">Usuario</label>
									<div class="col-sm-9">
									  <input type="text" class="form-control" id="username" name="username" placeholder="Nombre de usuario" autocomplete="off" required />
									</div>
								</div>
								<div class="form-group">
									<label for="password" class="col-sm-3 control-label">Contraseña</label>
									<div class="col-sm-9">
									  <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" autocomplete="off" required />
									</div>
								</div>								
								<div class="form-group">
									<div class="col-sm-offset-3 col-sm-9">
									  <button type="submit" class="btn btn-primary" style="background-color: #007bff; border-color: #007bff;"> <i class="glyphicon glyphicon-log-in"></i> Ingresar</button>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
					<!-- panel-body -->
				</div>
				<!-- /panel -->
			</div>
			<!-- /col-md-4 -->
		</div>
		<!-- /row -->
	</div>
	<!-- container -->	
</body>
</html>