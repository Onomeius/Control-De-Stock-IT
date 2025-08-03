<?php require_once 'includes/header.php'; ?>

<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Inicio</a></li>
		  <li class="active">Gestión de Facturas</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-file"></i> Gestionar Facturas</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">

				<!-- Formulario de Carga -->
                <form class="form-horizontal" id="uploadForm" enctype="multipart/form-data">
                    <h4>Cargar Nueva Factura</h4>
                    <div id="upload-messages"></div>
                    <div class="form-group">
                        <label for="numero_oc" class="col-sm-2 control-label">Numero de OC:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="numero_oc" name="numero_oc" placeholder="Ej: 98765">
                        </div>
                        <label for="numero_factura" class="col-sm-2 control-label">N° Factura:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="numero_factura" name="numero_factura" placeholder="Ej: F-00123">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="facturaFile" class="col-sm-2 control-label">Archivo:</label>
                        <div class="col-sm-10">
                            <input type="file" id="facturaFile" name="facturaFile" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">Cargar Factura</button>
                        </div>
                    </div>
                </form>

				<hr/>

				<h4>Facturas Cargadas</h4>
				<div class="remove-messages"></div>

				<div class="toolbar" style="margin-bottom: 15px;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por N° OC, N° factura o archivo..." style="width: 50%; display: inline-block; vertical-align: middle;">
					<button class="btn btn-default" id="downloadBtn" disabled style="margin-left: 10px;"> <i class="glyphicon glyphicon-download-alt"></i> Descargar Seleccionadas</button>
				</div>
				
				<table class="table table-bordered table-striped" id="invoiceTable">
					<thead>
						<tr>
							<th><input type="checkbox" id="selectAllCheckbox"></th>
                            <th>Numero de OC</th>
                            <th>N° Factura</th>
                            <th>Nombre del Archivo</th>
                            <th>Fecha de Carga</th>
						</tr>
					</thead>
                    <tbody id="invoiceTableBody">
                        <!-- Las filas se insertarán aquí dinámicamente -->
                    </tbody>
				</table>
				<!-- /table -->

			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php require_once 'includes/footer.php'; ?>
<script type="text/javascript" src="custom/js/invoice-management.js"></script>