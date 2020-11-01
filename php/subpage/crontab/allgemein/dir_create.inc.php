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
    __ADIR__."/app/check/",
    __ADIR__."/app/json/user/",
    __ADIR__."/app/json/servercfg",
    __ADIR__."/app/cache"
);

// suche Server verzeichnisse
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv = new server($dir[$i]);
        $dirs[] = __ADIR__."/app/data/shell_resp/log/".$serv->name();
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
        if (!file_exists(__ADIR__.'/app/data/shell_resp/log/'.$serv->name().'/last.log')) file_put_contents(__ADIR__.'/app/data/shell_resp/log/'.$serv->name().'/last.log', null);
        if (!file_exists(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json')) file_put_contents(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json', '{}');
    }
}

// schreibe lesen vom Webhelper
file_put_contents(__ADIR__."/app/check/webhelper", time());
