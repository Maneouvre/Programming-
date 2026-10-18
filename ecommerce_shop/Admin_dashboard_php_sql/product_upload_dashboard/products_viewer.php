<?php
declare(strict_types=1);

function check_upload_errors(){
    if (isset($_SESSION["errors_input"])){
        $errors = $_SESSION["errors_input"];
        
        echo '<div class="error-container">';
        foreach($errors as $error){
            // FIX: Added htmlspecialchars to prevent XSS breaks and applied custom class styles
            echo "<div class='error-message-line'>";
            echo "<span class='error-icon'>⚠️</span>";
            echo "<p class='error-text'>".htmlspecialchars($error)."</p>";
            echo "</div>";
        }
        echo '</div>';
        
        // FIX: Changed from errors_login to clear the correct input errors array index
        unset($_SESSION["errors_input"]); 
    }
    else if (isset($_GET['upload']) && $_GET['upload'] === 'success'){
         echo '<div class="success-container">';
         echo "<p class='form-success'>🎉 Product Uploaded Successfully!</p>";
         echo '</div>';
    }
}
