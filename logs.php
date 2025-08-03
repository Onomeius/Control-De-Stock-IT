<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';

$log_file = __DIR__ . '/logs/activity.log';
$log_content = '';

if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $log_lines = explode("\n", $log_content);
    $log_lines = array_filter($log_lines); // Remove empty lines
    $log_lines = array_reverse($log_lines); // Show newest entries first
} else {
    $log_content = "No hay registros de actividad aún.";
}

?>

<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Inicio</a></li>
		  <li class="active">Registros de Actividad</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading">
	        		<i class="glyphicon glyphicon-list"></i> Registros de Actividad
	        	</div>
			</div> <!-- /panel-heading -->
			<div class="panel-body">
				<div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
					<table class="table table-bordered table-hover table-striped" id="logTable">
						<thead>
							<tr>
								<th>Timestamp</th>
								<th>Usuario</th>
								<th>Acción</th>
								<th>Tipo de Entidad</th>
								<th>ID de Entidad</th>
								<th>Detalles</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($log_lines)): ?>
								<?php foreach ($log_lines as $line): ?>
									<?php
									$parts = explode(' | ', $line);
									$timestamp = isset($parts[0]) ? str_replace('[', '', $parts[0]) : '';
									$user = isset($parts[1]) ? str_replace('User: ', '', $parts[1]) : '';
									$action = isset($parts[2]) ? str_replace('Action: ', '', $parts[2]) : '';
									$entity_type = isset($parts[3]) ? str_replace('Type: ', '', $parts[3]) : '';
									$entity_id = isset($parts[4]) ? str_replace('ID: ', '', $parts[4]) : '';
									$details = isset($parts[5]) ? str_replace('Details: ', '', $parts[5]) : '';
									?>
									<tr>
										<td><?php echo htmlspecialchars($timestamp); ?></td>
										<td><?php echo htmlspecialchars($user); ?></td>
										<td><?php echo htmlspecialchars($action); ?></td>
										<td><?php echo htmlspecialchars($entity_type); ?></td>
										<td><?php echo htmlspecialchars($entity_id); ?></td>
										<td><?php echo htmlspecialchars($details); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="6"><?php echo $log_content; ?></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div> <!-- /panel-body -->
		</div> <!-- /panel -->
	</div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php require_once 'includes/footer.php'; ?>