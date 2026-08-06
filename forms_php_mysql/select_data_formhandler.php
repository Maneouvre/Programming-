
<?php


//CHECKS IF SENT METHOD IS POST
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Usersearch=($_POST["usersearch"]);
           
        try {
            //connects to the database
            require_once("dbasehandler-inc.php");
            //inserting data to mysql
            $query="SELECT * FROM users WHERE username=:usersearch;";
            $stmt=$pdo->prepare($query);
            $stmt->bindParam(":usersearch", $Usersearch);
            
            
            $stmt->execute();
            $results=$stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo=null;
            $stmt=null;
           

            }
        catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());
                                };

            if (empty($Usersearch)){
                header("Location:select_data_form.php");

            };
    }
   
else
    {header("Location:select_data_form.php");}



?>






<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            body{background-color:#f2f2f2;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:center;
                }
        </style>
        
        <title>Search Users</title>
    </head>

    <body>
        <section></section>
        <h1>Search Results</h1>
        <?php
        if (empty($results)) {
            echo "<div>";
            echo "<p>No results found.</p>";
            echo "</div>";
        }
        else{
            foreach($results as $result){
                
                echo "<div>";
                // use htmlspecialchars to prevent XSS attacks

                echo "<h4>"."Username: ".htmlspecialchars($result["username"])."</h4>";
                echo "<p>Email: ".htmlspecialchars($result["email"])."</p>";
                echo "<p>Password: ".htmlspecialchars($result["pwd"])."</p>";
                echo "</div>";
            }
            
        }
        ?>

        
    </body>

</html>
