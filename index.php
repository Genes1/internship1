<html> 
                                                                                                        <!--TODO implement bootstrap-->
    <h1> Create a sync account </h1>
    <form action = "/index.php", method = "post">
        <h2>Folder Lock Information</h2>
        <h3>Login:</h3>
        <input type = "text" name = "login"><br>
        <h3>Password:</h3>
        <input type = "text" name = "password"><br><br>

        <h2>Project Information</h2>
        <h3>Public Key:</h3>
        <input type = "password" name = "public_key" value = "5ocb05o8cb32btmoyedg"><br>
        <h3>Private Key</h3>
        <input type = "password" name = "private_key" value = "hunr9ueg9woel0ql6fti"><br><br>
        <input type = "submit" value = "Submit info">
    </form>

    <?php                                                                                                   
    /*  TODO
        1. Sanitize input             []

        BUGLIST
        - 
    */
        if (isset($_POST['login'])) {
            
            if(!file_exists("TildaSync"))
            {
                mkdir("TildaSync");
            }

            $formdata = array(
                'login'=> $_POST['login'],
                'password'=> $_POST['password'],
                'public_key'=> $_POST['public_key'],
                'private_key'=> $_POST['private_key'],
                'api_DIR'=> dirname(__FILE__),
                'synced' => false,
            );
            
            file_put_contents('TildaSync/info.json', json_encode($formdata, JSON_PRETTY_PRINT));
            file_put_contents('TildaSync/sync.php', file_get_contents('http://142.93.118.183/sync.txt'));
            file_put_contents('TildaSync/syncpage.php', file_get_contents('http://142.93.118.183/syncpage.txt'));
            header('Location: TildaSync/syncpage.php'); 
            
        }
    ?>

</html>