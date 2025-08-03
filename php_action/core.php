<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';

// Ensure userRole is always set in session
if (isset($_SESSION['userId']) && !isset($_SESSION['userRole'])) {
    $userId = $_SESSION['userId'];
    $sql = "SELECT role FROM users WHERE user_id = $userId";
    $result = $connect->query($sql);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['userRole'] = $row['role'];
    }
}

if (!isset($_SESSION['userId'])) {
    header('location: index.php');
    exit;
}
?>
