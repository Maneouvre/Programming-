<?php
//error reporting
declare(strict_types=1);
function check_signup_errors(){
    if(isset($_SESSION["errors_signup"])){
        $errors=$_SESSION["errors_signup"];
        foreach($errors as $error){
            echo "<p class='error'>".$error."</p>";
        }
        unset($_SESSION["errors_signup"]);
    } 
    else if(isset($_GET['signup']) && $_GET['signup'] === 'success'){
         echo "<p class='form-success'>Signup was successful! You can now log in.</p>";
    }
}