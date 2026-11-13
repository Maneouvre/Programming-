<?php
ini_set('session_use_only_cookies',1);
//web_browser only uses id provided by our server and complex
ini_set('session_use_strict_mode',1);
//make cookie secure
session_set_cookie_params([
    'lifetime'=>1800,
    'domain'=>'localhost',
    'path'=>'/',
    'secure'=>true,
    'httponly'=>true
    
]);
session_start();
//logic to determine rate of session_regeneration
if(!isset($_SESSION['last _regeneration'])){
    session_regenerate_id(true);
    $_SESSION['last_regenaration']=time();
}
else{
    $interval=60*30;
    if (time()-$_SESSION['last_regeneration']>=$interval){
        session_regenerate_id(true);
        $_SESSION['last_regeneration']=time();
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>session storage</title>
    </head>
    <body>
        
    </body>
</html>