<?php require_once 'php_action/core.php'; ?>
<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <link rel="shortcut icon" href="assests/images/logo.png" /> 
	<title>Control de Stock IT</title>
	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap" rel="stylesheet">
	<!-- bootstrap -->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
	<!-- bootstrap theme-->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
	<!-- font awesome -->
	<link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">

  <!-- custom css -->
  <link rel="stylesheet" href="custom/css/custom.css">

	<!-- DataTables -->
  <link rel="stylesheet" href="assests/plugins/datatables/jquery.dataTables.min.css">

  <!-- file input -->
  <link rel="stylesheet" href="assests/plugins/fileinput/css/fileinput.min.css">

  <!-- jquery -->
	<script src="assests/jquery/jquery.min.js"></script>
  <!-- jquery ui -->  
  <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
  <script src="assests/jquery-ui/jquery-ui.min.js"></script>

  <!-- bootstrap js -->
	<script src="assests/bootstrap/js/bootstrap.min.js"></script>

</head>

<body>

<nav class="navbar navbar-default navbar-static-top">
		<div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
    
  
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <!-- <a class="navbar-brand" href="#">Brand</a> -->
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">      

      <ul class="nav navbar-nav navbar-right">        

      	<li id="navDashboard"><a href="index.php"><i class="glyphicon glyphicon-list-alt"></i>  Inicio</a></li>        
        
        <?php if($_SESSION['userRole'] != 3): // Hide for Visualizer ?>
        <li id="navBrand"><a href="brand.php"><i class="glyphicon glyphicon-briefcase"></i> Proveedores </a></li>        

        <li id="navCategories"><a href="categories.php"> <i class="glyphicon glyphicon-tags"></i> Categorías</a></li>        
        <?php endif; ?>

        <li id="navProduct"><a href="product.php"> <i class="glyphicon glyphicon-barcode"></i> Productos </a></li>     

        <?php if($_SESSION['userRole'] == 1): // Only for SuperAdmin ?>
        <li id="navUser"><a href="users.php"> <i class="glyphicon glyphicon-user"></i> Usuarios </a></li>     
        <?php endif; ?>

        <li class="dropdown" id="navOrder">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-shopping-cart"></i> Salidas <span class="caret"></span></a>
          <ul class="dropdown-menu">            
            <li id="topNavAddOrder"><a href="add_salida.php"> <i class="glyphicon glyphicon-plus"></i> Agregar Salida</a></li>            
            <li id="topNavManageOrder"><a href="orders.php?o=manord"> <i class="glyphicon glyphicon-edit"></i> Gestionar Salidas</a></li>            
          </ul>
        </li> 

        <li id="navReport" class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-list-alt"></i> Reportes <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="report.php">Reporte de Salidas</a></li>
            <li><a href="replenishment_report.php">Reporte de Reposición</a></li>
          </ul>
        </li>

        <li id="navInvoices"><a href="invoices.php"> <i class="glyphicon glyphicon-file"></i> Facturas</a></li>

        <?php if(isset($_SESSION['userRole']) && $_SESSION['userRole'] == 1): // Only show for SuperAdmin ?>
        <li id="navLogs"><a href="logs.php"> <i class="glyphicon glyphicon-file"></i> Logs</a></li>
        <?php endif; ?>

        <?php if($_SESSION['userRole'] != 3): // Hide for Visualizer ?>
        <li id="navGraphs"><a href="graphs.php"> <i class="glyphicon glyphicon-stats"></i> Gráficas</a></li>
        <?php endif; ?>

        <li class="dropdown" id="navSetting">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-user"></i> <span class="caret"></span></a>
          <ul class="dropdown-menu">            
            <li><a href="#"> <i class="glyphicon glyphicon-user"></i> Usuario: <?php echo $_SESSION['userName']; ?></a></li>
            <li role="separator" class="divider"></li>
            <?php if($_SESSION['userRole'] != 3): // Hide for Visualizer ?>
            <li id="topNavSetting"><a href="setting.php"> <i class="glyphicon glyphicon-wrench"></i> Configuración</a></li>            
            <?php endif; ?>
            <li id="topNavLogout"><a href="logout.php"> <i class="glyphicon glyphicon-log-out"></i> Salir</a></li>            
          </ul>
        </li>        
               
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
	</nav>
	<div class="container">