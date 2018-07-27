<?php
   include "tilda-php/tilda-php-master/classes/Tilda/Api.php";
   include "tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    use \Tilda;
    define('TILDA_PUBLIC_KEY', '5ocb05o8cb32btmoyedg');
    define('TILDA_SECRET_KEY', 'hunr9ueg9woel0ql6fti');
    define('TILDA_PROJECT_ID', '577099');
    $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY);
    $local = new Tilda\LocalProject(array('projectDir' => 'tilda'));
    $local->setProject($api->getProjectExport(TILDA_PROJECT_ID));
    $local->createBaseFolders();
    $local->copyCssFiles('css');
    $local->copyJsFiles('js');
    $local->copyImagesFiles('img');

   // $arFiles = $local->copyCssFiles('css');
    /*
    if (! $arFiles) {
        die('Error in copy CSS files [' . $api->lastError . ']');
    }
    */
    //print_r($arFiles);
    //print_r($local->getProjectFullDir());
    //print_r($api->getProjectsList());
?>



