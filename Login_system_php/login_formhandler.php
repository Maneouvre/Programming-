<?php
//CHECKS IF SENT METHOD IS POST THEN RUNS
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Username=($_POST["username"]);
            $password=($_POST["password"]);

        try {
            //importing data
           require_once("login_signupdbase_handler.php");
           require_once("login_model.php");
           require_once("login_controller.php");

           //ERROR HANDLERS
            $errors=[];
                //checks if any input is empty
                if(is_input_empty($Username,$password)){
                    //pushes
                    $errors["empty_input"]="Please fill in all fields";

                }
                $result=get_user($pdo,$Username);
                if(is_username_wrong($result)){
                   $errors["login_incorect"]="Incorrect Login Info";

                }
                else if(is_password_wrong($password,$result["pwd"])){
                   $errors["login_incorect"]="Incorrect Login Info";

                };
                

                  
                require_once("login_session_id.php");
                //checks if errors exist
                if($errors){
                    $_SESSION["errors_login"]=$errors;
                    //saves input data to avoid retyping
                    $userData=[
                        "username"=>$Username,
                        "email"=>$email
                    ];
                    
                    header("Location:./login_form.php");
                    die();
                }
                //create new session id
                $newSessionId=session_create_id();
                //create sess id with users id
                $sessionId=$newSessionId."_".$result["id"];
                session_id($sessionId);

                $_SESSION["user_id"]= $result["id"];
                $_SESSION["user_username"]= htmlspecialchars($result["username"]);
                $_SESSION['last_regeneration']=time();
                header("Location:./login_form.php?login=success");

              
        


           }catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());}
                                

            if (empty($Username) || empty($password)){
                header("Location:./login_form.php");
                exit();

            };}
    
   
else
    {header("Location:./login_form.php");};