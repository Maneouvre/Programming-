<?php
var_dump($_SERVER["REQUEST_METHOD"]);
//CHECKS IF SENT METHOD IS POST
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Username=($_POST["username"]);
            $password=($_POST["password"]);
            $email=($_POST["email"]);

        try {
            //connects to the database
            require_once("dbasehandler-inc.php");
            //inserting data to mysql
            $query="INSERT INTO users(username,pwd,email) 
            VALUES (?,?,?);";
            $stmt=$pdo->prepare($query);
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 576101d2466c9927d7de9ada1803733e8be45433
            //hashing the password before storing it in the database
                $options=[
                'cost'=>12
                ];
                $hashedpwd=password_hash($password,PASSWORD_DEFAULT,$options);
            $stmt->execute([$Username,$hashedpwd,$email]);
<<<<<<< HEAD
=======
=======
            $stmt->execute([$Username,$password,$email]);
>>>>>>> eab79c1ae39398dd6964fa2582ad98fc6a87b49a
>>>>>>> 576101d2466c9927d7de9ada1803733e8be45433
            $pdo=null;
            $stmt=null;
            header("Location:./form.php");
            die();

            }
        catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());
                                };

            if (empty($Username || $password)){
                header("Location:./form.php");

            };
    }
   
else
    {header("Location:./form.php");}


/*
echo "<p>Hello world</p>";
echo '<br>';
echo '<p style="color: blue;">Am good</p>';



echo '<br>';
echo $_SERVER['DOCUMENT_ROOT'];
echo '<br>';
echo $_SERVER['PHP_SELF'];
echo '<br>';
echo $_SERVER['REQUEST_METHOD'];
echo '<br>';
$_REQUEST["name"]="Manu";
echo $_REQUEST["name"];

$a=5;
$b="daniel";
switch($a){
    case 3:echo "hi";
    break;
    default: 
    echo "Not equal";
};
echo "<br>";
$result=match($b){
    "daniel" => "Variable b is $b",

};
echo $result;

<?php
$myArray=["Apple",
        "Banana",
        "Orange"];

//$myArray[]="ichiungwa";
echo $myArray[0];
echo "<br>";
//keys
$chores=[
        "laundry"=>"Daniel",
        "cleaning"=>"rose",
        "Cooking"=>"Job",
];
//echo $chores["laundry"];

echo count($chores);
sort($chores);
print_r($chores);
*/
?>