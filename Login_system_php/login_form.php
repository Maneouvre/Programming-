<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login_form.css">
</head>
<body>
    <div class="login-container css-login-card">
        <h2 class="css-heading">Sign In</h2>
        
        <form class="login-form css-form-layout" action="signup_login_formhandler.php" method="POST">
            <div class="input-group css-input-wrapper">
                <input class="css-input text-field" type="text" id="username" name="username" placeholder="username" required autocomplete="username">
            </div>
            
            <div class="input-group css-input-wrapper">
                <input class="css-input password-field" type="password" id="password" name="password" placeholder="password" required autocomplete="current-password">
            </div>
            
            <div class="form-actions css-action-row">
                <a href="signup_form.php" class="signup css-signup-link">Sign Up</a>
                <a href="#" class="forgot-password css-forgot-link">Forgot Password?</a>
            </div>
            <button type="submit" class="submit-btn css-login-button">Sign In</button>
        </form>
    </div>
</body>
</html>
