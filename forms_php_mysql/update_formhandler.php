<?php
var_dump($_SERVER["REQUEST_METHOD"]);
//CHECKS IF SENT METHOD IS POST
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Username=($_POST["Username"]);
            $password=($_POST["password"]);
            $email=($_POST["email"]);

        try {
            //connects to the database
            require_once("dbasehandler-inc.php");
            //inserting data to mysql
            $query="UPDATE users SET username=:Username,pwd=:password,email=:email WHERE  id=2";
            $stmt=$pdo->prepare($query);
            $stmt->bindParam(":Username", $Username);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":email", $email);
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
