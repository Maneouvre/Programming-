<?php
session_start();

$_SESSION['username']='Manoeuvre';
require_once 'session_storage_rel.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>session storage</title>
    </head>
    <body>
        <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
    </body>
</html>