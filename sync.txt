<?php
    $id = $_REQUEST["id"];                                                                       //TODO update live, maybe first time do saving and then syncing after? make a check for an already existing project?
    include "../tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "../tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    set_time_limit(60);
    //ini_set('display_errors',1);

    /*  1. Fix the naming bug                                                      []
        2. Check if project folder exists for non-destructive sync                 []
        3. Check for empty categories                                              []
    */
    
    ini_set("allow_url_fopen", true);  
    $info = json_decode(file_get_contents("info.json"), true); 
    //echo "<center><h1>Saving " . $id . "</b></center> <p style=\"font-color:gray\">" . localtime(time(), true)[3] . date('g:i:s A') .  "</p> <br>";
    define('TILDA_PROJECT_ID', $id);  
    define('TILDA_PUBLIC_KEY', $info['public_key']);                       
    define('TILDA_SECRET_KEY', $info['private_key']);      
    $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY); 
    $local = new Tilda\LocalProject(array('projectDir' => 'tilda' . $id));
    $local->setProject($api->getProjectExport(TILDA_PROJECT_ID));
    if(!file_exists('tilda' . $id)){     
        $local->createBaseFolders();
    }
    $local->copyCssFiles('css');
    $local->copyJsFiles('js');

    //nondestruct
    /*
        if (file_exists("project".$id) && is_dir("project".$id)){
            set some variable to true
            modify rest of code to check for file existence before replacement
        }

    */

    //loading DOM                           can I even use dom? probably but diff setup needed
    /*
    $dom = new DOMDocument();
    $dom->load("syncpage.php");
    $dom->validate();
    $div = $dom->getElementById('updatelog');
    echo "<script>var d1 = document.getElementById('updatelog'); d1.insertAdjacentHTML('afterbegin', 'html from php'); </script>";
    */
 
    //saving htaccess, robots, and sitemap
    echo "<b> SAVING MISC </b> <hr>";
    file_put_contents("../tilda/.htaccess", $api->getProjectExport( TILDA_PROJECT_ID)["htaccess"] );
    echo "htaccess saved  <br>";
    file_put_contents("../tilda/sitemap.xml", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/sitemap.xml") );
    echo "sitemap saved  <br>";
    file_put_contents("../tilda/robots.txt", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/robots.txt") );
    echo "robots saved  <br>";
    //file_put_contents("../tilda/404.html", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/404.html") ); //doesn't currently work


    $pageList = $api->getPagesList(TILDA_PROJECT_ID);
    if (!empty($pageList)){

        echo "<br><b> SAVING PAGES </b> <hr> ";
        //saving HTML
        foreach($pageList as $page)
        {  
            $var = $api->getPage(array_pop(array_reverse($page)));
            $local->savePage( $var/*$api->getPage(array_pop(array_reverse($page)))*/ );
            echo "Saving page \"<u>". $var['title'] . "</u>\": <b>" . $var['id'] . ".html</b> saved <br>";
            if(!empty($var['descr'])){
                echo "Description: \"<i>" . $var['descr'] . "</i>\" <br><br>";
            }
        } 

        //saving the image files using page export
        echo "<br><b> SAVING IMAGES </b> <hr>";
        foreach($pageList as $page)
        {
            foreach($api->getPageExport($page["id"])["images"] as $image)
            {
                file_put_contents('../tilda'.$id.'\img\\'.$image["to"], file_get_contents($image["from"]) );    //PROBLEMS FOR CUSTOM NAME
                echo "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"" . $image["from"] . "\">Image saved</a>: <b>" . $image["to"] . "</b><br>";
            } 
        }   

    } else {echo "<i><b>No pages found in project.</b></i>";}

    /*
    if (file_exists("../project" . $id)){
        $dir = "../project" . $id;
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }                                                           //TODO implement correct pathing (user specified)
    //sleep(3);
    rename("../tilda", "../project" . $id);    //rename arg1 to arg2
    */

    ini_set("allow_url_fopen", false);   
    echo "<i><center>Project " . $id . " finished syncing.</center></i>";
?>