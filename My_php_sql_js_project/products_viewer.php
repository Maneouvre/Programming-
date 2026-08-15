<?php
declare(strict_types=1);
function check_upload_errors(){
    if(isset($_SESSION["errors_input"])){
        $errors=$_SESSION["errors_input"];
        foreach($errors as $error){
            echo "<p class='error'>".$error."</p>";
        };
        // Clear only the login errors array without kicking the user out
        unset($_SESSION["errors_login"]); 

    }
    else if(isset($_GET['upload']) && $_GET['upload'] === 'success'){
         echo "<p class='form-success'>Product Uploaded Successfully!.</p>";

}}