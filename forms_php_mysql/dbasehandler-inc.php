<?php
//connects to my sql
$dsn="mysql:host=localhost;dbname=first_database";
$dbusername="root";
$dbpassword="";

try {
    $pdo = new PDO($dsn, $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}