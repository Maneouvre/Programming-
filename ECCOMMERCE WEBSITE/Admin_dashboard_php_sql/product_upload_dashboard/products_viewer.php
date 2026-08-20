<?php 
declare(strict_types=1); 

function check_upload_errors(){ 
    if (isset($_SESSION["errors_input"])){ 
        $errors = $_SESSION["errors_input"]; 
        echo '<div class="error-container">'; 
        foreach($errors as $error){ 
            echo "<div class='error-message-line'>"; 
            echo "<span class='error-icon'>⚠️</span>"; 
            echo "<p class='error-text'>".htmlspecialchars($error)."</p>"; 
            echo "</div>"; 
        } 
        echo '</div>'; 
        unset($_SESSION["errors_input"]); 
    } 
    else if (isset($_GET['upload']) && $_GET['upload'] === 'success'){ 
        echo '<div id="successMessage" class="success-container">'; 
        echo "<p class='form-success'>🎉 Product Uploaded Successfully!</p>"; 
        echo '</div>'; 

        echo '
        <script>
            // 1. Instantly clean the URL parameter so refresh won\'t trigger success again
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete("upload");
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }

            // 2. Hide the message block after 10 seconds
            setTimeout(function() {
                var msg = document.getElementById("successMessage");
                if (msg) {
                    msg.style.transition = "opacity 0.5s ease";
                    msg.style.opacity = "0";
                    setTimeout(function() { msg.style.display = "none"; }, 500);
                }
            }, 10000);
        </script>
        ';
    } 
}

