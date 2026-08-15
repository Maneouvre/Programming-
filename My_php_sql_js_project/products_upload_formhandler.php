<?php
if ($_SERVER["REQUEST_METHOD"]==="POST"){
            //gets the username and password from the form
            $Product_name=($_POST["product_name"]);
            $Price_cents=($_POST["price_cents"]);
            $keywords=($_POST["keywords"]);
            $product_image=($_POST["product_image"]);
            $rating_stars=($_POST["rating_stars"]);
            $rating_count=($_POST["rating_count"]);
            
            
try {
            //importing data
           require_once("products_upload_dbase_connection.php");
           require_once("products_model.php");
           require_once("products_controller.php");

           //ERROR HANDLERS
            $errors=[];
                //checks if any input is empty
                
                if(is_price_empty( $Product_name)){
                    //pushes
                    $errors["empty_price"]="Please Enter the cost of product";

                }
                if(is_keywords_empty( $keywords)){
                    //pushes
                    $errors["empty_keywords"]="Please Enter the keyword";

                }
                if(is_image_empty($product_image)){
                    //pushes
                    $errors["empty_image"]="Please upload a product image";
                    }
                if(is_image_invalid($product_image)){
                    //pushes
                    $errors["invalid_image"]="Please upload a valid image (JPEG, PNG, or WEBP only)";
                    }


                $result=get_product_by_name($pdo,$Product_name);
                if(is_product_name_empty( $Product_name)){
                    //pushes
                    $errors["empty_name"]="Please fill in the name fields";

                }else if(does_product_name_match($result,$Product_name)){
                    $errors["duplicate_product"] = "The product name you entered already exists";

                };
                             

                  
                require_once("products_session.php");
                //checks if errors exist
                if($errors){
                    $_SESSION["errors_input"]=$errors;
                    //saves input data to avoid retyping
                    $product_data = [
                                    "product_name" => $Product_name,
                                    "price_cents"  => $Price_cents,
                                    "keywords"     => $keywords,
                                    "product_image"=> $product_image,
                                    "rating_stars" => $rating_stars,
                                    "rating_count" => $rating_count
                                ];
                    $_SESSION["product_data"]=$product_data;
                    
                    header("Location:./login_form.php");
                    die();
                }if (empty($errors)) {
                    // Calls the controller wrapper function with all variables
                    upload_product($pdo, $Product_name, $Price_cents, $keywords, $target_filepath, $rating_stars, $rating_count);

                    header("Location: ./products_upload.php?upload=success");
                    exit();
                    }
                //create new session id
                $newSessionId=session_create_id();
                //create sess id with users id
                $sessionId=$newSessionId."_".$result["id"];
                session_id($sessionId);

                $_SESSION["product_id"]= $result["id"];
                $_SESSION["product_name"]= htmlspecialchars($result["product_name"]);
                $_SESSION['last_regeneration']=time();
                header("Location:./products_upload.php?upload=success");

              
        


           }catch(PDOException  $e){
            // If something goes wrong, stop and show the error
                                die("Query Failed: ".$e->getMessage());}
                                

         ;}
    
   
else
    {header("Location:./products_upload.php");};
