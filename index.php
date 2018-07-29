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
    //echo json_encode($api->getPagesList(TILDA_PROJECT_ID), JSON_PRETTY_PRINT);

    $projectList = $api->getPagesList(TILDA_PROJECT_ID);
    /*saving the html files*/
    $increment = 0;
    foreach($projectList as $mydata)
    {
         $increment = 0;
         foreach($mydata as $values)
         {  
            if($increment == 0){
                $local->savePage($api->getPage($values));
            }
            $increment++;
         }
    } 

    //$local->savePage($api->getPage(2433918)); //TODO make loop to iterate for each page, this saves an html for the page id
    //print_r($api->getPageExport(2433918)); //this does indeed show images

?>



