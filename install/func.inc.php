<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

function check_curl() {
    if  (in_array  ('curl', get_loaded_extensions())) {
    return true;
    } else {
    return false;
    }
}

function check_rew() {
    return array_key_exists('HTTP_MOD_REWRITE', $_SERVER);
}

function check_OS() {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return false;
    } else {
        return true;
    }
}

function check_cmd($cmd = "arkmanager") {
    $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
    return !empty($return);
}

function check_server_run() {
    $curr = (time() * 1000);
    $run = (file_exists("arkadmin_server/data/run_time.txt")) ? intval(file_get_contents("arkadmin_server/data/run_time.txt")) : 0;
    $diff = $curr - $run;
    return (5000 > $diff) ? true : false;
}


?>