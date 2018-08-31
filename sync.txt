<?php
    $id = $_REQUEST["id"];
    ini_set("allow_url_fopen", true);  
    echo "exec phpsync on " . $id;
    define('TILDA_PROJECT_ID', $id);   
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
    return $id . " updated";
?>