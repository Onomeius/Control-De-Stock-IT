<?php
require_once 'db_connect.php';
session_start();

if (isset($_GET['id'])) {
    $invoiceId = $_GET['id'];

    // Optional: Add role check here if you want to restrict even single downloads
    // For now, allowing visualizers to download single files as per request

    $stmt = $connect->prepare("SELECT nombre_archivo, ruta_archivo FROM facturas WHERE id = ?");
    $stmt->bind_param("i", $invoiceId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filePath = $row['ruta_archivo'];
        $fileName = $row['nombre_archivo'];

        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '";');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            echo "Archivo no encontrado.";
        }
    } else {
        echo "Factura no encontrada.";
    }
    $stmt->close();
} else {
    echo "ID de factura no proporcionado.";
}

$connect->close();
?>