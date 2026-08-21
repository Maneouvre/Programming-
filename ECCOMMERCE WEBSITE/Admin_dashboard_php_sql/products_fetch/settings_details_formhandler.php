<?php
//CHECKS IF SENT METHOD IS POST
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $admin_name=$_POST["admin_name"]?? "";
            $admin_password=$_POST["admin_password"]?? "";
            $admin_photo=$_FILES["avatar_file"]?? null;

            $file_ext = pathinfo($product_image["name"], PATHINFO_EXTENSION);
            $target_filepath = $upload_dir . uniqid("prod_", true) . "." . $file_ext;

        try {
            //connects to the database
            require_once("settings_details_dbaseConnection.php");
            //inserting data to mysql
            $query="UPDATE admin_details SET admin_name=:admin_name,admin_password=:admin_password,admin_photo=:admin_photo WHERE  id=1";
            $stmt=$pdo->prepare($query);
            $stmt->bindParam(":admin_name", $admin_name);
            $stmt->bindParam(":admin_password", $padmin_password);
            $stmt->bindParam(":admin_photo", $admin_photo);
            $stmt->execute();
            $pdo=null;
            $stmt=null;
            header("Location:settings_details_formhandler.php");
            die();

            }
        catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());
                                };

            if (empty($admin_name || $admin_password || $admin_photo)){
                header("Location:settings_details_formhandler.php");

            };
    }
   
else
    {header("settings_details_formhandler.php");}



?>
