
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
<<<<<<< HEAD
             body {
                background-color: #f2f2f2;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-family: Arial, sans-serif;
            }
            table { 
                border-collapse: collapse; 
                width: 80%; 
                margin-top: 20px;
                background-color: #ffffff;
            }
            th, td { 
                border: 1px solid #dddddd; 
                text-align: left; 
                padding: 12px; 
            }
            th { 
                background-color: #e2e2e2; 
            }
=======
            body{background-color:#f2f2f2;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:center;
                }
>>>>>>> eab79c1ae39398dd6964fa2582ad98fc6a87b49a
        </style>
        
        <title>Search Users</title>
    </head>

    <body>
<<<<<<< HEAD
        
        
         <h1>Search Results</h1>
        
        <?php
        if (empty($results)) {
            echo "<div><p>No results found.</p></div>";
        } else {
            // Start the table ONCE outside the loop
            echo "<table>";
            echo "<tr><th>Username</th><th>Email</th><th>Password</th></tr>";

            // Loop through each user row
            foreach($results as $result) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($result["username"]) . "</td>";
                echo "<td>" . htmlspecialchars($result["email"]) . "</td>";
                echo "<td>" . htmlspecialchars($result["pwd"]) . "</td>";
                echo "</tr>";
            }

            // Close the table ONCE outside the loop
            echo "</table>";
        }
        ?>
            
        
        
=======
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
>>>>>>> eab79c1ae39398dd6964fa2582ad98fc6a87b49a

        
    </body>

</html>
