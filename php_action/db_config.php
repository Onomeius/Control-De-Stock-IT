<?php
$servername = "localhost";
$username = "root"; // Tu usuario de MySQL
$password = "";     // Tu contraseña de MySQL
$dbname = "pos_stock"; // El nombre de la base de datos principal

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>