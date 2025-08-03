<?php 
require_once 'includes/header.php'; 
?>

<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Inicio</a></li>
		  <li class="active">Agregar Salida</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-log-out"></i> Registrar Salida de Producto</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<form class="form-horizontal" id="salidaForm">
					<div class="form-group">
						<label for="client_name" class="col-sm-2 control-label">Persona que realiza la salida:</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="client_name" name="client_name" placeholder="Nombre del Cliente" required>
						</div>
					</div>

					<div class="form-group">
						<label for="location_destination" class="col-sm-2 control-label">Destino:</label>
						<div class="col-sm-10">
							<select class="form-control" id="location_destination" name="location_destination" required>
								<option value="">-- Selecciona un destino --</option>
								<option value="Planta Turmero">Planta Turmero</option>
								<option value="Plata la California">Plata la California</option>
								<option value="Cagua">Cagua</option>
								<option value="Chuao">Chuao</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="observation" class="col-sm-2 control-label">Observación:</label>
						<div class="col-sm-10">
							<textarea class="form-control" id="observation" name="observation" placeholder="Qué se hará con el material" rows="3" required></textarea>
						</div>
					</div>

					<hr/>

					<h4>Productos a Salir</h4>
					<div id="product-rows-container">
						<!-- Product rows will be added here by JavaScript -->
					</div>

					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<button type="button" class="btn btn-info" id="addProductRowBtn"><i class="glyphicon glyphicon-plus"></i> Añadir Producto</button>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<button type="submit" class="btn btn-success">Registrar Salida</button>
						</div>
					</div>
				</form>

				<div id="message" class="alert" style="display:none;"></div>

				<hr/>

				<h4>Stock Actual de Productos</h4>
				<table class="table table-bordered table-striped" id="stockTable">
					<thead>
						<tr>
							<th>ID</th>
							<th>Producto</th>
							<th>Stock</th>
						</tr>
					</thead>
					<tbody>
						<!-- Los productos se cargarán aquí -->
					</tbody>
				</table>

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const salidaForm = document.getElementById('salidaForm');
        const messageDiv = document.getElementById('message');
        const stockTableBody = document.querySelector('#stockTable tbody');
        const productRowsContainer = document.getElementById('product-rows-container');
        const addProductRowBtn = document.getElementById('addProductRowBtn');

        let productRowCounter = 0;
        let allProducts = []; // To store all products for dynamic selects

        // Function to load products for select dropdowns and stock table
        const loadProducts = async () => {
            try {
                const response = await fetch('php_action/fetch_products.php');
                allProducts = await response.json(); // Store all products

                stockTableBody.innerHTML = '';
                allProducts.forEach(product => {
                    const row = stockTableBody.insertRow();
                    row.insertCell().textContent = product.id;
                    row.insertCell().textContent = product.name;
                    row.insertCell().textContent = product.stock;
                });

                // Add initial product row
                addProductRow();

            } catch (error) {
                console.error('Error al cargar productos:', error);
                messageDiv.textContent = 'Error al cargar productos.';
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-danger';
            }
        };

        // Function to add a new product row
        const addProductRow = () => {
            productRowCounter++;
            const rowId = `productRow${productRowCounter}`;
            const selectId = `product_id_${productRowCounter}`;
            const quantityId = `quantity_out_${productRowCounter}`;

            const newRowHtml = `
                <div class="form-group" id="${rowId}">
                    <label for="${selectId}" class="col-sm-2 control-label">Producto:</label>
                    <div class="col-sm-4">
                        <select class="form-control product-select" id="${selectId}" name="product_ids[]" required>
                            <option value="">-- Selecciona un producto --</option>
                        </select>
                    </div>
                    <label for="${quantityId}" class="col-sm-2 control-label">Cantidad:</label>
                    <div class="col-sm-3">
                        <input type="number" class="form-control quantity-input" id="${quantityId}" name="quantities_out[]" min="1" required>
                    </div>
                    <div class="col-sm-1">
                        <button type="button" class="btn btn-danger remove-product-row-btn" data-row-id="${rowId}"><i class="glyphicon glyphicon-minus"></i></button>
                    </div>
                </div>
            `;
            productRowsContainer.insertAdjacentHTML('beforeend', newRowHtml);

            // Populate the new select with products
            const newProductSelect = document.getElementById(selectId);
            allProducts.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} (Stock: ${product.stock})`;
                newProductSelect.appendChild(option);
            });

            // Add event listener for removing row
            document.querySelector(`#${rowId} .remove-product-row-btn`).addEventListener('click', (e) => {
                const rowToRemoveId = e.target.dataset.rowId;
                document.getElementById(rowToRemoveId).remove();
            });
        };

        // Event listener for adding product row
        addProductRowBtn.addEventListener('click', addProductRow);

        // Handle form submission
        salidaForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Collect data from all product rows
            const productIds = Array.from(document.querySelectorAll('.product-select')).map(select => select.value);
            const quantitiesOut = Array.from(document.querySelectorAll('.quantity-input')).map(input => input.value);

            // Basic validation for product rows
            if (productIds.length === 0 || productIds.some(id => !id) || quantitiesOut.some(q => !q || parseInt(q) <= 0)) {
                messageDiv.textContent = 'Por favor, selecciona al menos un producto y especifica una cantidad válida para cada uno.';
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-danger';
                return;
            }

            const formData = new FormData();
            formData.append('client_name', document.getElementById('client_name').value);
            formData.append('location_destination', document.getElementById('location_destination').value);
            formData.append('observation', document.getElementById('observation').value);
            formData.append('product_ids', JSON.stringify(productIds)); // Send as JSON string
            formData.append('quantities_out', JSON.stringify(quantitiesOut)); // Send as JSON string

            try {
                const response = await fetch('php_action/process_salida.php', {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                messageDiv.textContent = result.message;
                messageDiv.style.display = 'block';
                if (result.success) {
                    messageDiv.className = 'alert alert-success';
                    salidaForm.reset();
                    productRowsContainer.innerHTML = ''; // Clear product rows
                    loadProducts(); // Reload stock and add initial row
                } else {
                    messageDiv.className = 'alert alert-danger';
                }
            } catch (error) {
                console.error('Error al procesar salida:', error);
                messageDiv.textContent = 'Error de red al procesar la salida.';
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-danger';
            }
        });

        // Initial load
        loadProducts();
    });
</script>

<?php require_once 'includes/footer.php'; ?>