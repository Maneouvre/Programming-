<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            .calc-error{color:red;}
        </style>
        <meta name="viewport" content="width=device-width">
        <title>Calculator</title>
    </head>



<body>
<p>To Do Simple Math</p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post">
    <input type="text" name="number1" placeholder=" Enter Number1">
        <select name="operator" >
            <option value="multiply">*</option>
            <option value="subtract">-</option>
            <option value="add">+</option>
            <option value="divide">/</option>
        </select>
    <input type="text" name="number2" placeholder=" Enter Number2">
    <button>Calculate</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"]="POST"){
    //grab data
    $num1=filter_input(INPUT_POST,"number1",FILTER_SANITIZE_NUMBER_FLOAT);
    $num2=filter_input(INPUT_POST,"number2",FILTER_SANITIZE_NUMBER_FLOAT);  
    $operator=htmlspecialchars($_POST["operator"]);
    //error handlers
    $errors=false;
    if (empty($num1) || empty($num2) || empty($operator)){ echo "<p class='calc-error'>Please fill all fields</p>";
    $errors=true;};
    if (!is_numeric($num1) && !is_numeric($num2) ){echo "<p class='calc-error'>Please fill all fields with numbers only</p>"
    ;$errors=true;};
    //Calculate if no errors
    if (!$errors){
        $value=0;
        switch($operator){
        case "add":
            $value = $num1 + $num2;
            break;
        case "subtract":
            $value = $num1 - $num2;
            break;
        case "multiply":
            $value = $num1 * $num2;
            break;
        case "divide":
            $value = $num1 / $num2;
            break;
        default;echo"Something went wrong";
        };
        echo '<p class="result" >Result='.$value.'  </p>';
    }



}

?>
<p>To Calculate area of circle</p>
<form  action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post" >
            
            <input type="text" name="radius" placeholder="Enter Radius of circle">
            <button>Calculate</button>

        </form>
        <?php
        //area of circle calculator
        $radius=filter_input(INPUT_POST,"radius",FILTER_SANITIZE_NUMBER_FLOAT);
        define ("PI",3.142);
        $answer=$radius*PI;
        echo "<p>$answer</p>"
/*
<?php
// Start or resume the session to remember the calculator screen value
session_start();

// Initialize the screen if it is empty
if (!isset($_SESSION['screen'])) {
    $_SESSION['screen'] = "";
}

// Check if a button was clicked
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pressed = $_POST['button'];

    if ($pressed == "C") {
        // Clear the screen
        $_SESSION['screen'] = "";
    } elseif ($pressed == "=") {
        // Calculate the result using a basic math helper function
        $_SESSION['screen'] = calculate_simple_string($_SESSION['screen']);
    } else {
        // Append the pressed number or operator to the screen
        $_SESSION['screen'] .= $pressed;
    }
}

// Simple beginner-friendly evaluator for basic math strings (e.g., "12+4")
function calculate_simple_string($equation) {
    // Find which operator is in the string
    if (strpos($equation, '+') !== false) {
        $parts = explode('+', $equation);
        return $parts[0] + $parts[1];
    } elseif (strpos($equation, '-') !== false) {
        $parts = explode('-', $equation);
        return $parts[0] - $parts[1];
    } elseif (strpos($equation, '*') !== false) {
        $parts = explode('*', $equation);
        return $parts[0] * $parts[1];
    } elseif (strpos($equation, '/') !== false) {
        $parts = explode('/', $equation);
        if ($parts[1] == 0) {
            return "Error (Div by 0)";
        }
        return $parts[0] / $parts[1];
    }
    return $equation; // Return as-is if no operator found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Button Calculator</title>
    <style>
        .calculator {
            width: 260px;
            margin: 50px auto;
            padding: 20px;
            border: 2px solid #333;
            border-radius: 10px;
            background-color: #f4f4f4;
            text-align: center;
        }
        .screen {
            width: 90%;
            height: 40px;
            margin-bottom: 15px;
            font-size: 20px;
            text-align: right;
            padding-right: 5px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .btn {
            width: 55px;
            height: 55px;
            font-size: 18px;
            cursor: pointer;
        }
        .btn-clear { background-color: #ff8080; }
        .btn-equal { background-color: #80ff80; }
    </style>
</head>
<body>

<div class="calculator">
    <!-- The Calculator Screen -->
    <input type="text" class="screen" value="<?php echo htmlspecialchars($_SESSION['screen']); ?>" readonly>

    <!-- The Calculator Buttons -->
    <form action="" method="post">
        <div class="row">
            <button type="submit" name="button" value="7" class="btn">7</button>
            <button type="submit" name="button" value="8" class="btn">8</button>
            <button type="submit" name="button" value="9" class="btn">9</button>
            <button type="submit" name="button" value="/" class="btn">/</button>
        </div>
        <div class="row">
            <button type="submit" name="button" value="4" class="btn">4</button>
            <button type="submit" name="button" value="5" class="btn">5</button>
            <button type="submit" name="button" value="6" class="btn">6</button>
            <button type="submit" name="button" value="*" class="btn">*</button>
        </div>
        <div class="row">
            <button type="submit" name="button" value="1" class="btn">1</button>
            <button type="submit" name="button" value="2" class="btn">2</button>
            <button type="submit" name="button" value="3" class="btn">3</button>
            <button type="submit" name="button" value="-" class="btn">-</button>
        </div>
        <div class="row">
            <button type="submit" name="button" value="C" class="btn btn-clear">C</button>
            <button type="submit" name="button" value="0" class="btn">0</button>
            <button type="submit" name="button" value="=" class="btn btn-equal">=</button>
            <button type="submit" name="button" value="+" class="btn">+</button>
        </div>
    </form>
</div>

</body>
</html>
*/
        ?>

</body>
<html>