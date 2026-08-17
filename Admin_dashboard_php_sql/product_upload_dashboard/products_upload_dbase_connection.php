<?php
//connects to my sqldbase
$dsn="mysql:host=localhost;dbname=ecommerce_db";
$dbusername="root";
$dbpassword="";

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    echo "Connection failed. Please try again later.";
}
