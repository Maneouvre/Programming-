<?php
declare(strict_types=1);

function check_login_errors(){
    if(isset($_SESSION["errors_login"])){
        $errors=$_SESSION["errors_login"];
        foreach($errors as $error){
            echo "<p class='error'>".$error."</p>";
        };
        // Clear only the login errors array without kicking the user out
        unset($_SESSION["errors_login"]); 

    }
    else if(isset($_GET['login']) && $_GET['login'] === 'success'){
         echo "<p class='form-success'>Welcome back.</p>";

}}