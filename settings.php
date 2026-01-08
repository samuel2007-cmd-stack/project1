<?php
$host = "localhost";
$user = "root";
$pwd  = "";
$db   = "ctrlaltelite";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pwd, $db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    exit("Database service is currently unavailable. Please try again later.");
}
?>