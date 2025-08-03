<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 

?>

<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Inicio</a></li>
		  <li class="active">Reporte de Reposición</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-list"></i> Reporte de Reposición</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div class="pull pull-right" style="padding-bottom:20px;">
					<a href="php_action/exportReplenishment.php" class="btn btn-success">Exportar a CSV</a>
				</div>
				<br /><br />

				<div class="table-responsive">
				<table class="table" id="replenishmentReportTable">
					<thead>
						<tr>
							<th>Producto</th>
							<th>Cantidad Actual</th>
							<th>Cantidad Necesaria</th>
							<th>Precio Unitario</th>
							<th>Costo de Reposición</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$totalReplenishmentCost = 0;
					$productSql = "SELECT product_name, quantity, rate FROM product WHERE status IN (1, 2)";
					$productResult = $connect->query($productSql);

					while($row = $productResult->fetch_array()) {
						$productName = $row[0];
						$currentQuantity = $row[1];
						$rate = $row[2];
						$maxStock = 8;
						$neededQuantity = 0;
						$replenishmentCost = 0;

						if ($currentQuantity < $maxStock) {
							$neededQuantity = $maxStock - $currentQuantity;
							$replenishmentCost = $neededQuantity * $rate;
							$totalReplenishmentCost += $replenishmentCost;
						}

						if ($neededQuantity > 0) {
							echo '<tr>';
							echo '<td>'. $productName .'</td>';
							echo '<td>'. $currentQuantity .'</td>';
							echo '<td>'. $neededQuantity .'</td>';
							echo '<td>'. number_format($rate, 2) .'</td>';
							echo '<td>'. number_format($replenishmentCost, 2) .'</td>';
							echo '</tr>';
						}
					}
					?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="4" class="text-right">Costo Total de Reposición:</th>
							<th><?php echo number_format($totalReplenishmentCost, 2); ?></th>
						</tr>
					</tfoot>
				</table>
				</div> <!--/table-responsive-->

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php require_once 'includes/footer.php'; ?>