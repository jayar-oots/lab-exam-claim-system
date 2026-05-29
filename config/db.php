<?php
// db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "lab_claim_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
