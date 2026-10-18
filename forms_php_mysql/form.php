<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
        <link rel="stylesheet" href="form.css">

  </head>

<body> 

  <div class="form"> 
    <form action="formhandler.php" method="post"> 
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

      <div class="bottom"> 
         
        <a class="css-forgot-password" href="https://www.brightermonday.co.ke/jobs">Forgot Password?</a> 
      </div> 
      
      <div> 
        <button class="login css-signup-button" type="submit ">Sign Up</button> 
      </div> 
    </form> 
  </div> 
</body>
</html>
