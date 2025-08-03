<?php
require_once 'core.php';
require_once 'db_connect.php';
require_once 'log_helper.php';

$valid['success'] = array('success' => false, 'messages' => array());

if ($_POST) {
    $invoiceId = $_POST['invoiceId'];

    $sql = "SELECT file_path FROM invoices WHERE invoice_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $invoiceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $filePath = $result->fetch_assoc()['file_path'];
    $stmt->close();

    $deleteSql = "DELETE FROM invoices WHERE invoice_id = ?";
    $deleteStmt = $connect->prepare($deleteSql);
    $deleteStmt->bind_param("i", $invoiceId);

    if ($deleteStmt->execute()) {
        // Attempt to delete the file from the server
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $valid['success'] = true;
        $valid['messages'] = "Factura eliminada exitosamente.";
        $loggedInUser = isset($_SESSION['userId']) ? $_SESSION['userId'] : 'Desconocido';
        log_activity($loggedInUser, 'delete', 'invoice', $invoiceId, "Factura eliminada: $filePath");
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error al eliminar la factura.";
    }
    $deleteStmt->close();
    $connect->close();
}

echo json_encode($valid);
?>