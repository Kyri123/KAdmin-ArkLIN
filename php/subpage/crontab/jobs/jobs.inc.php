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
            if (count($json['jobs']) > 0) {
                foreach($json['jobs'] as $key => $value) {
                    $diff =  time() - $json['jobs'][$key]['datetime'];
                    if ($diff >= 0 && $json['jobs'][$key]['active'] == "true") {
                        if ($diff > $json['jobs'][$key]['intervall']) {
                            $x = $diff / $json['jobs'][$key]['intervall'];
                            $x = floor($x);
                            $x = $x * $json['jobs'][$key]['intervall'];
                        }
                        else {
                            $x = $json['jobs'][$key]['intervall'];
                        }
                        $nextrun = $json['jobs'][$key]['datetime'] + $x;
                        $jobs->arkmanager($json['jobs'][$key]['action'].' '.$json['jobs'][$key]['parameter']);
                        $json['jobs'][$key]['datetime'] = $nextrun;
                    }
                }
            }

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


?>