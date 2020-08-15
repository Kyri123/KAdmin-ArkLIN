<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$ipath = 'remote/arkmanager/instances/';
$dir = scandir($ipath);
$json_all['onserv'] = $json_all['maxserv'] = 0;

$file = 'app/json/serverinfo/all.json';
for ($i=0;$i<count($dir);$i++) {
    $ifile = $ipath.$dir[$i];
    // wenn es ein Verzeichnis ist skippe
    if(is_dir($ifile)) continue;

    $ifile_info = pathinfo($ipath.$dir[$i]);
    $checkit = false;

    if ($ifile_info['extension'] == "cfg" && strpos($ifile_info['filename'], "example") !== true) {
        $servdata = $serv = new server($ifile_info['filename']);
        // erstelle STATUS
        $raw = 'app/json/serverinfo/raw_'.$serv->name().'.json';
        if (file_exists($raw)) {
            $checkit = true; $server = null;

            $jsonfile = 'app/json/serverinfo/'.$serv->name().'.json';
            $raw_jsonfile = $helper->file_to_json('app/json/serverinfo/raw_'.$serv->name().'.json');
            $server = $helper->file_to_json($jsonfile);
            
            // setzte Default daten
            $server['warning_count'] = 0;
            $server['error_count'] = 0;
            $server['error'] = null;
            $server['error'][] = null;
            $server['warning'] = null;
            $server['warning'][] = null;
            $server['aplayers'] = 0;
            $server['players'] = 0;
            $server['pid'] = 'No';
            $server['run'] = 'No';
            $server['online'] = 'No';
            $server['listening'] = 'No';
            $server['installed'] = $serv->isinstalled();

            // verarbeite AA-Server response
            foreach($raw_jsonfile as $k => $v) $server[$k] = $v;
            $server['run'] = ($server['run'] === true) ? "Yes" : "No";

            // lese Status für die Ausführung von Aktionen
            if (!file_exists('sh/serv/jobs_ID_'.$serv->name().'.state')) file_put_contents('sh/serv/jobs_ID_'.$serv->name().'.state', 'TRUE');
            $serv_state = trim(file_get_contents('sh/serv/jobs_ID_'.$serv->name().'.state'));
            $log = 'sh/resp/'.$serv->name().'/last.log';
            if ($serv_state == 'TRUE' && timediff($log, ($webserver['config']['ShellIntervall'] / 1000 + 3))) {
                $server['next'] = 'FALSE';
            }
            else {
                $server['next'] = 'TRUE';
            }

            //schreibe Informationen zur verarbeitung
            if ($checkit) {
                $helper->savejson_create($server, $jsonfile);
            }

            // Online & Max Counter (Global Information)
            if ($servdata->statecode() == 2) {
                $json_all['onserv']++;
            }

            $json_all['maxserv']++;
            $json_all['cfgs'][] = $servdata->name().'.cfg';
        }
    }
}

$helper->savejson_create($json_all, $file);
?>