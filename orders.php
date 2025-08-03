<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
            <li><a href="dashboard.php">Inicio</a></li>
            <li class="active">Reporte de Salidas</li>
        </ol>

        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-list-alt"></i> Reporte de Salidas
            </div>
            <div class="panel-body">
                <form class="form-inline" id="reportForm">
                    <div class="form-group">
                        <label for="startDate">Fecha de Inicio:</label>
                        <input type="text" class="form-control" id="startDate" name="startDate" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="form-group">
                        <label for="endDate">Fecha de Fin:</label>
                        <input type="text" class="form-control" id="endDate" name="endDate" placeholder="YYYY-MM-DD">
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrar por Fechas</button>
                </form>
                <hr>
                <div class="pull-right">
                    <a href="#" id="exportCsvBtn" class="btn btn-success">Exportar a CSV</a>
                </div>
                <br /><br />
                <table class="table table-hover table-striped" id="manageOrderTable">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Analista</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Destino</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize datepickers
    $("#startDate").datepicker({ dateFormat: 'yy-mm-dd' });
    $("#endDate").datepicker({ dateFormat: 'yy-mm-dd' });

    // Initialize DataTable
    var manageOrderTable = $('#manageOrderTable').DataTable({
        'ajax': {
            'url': 'php_action/fetchSalidas.php',
            'type': 'POST',
            'data': function(d) {
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
            }
        },
        'order': [],
        'columns': [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 }
        ]
    });

    // Handle form submission
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        manageOrderTable.ajax.reload();
    });

    // Handle CSV export
    $('#exportCsvBtn').on('click', function(e) {
        e.preventDefault();
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        window.location.href = `php_action/exportSalidas.php?startDate=${startDate}&endDate=${endDate}`;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>