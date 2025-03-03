<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset(); // Remove all session variables
session_destroy(); // Destroy the session

// Corrected header redirection
header("Location: /EventManagementSystem/index.php");
exit;
?>
