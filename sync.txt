<?php
    //ini_set('display_errors',1);
    ini_set("allow_url_fopen", true);  
    include "../tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "../tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
                                                                            //use \Tilda; what does this even do? not clear
                                                                            //TODO divide class up into methods(?) and use ajax to display a form or split up into two files 

    /*So, what's the plan? On initial call, delete index file and lock the folder[X]. Along with this, make a call to the Tilda API using
      the keys from info.json to create an element for the project list in the json[X]. Follow up with creating the divs as needed[].

      The sync buttons still need to be created[]. AJAX will need to be run in order for the sync to occur[]. Implement the tilda ID into
      the divs ("id" = "xxxxxx"?)
    */

    //FIRST TIME RUN=====================
    $info = json_decode(file_get_contents("info.json"), true); 
    if(file_exists($info['api_DIR']."/index.php")){
        define('TILDA_PROJECT_ID', $info['project_id']);   
        define('TILDA_PUBLIC_KEY', $info['public_key']);                       
        define('TILDA_SECRET_KEY', $info['private_key']);      
        $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY);
        $local = new Tilda\LocalProject(array('projectDir' => 'tilda'));
        $local->setProject($api->getProjectExport(TILDA_PROJECT_ID));             //will this work on non-first?
        //print_r(array_merge($info, $api->getProjectsList()));
        $info = array_merge($info, $api->getProjectsList());
        file_put_contents("info.json", json_encode($info, JSON_PRETTY_PRINT));
        $info = json_decode(file_get_contents("info.json"), true);
        //print_r(json_encode($info, JSON_PRETTY_PRINT) . "<br/>");
        file_put_contents('.htpasswd', $info['login'].':{SHA}'. base64_encode(sha1($info['password'], true)) );
        file_put_contents('../.htaccess','AuthType Basic' . PHP_EOL . 'AuthName "Sync Folder" ' . PHP_EOL . 'AuthUserFile '.$info['api_DIR'].'\.htpasswd '. PHP_EOL .'Require valid-user');
    } 
    //==================================

    //START TO SAVING================================================
    //define('TILDA_PROJECT_ID', $info['project_id']);                         //the id should be set on BUTTON PRESS
    //$api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY);
    //$local = new Tilda\LocalProject(array('projectDir' => 'tilda'));
    //$local->setProject($api->getProjectExport(TILDA_PROJECT_ID));
    //Attempt to create folders and copy css, js, and image files
    $local->createBaseFolders();
    $local->copyCssFiles('css');
    $local->copyJsFiles('js');
    //saving htaccess, robots, and sitemap
    file_put_contents("../tilda/.htaccess", $api->getProjectExport( TILDA_PROJECT_ID)["htaccess"] );
    file_put_contents("../tilda/sitemap.xml", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/sitemap.xml") );
    file_put_contents("../tilda/robots.txt", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/robots.txt") );
    //file_put_contents("../tilda/404.html", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/404.html") ); //doesn't currently work
    //saving HTML
    $pageList = $api->getPagesList(TILDA_PROJECT_ID);
    foreach($pageList as $page)
    {  
        $local->savePage( $api->getPage(array_pop(array_reverse($page))) );
    } 
    //saving the image files using page export
    foreach($pageList as $page)
    {
        foreach($api->getPageExport($page["id"])["images"] as $image)
        {
           file_put_contents('../tilda\img\\'.$image["to"], file_get_contents($image["from"]) );
        } 
    }
    ini_set("allow_url_fopen", false);   //closing just in case
    //===============================================================
?>

<html>
 
        <div id="container"></div>

        <script>

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var jsArr = JSON.parse(this.responseText);
                    //document.getElementById("demo").innerHTML = myObj.name;
                }
            };
            xmlhttp.open("GET", "info.json", true); //post? regex s.match(/^\d/) for start with number
            xmlhttp.send();

            var container = document.getElementById("container");
            for (var i = 0; i < idArr.length; i++) 
            {   
                if (jsArr[i].match(/^\d/)){ //does this check index or ele?
                    container.innerHTML += '<div class="box" style="width:50%; height:50px; background:rgb(212, 206, 206); border:thin dotted">' + idArr[i]['id'] +  '</div>';
                }
            } 

        </script>

</html>