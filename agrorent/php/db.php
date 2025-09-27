<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- IMPORTANT: UPDATE WITH YOUR DATABASE CREDENTIALS IF NEEDED ---
// These are the default for XAMPP
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agrorent";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { 
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error])); 
}
?>