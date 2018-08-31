<?php   
    clearstatcache();
    ini_set('display_errors',1);
    ini_set("allow_url_fopen", true);
    include "../tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "../tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    /*So, what's the plan? On initial call, delete index file and lock the folder[X]. Along with this, make a call to the Tilda API using
      the keys from info.json to create an element for the project list in the json[X]. Follow up with creating the divs as needed[X].
      The sync buttons still need to be created[X]. AJAX will need to be run in order for the sync to occur[]. Implement the tilda ID into
      the divs ("id" = "xxxxxx"?)
    */

    //FIRST TIME RUN=====================
    $info = json_decode(file_get_contents("info.json"), true); 
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
    <script>
        
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() { 
            if (this.readyState === 4 && this.status === 200) {
                const container = document.getElementById("container");
                let input = JSON.parse(this.responseText);
                for (var i in input ) {
                    if (i.match(/^\d/)){ 
                        var id = input[i].id;
                        container.innerHTML +=                                              //TODO dix the outlines
                            "<div class = \"box\" id = \"div" + 
                            id + 
                            "\" style = \" width:50%; height:50px; background:rgb(212, 206, 206); border: \"thin\" >" + 
                            id +  
                            "<button type = \"button\" id = \"button" + 
                            id + 
                            "\" onClick = \"sync("+ 
                            id +
                            ")\"> Sync </button></div>"; 
                            
                    }
                }
            } else {
                //console.log("An error occurred.");
            }
        };
        xhr.open("GET", "info.json");
        xhr.send(); 
        
        
        //=========================================================
        
        function sync(id){
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("div" + id).innerHTML = this.responseText;
                }
            }
            xmlhttp.open("GET", "sync.php?id="+ " \" " + id + " \" ", true);
            xmlhttp.send();
        }
        

    </script>

</html>