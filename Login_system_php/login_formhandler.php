<?php
//CHECKS IF SENT METHOD IS POST THEN RUNS
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Username=($_POST["username"]);
            $password=($_POST["password"]);

        try {
           


           }catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());}
                                

            if (empty($Username) || empty($password)){
                header("Location:./login_form.php");
                exit();

            };}
    
   
else
    {header("Location:./signup_form.php");};