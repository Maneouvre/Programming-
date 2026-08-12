<?php
/*//to strictly enforce data types
declare(strict_types=1);
//connects to mysql through pdo in login_signupdbase_handler.php
// Implementation for checking if username is already taken
function get_username(object $pdo,string $Username){
    $query="SELECT username FROM users WHERE username=:Username;";
    $stmt=$pdo->prepare($query);
    $stmt->execute(['username'=>$Username]);
    $stmt->execute();
    $result=$stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}
function get_email(object $pdo,string $email){
    $query="SELECT email FROM users WHERE email=:email;";
    $stmt=$pdo->prepare($query);
    $stmt->execute(['email'=>$email]);
    $stmt->execute();
    $result=$stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
};*/

// to strictly enforce data types
declare(strict_types=1);

// connects to mysql through pdo in login_signupdbase_handler.php
// Implementation for checking if username is already taken
function get_username(object $pdo, string $Username){
    $query = "SELECT username FROM users WHERE username = :Username;";
    $stmt = $pdo->prepare($query);
    
    // FIXED: Added colon to key and removed the second duplicate execute() line
    $stmt->execute([':Username' => $Username]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function get_email(object $pdo, string $email){
    $query = "SELECT email FROM users WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    
    // FIXED: Added colon to key and removed the second duplicate execute() line
    $stmt->execute([':email' => $email]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}
