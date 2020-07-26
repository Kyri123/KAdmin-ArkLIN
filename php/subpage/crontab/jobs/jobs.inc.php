<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$dir = dirToArray('remote/arkmanager/instances/');
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv = new server($dir[$i]);
        $jobs = new jobs();
        $jobs->set($serv->name());
        $cpath = "app/json/servercfg/jobs_" . $serv->name() . ".json";
        if (file_exists($cpath)) {
            $json = $helper->file_to_json($cpath, true);

            // Backup & Update
            $key[0] = 'backup'; $key[1] = 'update';
            for ($z=0;$z<count($key);$z++) {
                if ($json['option'][$key[$z]]['active'] == "true") {
                    $diff =  time() - $json['option'][$key[$z]]['datetime'];
                    if ($diff >= 0) {
                        if ($diff > $json['option'][$key[$z]]['intervall']) {
                            $x = $diff / $json['option'][$key[$z]]['intervall'];
                            $x = floor($x);
                            $x = $x * $json['option'][$key[$z]]['intervall'];
                        }
                        else {
                            $x = $json['option'][$key[$z]]['intervall'];
                        }
                        $nextrun = $json['option'][$key[$z]]['datetime'] + $x;
                        $jobs->arkmanager($key[$z].' '.$json['option'][$key[$z]]['parameter']);
                        $json['option'][$key[$z]]['datetime'] = $nextrun;
                    }
                }
            }
            
            $helper->savejson_exsists($json, $cpath);
        }
    }
}

// Lese und verarbeite Jobs aus der Datenbank
$query = 'SELECT * FROM `ArkAdmin_jobs`';
if($mycon->query($query)->numRows() > 0) {
    $arr = $mycon->query($query)->fetchAll();
    foreach ($arr as $k => $v) {
        $diff = time() - $v["time"];
        $iv = $v["intervall"];
        if($diff >= 0) {
            if ($diff > $iv) {
                $x = $diff / $iv;
                $x = floor($x);
                $x = $x * $iv;
            }
            else {
                $x = $iv;
            }
            $nextrun = $v["time"] + $x;
            $jobs->set($v["server"]);
            $jobs->arkmanager($v["job"].' '.$v['parm']);
            echo $query = 'UPDATE `ArkAdmin_jobs` SET `time` = \''.$nextrun.'\' WHERE `id` = \''.$v["id"].'\'';
            $mycon->query($query);
        }
    }
}



?>