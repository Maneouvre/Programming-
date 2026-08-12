<?php
//error reporting
require_once("login_signup_sessionhandler.php");
require_once("signup_view.php");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
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
            <div> 
              <input class="username css-username" name="username" type="text" placeholder="username" required> 
            </div> 
              <br> 

              <!-- Password Box -->
            <div> 
              <input class="password css-password" name="password" type="password" placeholder="password" required> 
            </div> 
              <br> <!-- Added a break here for consistent spacing -->

              <!-- Email Box -->
            <div>
              <input class="css-email" name="email" type="email" placeholder="email" required>
            </div>
            <br>
            <br>

      
      
      <div> 
        <button class="login css-signup-button" type="submit ">Sign Up</button> 
      </div> 
    </form> 
  </div> 
  <?php
  check_signup_errors();
  ?>
</body>
</html>
