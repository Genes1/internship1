<?php   
    clearstatcache();
    ini_set('display_errors',1);
    ini_set("allow_url_fopen", true);
    include "../tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "../tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    /*  TO DO
        1. Let user choose install path                                                             []
        2. Update divs to show name, desc, id, last synced, etc. (investigate time as last synced)  []
        3. Update by page within project                                                            []
        4. Fix tilda htaccess   https://github.com/tivie/php-htaccess-parser                        []
        5. Make the clear button work - or remove it                                                []

        BUG LIST
        - htaccess still requests password after initial loadup
        - clear button does not work

        THINGS TO CONSIDER
        + If a project is already synced somewhere and the path is changed
    */

    $info = json_decode(file_get_contents("info.json"), true);      //catch api limit error?
    if(file_exists("../index.php")){
        unlink("../index.php");
        define('TILDA_PUBLIC_KEY', $info['public_key']);                       
        define('TILDA_SECRET_KEY', $info['private_key']);      
        $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY);
        //print_r(array_merge($info, $api->getProjectsList()));
        $info = array_merge($info, $api->getProjectsList());
        file_put_contents("info.json", json_encode($info, JSON_PRETTY_PRINT));
        $info = json_decode(file_get_contents("info.json"), true);
        //print_r(json_encode($info, JSON_PRETTY_PRINT) . "<br/>");
        file_put_contents('../.htpasswd', $info['login'].':{SHA}'. base64_encode(sha1($info['password'], true)) );
        file_put_contents('.htaccess','AuthType Basic' . PHP_EOL . 'AuthName "Sync Folder" ' . PHP_EOL . 'AuthUserFile '.$info['api_DIR'].'\.htpasswd '. PHP_EOL .'Require valid-user'. PHP_EOL .'Allow from 99.88.77.66'. PHP_EOL ."Satisfy Any");
    } 
    ini_set("allow_url_fopen", false);   //closing just in case

?>

<html>
    <div id="container"></div>
    <!--<button onClick="clear()">clear</button>-->
    <div id="syncing" style = "text-align:center;font-size:30px"></div>
    <div class="box" id="updatelog" style="background:rgb(182, 194, 255); overflow:auto; height:500px; border-style:solid; border-width:thin; padding:10px" ><div>

    <script>
        const zone = document.getElementById("updatelog");
        const synctext = document.getElementById("syncing");
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() { 
            if (this.readyState === 4 && this.status === 200) {
                const container = document.getElementById("container");
                let input = JSON.parse(this.responseText);
                for (let i in input ) {
                    if (i.match(/^\d/)){ 
                        var id = input[i].id;   //I have to get the name/description of the project 
                        container.innerHTML += "<div class = \"box\" id = \"div" + id + "\" style =  \" font-size:30px; width:100%; height:40px; background:rgb(212, 206, 206); border-style:solid; border-width:thin\" vertical-align: middle; line-height: 40px;>" + input[i].title + " [id " + id + "]" +  "<button type = \"button\" id = \"button" + id + "\" onClick = \"sync("+ id +")\" height:20px; style = \"position:absolute; right:5%; vertical-align: middle; \"> Sync </button></div>"; 
                    }
                    container.innerHTML += "<br>";
                }
            } else {
                //console.log("An error occurred.");
            }
        };
        xhr.open("GET", "info.json");
        xhr.send(); 
        
        /*                      get this working
        function clear(){
            var div = document.getElementById('updatelog');
            while(div.firstChild){
                div.removeChild(div.firstChild);
            }
        }
        */

        function sync(id){            
            synctext.innerHTML += "Syncing Project " + id + "...";//add title somehow
            const xhr2 = new XMLHttpRequest();
            xhr2.onreadystatechange = function() { 
                if (this.readyState === 4 && this.status === 200) {
                    var time = new Date();                                      //this is global, last synced potential, toLocaleString() 
                    zone.insertAdjacentHTML("afterbegin", this.responseText); 
                    zone.insertAdjacentHTML("afterbegin", "<center><h1>Saving " + id + "<p style = \" font-size:15px; color:gray\">" + time.toLocaleTimeString()  + "</p> </center>");
                    synctext.innerHTML = "";
                    //synctext.scrollTop = 0; dont work
                    //zone.innerHTML += this.responseText;
                } else {
                    //console.log("An error occurred.");
                }
            };
            xhr2.open("GET", "sync.php?id="+id, true);
            xhr2.send();
            
        }
        

    </script>

</html>