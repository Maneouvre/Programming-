<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            body{background-color:rgb(230, 222, 222);}
            .searchform {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-top: 50px;
                background-color: #f2f2f2;
            }
            .searchlabel {
                
                font-weight: bold;
                margin-bottom: 5px;
            }

            .searchform div {
                margin-bottom: 10px;
            }

            .searchinput {
                padding: 5px;
                width: 200px;
            }

            .searchbutton {
                padding: 5px 10px;
            }
            </style>
        <title>Search Users</title>
    </head>

    <body>

        <form class="searchform" action="select_data_formhandler.php" method="post">
            <div>
            <label class="searchlabel" for="search">Search for user:</label>
            </div>

            <div>
            <input class="searchinput" id="search" type="text" name="usersearch" placeholder="Search...">
            </div>

            <button type="submit"class="searchbutton">Search</button>
        </form>

    </body>

</html>
