<?php
//error reporting
require_once("login_signup_sessionhandler.php");
require_once("signup_view.php");
require_once("signup_model.php");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
<<<<<<< HEAD
      .form-success{color:green;}
      .error{color:red;}
    </style>
=======
        .form-success{color:green;}
        .error{color:red;}
        .css-login-link {
                    display: inline-block;
                    width: 100%; 
                    text-align: center;
                    color: blue;
                    } 
    </style>
    
>>>>>>> 576101d2466c9927d7de9ada1803733e8be45433
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
        <link rel="stylesheet" href="signup_form.css">

  </head>

<body> 

  <div class="form"> 
    <form action="signup_login_formhandler.php" method="post"> 
      <h1  class="css-heading">Sign Up</h1> 
              <!-- Username Box -->
             <!-- Password Box -->
             <!-- Email Box -->
              <?php
              signupData();
              ?>
            
            <br>
            <br>
  <?php
  check_signup_errors();
  ?>

<<<<<<< HEAD
      <a href="login_form.php" class="css-login-link">Already have an account? Log in</a>
=======
      <a  href="login_form.php" class="css-login-link">Already have an account? Log in</a>
>>>>>>> 576101d2466c9927d7de9ada1803733e8be45433
      <br>
      <br>
      
      <div> 
        <button class="login css-signup-button" type="submit ">Sign Up</button> 
      </div> 
    </form> 
   
  </div> 
  
</body>
</html>
