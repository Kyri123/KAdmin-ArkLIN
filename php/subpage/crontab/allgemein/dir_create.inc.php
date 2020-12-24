<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

// Erstelle Dateien & Verzeichnis...
$dirs = array(
    __ADIR__."/app/check/",
    __ADIR__."/app/json/user/",
    __ADIR__."/app/json/servercfg",
    __ADIR__."/app/cache"
);

// suche Server verzeichnisse
for ($i=0;$i<count($dir);$i++) {
    if (strpos($dir[$i], ".cfg") !== false) {
        $dir[$i]    = str_replace(".cfg", null, $dir[$i]);
        $serv       = new server($dir[$i]);
        if($serv !== false) {
            $dirs[]     = __ADIR__."/app/data/shell_resp/log/".$serv->name();
            $dirs[]     = $serv->cfgRead("logdir");
            $dirs[]     = $serv->cfgRead("arkserverroot");
            $dirs[]     = $serv->cfgRead("arkbackupdir");
        }
    }
}

// Erstelle verzeichnisse
for($i=0;$i<count($dirs);$i++) $KUTIL->mkdir($dirs[$i]);


//Erstelle Default Dateien
for ($i=0;$i<count($dir);$i++) {
    if (strpos($dir[$i], ".cfg") !== false) {
        $dir[$i]    = str_replace(".cfg", null, $dir[$i]);
        $serv       = new server($dir[$i]);
        if($serv !== false) {
            $KUTIL->createFile(__ADIR__.'/app/data/shell_resp/log/'.$serv->name().'/last.log', "");
            $KUTIL->createFile(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json', '{}');
        }
    }
}

// schreibe lesen vom Webhelper
$KUTIL->filePutContents(__ADIR__."/app/check/webhelper", time());
