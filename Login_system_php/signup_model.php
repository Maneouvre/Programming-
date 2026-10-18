<?php
// to strictly enforce data types
declare(strict_types=1);

// connects to mysql through pdo in login_signupdbase_handler.php
// Implementation for checking if username is already taken
function get_username(object $pdo, string $Username){
    $query = "SELECT username FROM users WHERE username = :Username;";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':Username' => $Username]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}
//gets email from the dbase
function get_email(object $pdo, string $email){
    $query = "SELECT email FROM users WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}
//inserts user into the dbase
function set_user(object $pdo,string $Username,string $password,string $email){
    $query = "INSERT INTO users(username,pwd,email) VALUES ( :username,:pwd,:email);";
    $stmt = $pdo->prepare($query);

   //hashing password
   $options=[
    'cost'=>12
   ];
   $hashedpwd=password_hash($password,PASSWORD_BCRYPT,$options);

    $stmt->bindparam(":username",$Username);
    $stmt->bindparam(":pwd",$hashedpwd);
    $stmt->bindparam(":email",$email);
    $stmt->execute();
}
function signupData(){
    if(isset($_SESSION["signup_data"]["username"]) && 
        !isset($_SESSION["errors_signup"]["username_taken"])){
          echo'<div> 
              <input class="username css-username" name="username" type="text" placeholder="username" 
              value="'.$_SESSION["signup_data"]["username"].'" required> 
            </div> 
            <br> ';
            unset($_SESSION["signup_data"]["username"]);
        } else{
          echo '<div> 
              <input class="username css-username" name="username" type="text" placeholder="username" required> 
            </div> 
            <br> ';
        }
        echo ' <div> 
              <input class="password css-password" name="password" type="password" placeholder="password" required> 
            </div> 
            <br> ';
        if(isset($_SESSION["signup_data"]["email"]) &&
        !isset($_SESSION["errors_signup"]["invalid_email"]) &&
        !isset($_SESSION["errors_signup"]["email_registered"])){
          echo'<div>
              <input class="css-email" name="email" type="email" placeholder="email"
              value="'.$_SESSION["signup_data"]["email"].'" required>
            </div>
            ';
           unset($_SESSION["signup_data"]["email"]);
        } else{
          echo '<div>
              <input class="css-email" name="email" type="email" placeholder="email" required>
            </div> ';
        }
  }