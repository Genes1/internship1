<?php
    $info = json_decode(file_get_contents("info.json"), true); 
    $id = $_REQUEST["id"];    

    $loc = str_replace("\\", "/", urldecode($_REQUEST["loc"])); 
    $dloc = str_replace("\\", "/", urldecode($_REQUEST["dloc"]));     
    $api_DIR = str_replace("\\", "/", $info['api_DIR']."/");

    include "Api.php";
    include "LocalProject.php";
    set_time_limit(0);
    //ini_set('display_errors',1);


    ini_set("allow_url_fopen", true);  
    ini_set("auto_detect_line_endings", true);
    define('TILDA_PROJECT_ID', $id);  
    define('TILDA_PUBLIC_KEY', $info['public_key']);
    define('TILDA_SECRET_KEY', $info['private_key']);
    $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY); 
    /*  TODO
        1. Check for empty categories                                              []
    */

    $fullpath = explode("/", $dloc);
    $compath = rtrim($api_DIR, "/ ");
    
    foreach($fullpath as $dirpart){
        $compath = $compath."/".$dirpart;
        if(file_exists($compath)){
            if(!is_dir($compath)){ 
                unlink($compath);
            }
        } else {mkdir($compath);}   

    }

    $local = new Tilda\LocalProject(array('projectDir' => $dloc));
    $local->setProject($api->getProjectExport(TILDA_PROJECT_ID));
    $local->createBaseFolders();
    $local->copyCssFiles('css');
    $local->copyJsFiles('js');


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
    file_put_contents("../".$dloc ."/htaccess", $api->getProjectExport( TILDA_PROJECT_ID)["htaccess"] );
    echo "htaccess saved  <br>";
    file_put_contents("../". $dloc ."/sitemap.xml", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/sitemap.xml") );
    echo "sitemap saved  <br>";
    file_put_contents("../". $dloc ."/robots.txt", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/robots.txt") );
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
                file_put_contents('../'.$dloc.'\img\\'.$image["to"], file_get_contents($image["from"]) ); 
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
    }
    */
    for ($x = 0; $x < count($info) - 6; $x++) {
        if ( $info[$x]['id'] == $id ){ 
            //echo "info[i].id == id is true somewhere <br>";
            $info[$x]['savedlocation'] = str_replace($api_DIR, "", $compath);
            //file_put_contents("tildasync/info.json", json_encode($info, JSON_PRETTY_PRINT));
        }
    }

    //$info[].savedlocation = $compath;
    file_put_contents("info.json", json_encode($info, JSON_PRETTY_PRINT));

    ini_set("allow_url_fopen", false);   
    echo "<i><center>Project " . $id . " finished syncing.</center></i>";
?>