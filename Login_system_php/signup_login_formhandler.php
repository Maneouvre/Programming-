<?php
//CHECKS IF SENT METHOD IS POST THEN RUNS
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Username=($_POST["username"]);
            $password=($_POST["password"]);
            $email=($_POST["email"]);

        try {
            //connects to the database
            require_once("login_signupdbase_handler.php");
            require_once("signup_model.php");
            require_once("signup_controller.php");
            //ERROR HANDLERS
            $errors=[];
                //checks if any input is empty
                if(is_input_empty($Username,$password,$email)){
                    //pushes
                    $errors["empty_input"]="Please fill in all fields";

                }
                //checks if email is valid
                if(is_email_invalid($email)){
                    $errors["invalid_email"]="Please enter a valid email address";
                }
                
                //checks if username is already taken
                if (is_username_taken($pdo,$Username)){
                    $errors["username_taken"]="Username is already taken";
                }
                //checks if email is already registered
                if (is_email_registered($pdo,$email)){
                
                    $errors["email_registered"]="Email is already registered";

                }
                require_once("login_signup_sessionhandler.php");
                //checks if errors exist
                if($errors){
                    $_SESSION["errors_signup"]=$errors;
                    //saves input data to avoid retyping
                    $userData=[
                        "username"=>$Username,
                        "email"=>$email
                    ];
                    $_SESSION["signup_data"]=$userData;
                    header("Location:./signup_form.php");
                    die();
                }
                create_users($pdo,$Username,$password,$email);
                header("Location:./signup_form.php?signup=success");
                $pdo=null;
                $stmt=null;



           }catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());}
                                

            if (empty($Username) || empty($password)){
                header("Location:./login_form.php");
                exit();

            };}
    
   
else
    {header("Location:./signup_form.php");};