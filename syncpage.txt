<?php   
    clearstatcache();
    ini_set('display_errors',1);
    ini_set("allow_url_fopen", true);
    include "../tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "../tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    /*  1. Let user choose install path                                                             []
        2. Update divs to show name, desc, id, last synced, etc. (investigate time)                 []
        3. Update by page within project                                                            []
        4. Fix htaccess                                                                             []
        5. Make the clear button work - or remove it                                                []
    */

    //FIRST TIME RUN=====================
    $info = json_decode(file_get_contents("info.json"), true); 
    //print_r($info);
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
        file_put_contents('.htaccess','AuthType Basic' . PHP_EOL . 'AuthName "Sync Folder" ' . PHP_EOL . 'AuthUserFile '.$info['api_DIR'].'\.htpasswd '. PHP_EOL .'Require valid-user');
    } 
    //==================================
    ini_set("allow_url_fopen", false);   //closing just in case

?>

<html>
    <div id="container"></div>
    <button onClick="clear()">clear</button>
    <div class="box" id="updatelog" style="background:rgb(182, 194, 255); overflow:auto; height:500px; border-style:solid;border-width:thin " ><div>

    <script>
        
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() { 
            if (this.readyState === 4 && this.status === 200) {
                const container = document.getElementById("container");
                let input = JSON.parse(this.responseText);
                for (var i in input ) {
                    if (i.match(/^\d/)){ 
                        var id = input[i].id;   //I have to get the name/description of the project 
                        container.innerHTML += "<div class = \"box\" id = \"div" + id + "\" style =  \" font-size:30px; width:100%; height:40px; background:rgb(212, 206, 206); border-style:solid; border-width:thin\" vertical-align: middle; line-height: 40px;>" + id +  "<button type = \"button\" id = \"button" + id + "\" onClick = \"sync("+ id +")\" height:20px; style = \"position:absolute; right:5%; vertical-align: middle; line-height: 20px; padding:10px \"> Sync </button></div>"; 
                    }
                }
            } else {
                //console.log("An error occurred.");
            }
        };
        xhr.open("GET", "info.json");
        xhr.send(); 
        
        function clear(){
            var div = document.getElementById('updatelog');
            while(div.firstChild){
                div.removeChild(div.firstChild);
            }
        }

        function sync(id){                                      //TODO freeze button activity, open third param won't work for live update if that ever happens 
        //toLocaleString() look into for last updated on btn press
            const xhr2 = new XMLHttpRequest();
            const zone = document.getElementById("updatelog");
            xhr2.onreadystatechange = function() { 
                if (this.readyState === 4 && this.status === 200) {
                    var time = new Date();
                    //var d1 = document.getElementById('updatelog');            for bottom-up insert
                    //d1.insertAdjacentHTML('afterbegin', 'html from php');
                    zone.innerHTML += "<center><h1>Saving " + id + "<p style =  \" font-size:15px; color:gray\">" + time.toLocaleTimeString()  + "</p> </center>";
                    zone.innerHTML += this.responseText;
                } else {
                    //console.log("An error occurred.");
                }
            };
            xhr2.open("GET", "sync.php?id="+id, true);
            xhr2.send();
        }
        

    </script>

</html>