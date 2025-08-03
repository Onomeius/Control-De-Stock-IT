<?php
$testFile = 'invoices/test_write.txt';
$message = '';

if (is_writable('invoices/')) {
    $message .= "DEBUG: El directorio 'invoices/' es escribible.<br>";
    if (file_put_contents($testFile, 'Esto es una prueba de escritura.') !== false) {
        $message .= "DEBUG: Se pudo escribir en 'invoices/test_write.txt'.<br>";
        unlink($testFile); // Eliminar el archivo de prueba
        $message .= "DEBUG: Archivo de prueba eliminado.<br>";
    } else {
        $message .= "ERROR: No se pudo escribir en 'invoices/test_write.txt'. Verifica los permisos de escritura.<br>";
    }
} else {
    $message .= "ERROR: El directorio 'invoices/' NO es escribible. Verifica los permisos.<br>";
}

echo $message;
?>