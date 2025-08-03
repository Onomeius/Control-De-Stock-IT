<?php 
require_once 'php_action/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_brand'])) {
        $id = $_POST['id'];
        $stmt = $connect->prepare("DELETE FROM brands WHERE brand_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: brand.php");
        exit();
    }
}

require_once 'includes/header.php'; 
include('modal/brandModal.php');
?>
 <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
<div class="row">
	<div class="col-md-12">

		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Inicio</a></li>		  
		  <li class="active">Proveedores</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-edit"></i> Listado de Proveedores </div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div class="remove-messages"></div>

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<button class="btn btn-default button1" data-toggle="modal" data-target="#addBrandModel"> <i class="glyphicon glyphicon-plus-sign"></i> Agregar Proveedores  </button>
				</div> <!-- /div-action -->				
				
				<table class="table" id="manageBrandTable">
					<thead>
						<tr>							
							<th>Nombre</th>
							<th>RIF</th>
							<th>Estado</th>
							<th style="width:15%;">Opciones</th>
						</tr>
					</thead>
				</table>
				<!-- /table -->

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->		
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->







<script src="custom/js/brand.js"></script>

<?php require_once 'includes/footer.php'; ?>