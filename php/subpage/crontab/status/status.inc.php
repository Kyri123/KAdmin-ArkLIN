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

$ipath              = $KUTIL->path(__ADIR__.'/remote/arkmanager/instances/')["/path/"];
$dir                = scandir($ipath);
$json_all['onserv'] = $json_all['maxserv'] = 0;
$file               = $KUTIL->path(__ADIR__.'/app/json/serverinfo/all.json')["/path"];

for ($i=0;$i<count($dir);$i++) {
    $ifile = $KUTIL->path($ipath.$dir[$i])["/path"];

    // wenn es ein Verzeichnis ist skippe
    if(is_dir($ifile)) continue;

    $ifile_info = pathinfo($ifile);
    if ($ifile_info['extension'] == "cfg" && strpos($ifile_info['filename'], "example") === false) {
        $servdata   = $serv = new server($ifile_info['filename']);

        // erstelle STATUS
        $raw        = $KUTIL->path(__ADIR__.'/app/json/serverinfo/raw_'.$serv->name().'.json')["/path"];
        if (@file_exists($raw)) {

            $jsonfile       = $KUTIL->path(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json')["/path"];
            $raw_jsonfile   = $helper->fileToJson($raw);
            $server         = $helper->fileToJson($jsonfile);
            if($server === false) $server = [];
            
            // setzte Default daten
            $server['warning_count']    = 0;
            $server['error_count']      = 0;
            $server['error']            = null;
            $server['error'][]          = null;
            $server['warning']          = null;
            $server['warning'][]        = null;
            $server['aplayers']         = 0;
            $server['players']          = 0;
            $server['pid']              = 'No';
            $server['run']              = 'No';
            $server['online']           = 'No';
            $server['listening']        = 'No';
            $server['installed']        = $serv->isInstalled();

            // verarbeite AA-Server response
            foreach($raw_jsonfile as $k => $v) $server[$k] = $v;
            $server['run']              = ($server['run'] === true) ? "Yes" : "No";

            // lese Status für die Ausführung von Aktionen
            $statefile                  = $KUTIL->path(__ADIR__.'/app/data/shell_resp/state/'.$serv->name().'.state')["/path"];
            $KUTIL->createFile($statefile, 'TRUE');
            $serv_state                 = trim($KUTIL->fileGetContents($statefile)) === 'TRUE';
            $log                        = $KUTIL->path(__ADIR__.'/app/data/shell_resp/log/'.$serv->name().'/last.log')["/path"];
            $server['next']             = ($serv_state || timediff($log, ($webserver['config']['ShellIntervall'] / 1000 + 3))) ? 'FALSE' : 'TRUE';

            //schreibe Informationen zur verarbeitung
            $helper->saveFile($server, $jsonfile);

            // Online & Max Counter (Global Information)
            if ($servdata->stateCode() == 2) $json_all['onserv']++;

            $json_all['maxserv']++;
            $json_all['cfgs'][]             = $servdata->name().'.cfg';
            $json_all['cfgs_only_name'][]   = $servdata->name();
        }
    }
}

$helper->saveFile($json_all, $file);