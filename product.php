<?php 
require_once 'php_action/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_product'])) {
        $id = $_POST['id'];
        $stmt = $connect->prepare("DELETE FROM product WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: product.php");
        exit();
    }
}

require_once 'includes/header.php'; 
require_once 'modal/productModal.php'; 
?>
 <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
<div class="row">
	<div class="col-md-12">

		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Inicio</a></li>		  
		  <li class="active">Productos</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-edit"></i> Listado de productos</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div class="remove-messages"></div>

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<?php if($_SESSION['userRole'] != 3): // Only show for non-visualizers ?>
					<button class="btn btn-default button1" data-toggle="modal" id="addProductModalBtn" data-target="#addProductModal"> <i class="glyphicon glyphicon-plus-sign"></i> Agregar producto </button>
					<?php endif; ?>
				</div> <!-- /div-action -->				
				
				<table class="table" id="manageProductTable">
					<thead>
						<tr>
							<th style="width:10%;">Imagen</th>							
							<th>Nombre del producto</th>
							<th>Precio</th>							
							<th>Stock</th>
							<th>Proveedor </th>
							<th>Categoría</th>
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






<script src="custom/js/product.js"></script>

<?php require_once 'includes/footer.php'; ?>