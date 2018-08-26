<?php
    //ini_set('display_errors',1);
    include "../tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "../tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    //use \Tilda; what does this even do? not clear
    //TODO divide class up into methods and use ajax to display a form or split up into two files 

    $info = json_decode(file_get_contents("info.json"), true); //change the pathing for when this is a dl
    if(file_exists($info['api_DIR']."/index.php")){
        file_put_contents('.htpasswd', $info['login'].':{SHA}'. base64_encode(sha1($info['password'], true)) );
        file_put_contents('../.htaccess','AuthType Basic' . PHP_EOL . 'AuthName "Sync Folder" ' . PHP_EOL . 'AuthUserFile '.$info['api_DIR'].'\.htpasswd '. PHP_EOL .'Require valid-user');
        unlink($info['api_DIR'] . "/index.php");
    } 

    ini_set("allow_url_fopen", true);   //will this cause problems? remember to close
    define('TILDA_PUBLIC_KEY', $info['public_key']); //5ocb05o8cb32btmoyedg
    define('TILDA_SECRET_KEY', $info['private_key']); //hunr9ueg9woel0ql6fti
    define('TILDA_PROJECT_ID', $info['project_id']); //577099
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
    file_put_contents("tilda/404.html", file_get_contents("http://project" . TILDA_PROJECT_ID . ".tilda.ws/404.html") ); //doesn't currently work

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