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

if(isset($_SESSION["product_id"])){
        if(!isset($_SESSION['last_regeneration'])){
        session_regenerate_id_loggedin();
        }
        else{
        $interval=60*30;
        if (time()-$_SESSION['last_regeneration']>=$interval){
            session_regenerate_id_loggedin();
        
        }
        }

        }
else{
    if(!isset($_SESSION['last_regeneration'])){
    session_regenerate_id(true);
    $_SESSION['last_regeneration']=time();
    }
    else{
    $interval=60*30;
    if (time()-$_SESSION['last_regeneration']>=$interval){
    session_regenerate_id(true);
    $_SESSION['last_regeneration']=time();
    }
    } }

//logic to determine rate of session_regeneration
function session_regenerate_id_loggedin(){
        session_regenerate_id(true); 
        $ProductId=$_SESSION["product_id"];
        
        $newSessionId=session_create_id();
        //create sess id with users id
        $sessionId=$newSessionId."_".$ProductId;
        session_commit();
        session_id($sessionId);
         session_start();
        $_SESSION['last_regeneration']=time();
}