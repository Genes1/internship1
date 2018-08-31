<?php
    $id = $_REQUEST["id"];                                                                       //TODO update live, maybe first time do saving and then syncing after? make a check for an already existing project?
    include "../tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "../tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    set_time_limit(60);
    //ini_set('display_errors',1);
    ini_set("allow_url_fopen", true);  
    $info = json_decode(file_get_contents("info.json"), true); 
    echo "<center><h1>Saving " . $id . "</b></center><br>";
    define('TILDA_PROJECT_ID', $id);  
    define('TILDA_PUBLIC_KEY', $info['public_key']);                       
    define('TILDA_SECRET_KEY', $info['private_key']);      
    $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY); 
    $local = new Tilda\LocalProject(array('projectDir' => 'tilda'));
    $local->setProject($api->getProjectExport(TILDA_PROJECT_ID));     
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
    echo "<b> SAVING PAGES </b> <hr> <br>";
    foreach($pageList as $page)
    {  
        $local->savePage( $api->getPage(array_pop(array_reverse($page))) );
        echo "Page " . $api->getPage(array_pop(array_reverse($page)))['id'] . " saved <br>";
    } 

    //saving the image files using page export
    echo "<br><b> SAVING IMAGES </b> <hr> <br>";
    foreach($pageList as $page)
    {
        foreach($api->getPageExport($page["id"])["images"] as $image)
        {
            file_put_contents('../tilda\img\\'.$image["to"], file_get_contents($image["from"]) );
            echo "Image saved as " . $image["to"] . " <br>";
        } 
    }   

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
    rename("../tilda", "../project" . $id);
    ini_set("allow_url_fopen", false);   
    echo "<hr><i>Project " . $id . " finished syncing.</i>";
?>