<?php

//CHECKS IF SENT METHOD IS POST
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Usersearch=($_POST["usersearch"]);
            $password=($_POST["password"]);
            $email=($_POST["email"]);

        try {
            //connects to the database
            require_once("dbasehandler-inc.php");
            //inserting data to mysql
            $query="DELETE FROM users WHERE username=:Username AND pwd=:password;";
            $stmt=$pdo->prepare($query);
            $stmt->bindParam(":Username", $Username);
            $stmt->bindParam(":password", $password);
            
            $stmt->execute();
            $pdo=null;
            $stmt=null;
            header("Location:del_update_form.php");
            die();

            }
        catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());
                                };

            if (empty($Username || $password)){
                header("Location:del_update_form.php");

            };
    }
   
else
    {header("Location:del_update_form.php");}



?>
