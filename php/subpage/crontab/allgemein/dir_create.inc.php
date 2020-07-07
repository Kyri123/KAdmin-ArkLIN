<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Erstelle Dateien & Verzeichnis...
$dirs = array(
    "app/check/",
    "app/json/user/",
    "app/json/servercfg",
    "sh/serv",
    "cache"
);

// suche Server verzeichnisse
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv = new server($dir[$i]);
        $dirs[] = 'sh/resp/'.$serv->name();
        $dirs[] = 'remote/serv/server_ID_'.$serv->name().'_logs';
        $dirs[] = $serv->cfg_read("logdir");
        $dirs[] = $serv->cfg_read("arkserverroot");
        $dirs[] = $serv->cfg_read("arkbackupdir");
    }
}

// erstelle verzeichnisse
for($i=0;$i<count($dirs);$i++) {
    if(!file_exists($dirs[$i])) mkdir($dirs[$i], 0777, true);
}


//Erstelle Default Dateien
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        if (!file_exists('sh/resp/'.$serv->name().'/last.log')) file_put_contents('sh/resp/'.$serv->name().'/last.log', null);
        if (!file_exists('app/json/serverinfo/'.$serv->name().'.json')) file_put_contents('app/json/serverinfo/'.$serv->name().'.json', '{}');
        if (!file_exists('sh/serv/jobs_ID_'.$serv->name().'.sh')) file_put_contents('sh/serv/jobs_ID_'.$serv->name().'.sh', null);
        if (!file_exists('sh/serv/sub_jobs_ID_'.$serv->name().'.sh')) file_put_contents('sh/serv/sub_jobs_ID_'.$serv->name().'.sh', null);
    }
}

// schreibe lesen vom Webhelper
file_put_contents("app/check/webhelper", time());
?>