<?php
    include "tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    use \Tilda;
    $info = json_decode(file_get_contents("TildaSync/info.json"), true); //change the pathing for when this is a dl
    echo $info['api_DIR'];
    if(file_exists($info['api_DIR'])){unlink($info['api_DIR']);} //file delet
    //ini_set('display_errors',1);
    ini_set("allow_url_fopen", true);   //will this cause problems?
    define('TILDA_PUBLIC_KEY', '5ocb05o8cb32btmoyedg'); //5ocb05o8cb32btmoyedg
    define('TILDA_SECRET_KEY', 'hunr9ueg9woel0ql6fti'); //hunr9ueg9woel0ql6fti
    define('TILDA_PROJECT_ID', '577099'); //577099
    $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY);
    $local = new Tilda\LocalProject(array('projectDir' => 'tilda'));
    $local->setProject($api->getProjectExport(TILDA_PROJECT_ID));
    //Attempt to create folders and copy css, js, and image files
    $local->createBaseFolders();
    $local->copyCssFiles('css');
    $local->copyJsFiles('js');

    //saving htaccess, robots, and sitemap
    file_put_contents("tilda/.htaccess", $api->getProjectExport( TILDA_PROJECT_ID)["htaccess"] );
    file_put_contents("tilda/sitemap.xml", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/sitemap.xml") );   //http://project788111.tilda.ws/
    file_put_contents("tilda/robots.txt", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/robots.txt") );

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
           file_put_contents('tilda\img\\'.$image["to"], file_get_contents($image["from"]) );
        } 
    }

    ini_set("allow_url_fopen", false);   //closing just in case
    
?>