<?php
declare(strict_types=1);

function get_user(object $pdo,string $Username){
    $query = "SELECT * FROM users WHERE username = :Username;";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':Username' => $Username]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;

}