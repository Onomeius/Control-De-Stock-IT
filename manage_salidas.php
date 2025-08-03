<?php 
require_once 'includes/header.php'; 
?>

<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Inicio</a></li>
		  <li class="active">Gestionar Salidas</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-list-alt"></i> Historial de Salidas</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<div id="message" class="alert" style="display:none;"></div>

				<h4>Historial de Salidas</h4>
				<table class="table table-bordered table-striped" id="salidasTable">
					<thead>
						<tr>
							<th>ID Salida</th>
							<th>Producto</th>
							<th>Cantidad</th>
							<th>Fecha</th>
						</tr>
					</thead>
					<tbody>
						<!-- Las salidas se cargarán aquí -->
					</tbody>
				</table>

			</div> <!-- /panel-body -->
			</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const messageDiv = document.getElementById('message');
        const salidasTableBody = document.querySelector('#salidasTable tbody');

        // Función para cargar el historial de salidas
        const loadSalidas = async () => {
            try {
                const response = await fetch('php_action/fetch_salidas.php');
                const salidas = await response.json();

                salidasTableBody.innerHTML = '';
                salidas.forEach(salida => {
                    const row = salidasTableBody.insertRow();
                    row.insertCell().textContent = salida.id;
                    row.insertCell().textContent = salida.product_name;
                    row.insertCell().textContent = salida.quantity_out;
                    row.insertCell().textContent = salida.date;
                });
            } catch (error) {
                console.error('Error al cargar salidas:', error);
                messageDiv.textContent = 'Error al cargar el historial de salidas.';
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-danger';
            }
        };

        // Cargar datos iniciales
        loadSalidas();
    });
</script>

<?php require_once 'includes/footer.php'; ?>