<?php
//error reporting
declare(strict_types=1);
function is_input_empty(string $Username, string $password, string $email){
    if (empty($Username) || empty($password) || empty($email)){
        return true;
    }
    else{
        return false;
    }
};
function is_email_invalid(string $email){
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)){
        return true;
    }
    else{
        return false;
        }
};
 // Implementation for checking if username is already taken
function is_username_taken(object $pdo, string $Username){
    if (get_username($pdo,$Username)){
        return true;
    }
    else{
        return false;
    }
   
}
function is_email_registered(object $pdo,string $email){
    if (get_email($pdo,$email)){
        return true;
    }
    else{
        return false;
    }
}