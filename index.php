<?php
    //DECLARATION
    include "tilda-php/tilda-php-master/classes/Tilda/Api.php";
    include "tilda-php/tilda-php-master/classes/Tilda/LocalProject.php";
    use \Tilda;
    ini_set("allow_url_fopen", true);                        //will this cause problems?
    define('TILDA_PUBLIC_KEY', '5ocb05o8cb32btmoyedg');
    define('TILDA_SECRET_KEY', 'hunr9ueg9woel0ql6fti');
    define('TILDA_PROJECT_ID', '577099');
    $api = new Tilda\Api(TILDA_PUBLIC_KEY, TILDA_SECRET_KEY);
    $local = new Tilda\LocalProject(array('projectDir' => 'tilda'));
    $local->setProject($api->getProjectExport(TILDA_PROJECT_ID));

    //Attempt to create folders and copy css, js, and image files
    $local->createBaseFolders();
    $local->copyCssFiles('css');
    $local->copyJsFiles('js');
    $local->copyImagesFiles('img'); //the image copying fails, despite no error message. manual download is farther down

    //saving the html files
    $projectList = $api->getPagesList(TILDA_PROJECT_ID);
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

    //saving the image files manually
    $increment = 0;
    foreach($api->getPagesList(TILDA_PROJECT_ID) as $page){
        foreach($api->getPageExport($page["id"]) as $outerArrAcc)
        {
            if ($increment == 11){
                foreach($outerArrAcc as $innerArr)
                {     
                    file_put_contents ('tilda\img\\'.$innerArr["to"], file_get_contents($innerArr["from"]) );
                }
            }
            $increment++;
        } 
    }
?>