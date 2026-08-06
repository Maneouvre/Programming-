<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    
    <link rel="stylesheet" href="del_update_form.css">
</head>
<body>

    <h3>Change account</h3>

    <form action="update_formhandler.php" method="post">
        <div>
            <input type="text" name="Username" placeholder="Username">
        </div>

        <div>
            <input type="password" name="password" placeholder="Password">
        </div>

        <div>
            <input type="text" name="email" placeholder="E-Mail">
        </div>

        <div>
            <button>Update</button>
        </div>
    </form>
    

    <h3>Delete account</h3>

    <form action="del_formhandler.php" method="post">
        <div>
            <input type="text" name="Username" placeholder="Username">
        </div>

        <div>
            <input type="password" name="password" placeholder="Password">
        </div>

        <div>
            <button>Delete</button>
        </div>
    </form>

</body>
</html>
