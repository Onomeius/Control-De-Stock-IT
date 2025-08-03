<?php
require_once 'db_connect.php';

// --- Archivo de Log ---
$log_file = __DIR__ . '/download_log.txt';
file_put_contents($log_file, "--- INICIO DEL REGISTRO DE DESCARGA ---\n");

function write_log($message) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ": " . $message . "\n", FILE_APPEND);
}

// Iniciar el buffer de salida para atrapar cualquier salida prematura
ob_start();
write_log("Buffer de salida iniciado.");

function send_json_error($message) {
    write_log("ERROR: " . $message);
    ob_end_clean(); // Limpiar buffer antes de enviar JSON
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['message' => $message]);
    exit;
}

write_log("Script iniciado.");

if (!isset($_POST['ids'])) {
    send_json_error('No se recibieron los IDs de las facturas.');
}

$ids_json = $_POST['ids'];
write_log("JSON de IDs recibido: " . $ids_json);

$ids = json_decode($ids_json, true);

if (!is_array($ids) || empty($ids)) {
    send_json_error('Los IDs proporcionados no son válidos.');
}

$sanitized_ids = array_map('intval', $ids);
write_log("IDs sanitizados: " . implode(', ', $sanitized_ids));

$placeholders = implode(',', array_fill(0, count($sanitized_ids), '?'));
$types = str_repeat('i', count($sanitized_ids));

$sql = "SELECT ruta_archivo, nombre_archivo FROM facturas WHERE id IN ($placeholders)";
write_log("SQL preparado: " . $sql);

$stmt = $connect->prepare($sql);
if (!$stmt) {
    send_json_error('Error al preparar la consulta a la base de datos: ' . $connect->error);
}

$stmt->bind_param($types, ...$sanitized_ids);
$stmt->execute();
$result = $stmt->get_result();

write_log("Filas encontradas en la BD: " . $result->num_rows);

if ($result->num_rows === 0) {
    $stmt->close();
    $connect->close();
    send_json_error('No se encontraron facturas para los IDs proporcionados.');
}

$zip = new ZipArchive();
$zip_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'facturas_' . time() . '.zip';
write_log("Creando archivo ZIP en: " . $zip_name);

if ($zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    send_json_error('No se pudo crear el archivo ZIP.');
}

$files_added = 0;
while ($row = $result->fetch_assoc()) {
    $ruta_db = $row['ruta_archivo'];
    write_log("Procesando archivo de DB: " . $ruta_db);

    $file_path = realpath(__DIR__ . '/../' . ltrim($ruta_db, '../'));

    write_log("Ruta calculada: " . ($file_path ? $file_path : '[RUTA INVÁLIDA]'));

    if ($file_path && file_exists($file_path)) {
        write_log("¡ÉXITO! Archivo encontrado. Añadiendo al ZIP: " . $row['nombre_archivo']);
        $zip->addFile($file_path, $row['nombre_archivo']);
        $files_added++;
    } else {
        write_log("¡FALLO! El archivo no existe en la ruta calculada.");
    }
}
$stmt->close();

write_log("Intentando cerrar y finalizar el archivo ZIP. Total de archivos a añadir: " . $files_added);

if ($zip->close()) {
    write_log("¡ÉXITO! El archivo ZIP se cerró y finalizó correctamente.");
} else {
    write_log("¡FALLO CRÍTICO! No se pudo cerrar y finalizar el archivo ZIP.");
    unlink($zip_name);
    send_json_error('Error al finalizar la creación del archivo ZIP.');
}

if ($files_added > 0) {
    write_log("Enviando el archivo ZIP al usuario. Tamaño: " . filesize($zip_name) . " bytes.");
    
    ob_end_clean(); // Limpiar buffer antes de enviar las cabeceras

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_name) . '"');
    header('Content-Length: ' . filesize($zip_name));
    header('Connection: close');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($zip_name);
    
    write_log("Limpiando el archivo temporal del servidor.");
    unlink($zip_name);
    $connect->close();
    exit;
} else {
    write_log("No se añadieron archivos al ZIP. Eliminando archivo vacío.");
    unlink($zip_name);
    $connect->close();
    send_json_error('No se encontraron los archivos de factura en el servidor. Revise download_log.txt para más detalles.');
}
?>