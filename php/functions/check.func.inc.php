<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

function check_server()
{
    $header = @get_headers("http://127.0.0.1:30000/");
    return is_array($header);
}

function check_curl() {
    return (in_array  ('curl', get_loaded_extensions())) ? true : false;
}

function check_rew() {
    return array_key_exists('HTTP_MOD_REWRITE', $_SERVER);
}

function check_cmd($cmd = "arkmanager") {
    $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
    return !empty($return);
}

function isie() {
    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
        return true;
    }
    elseif (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false)) {
        return true;
    }
    elseif (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false)) {
        return true;
    }
    return false;
}

function check_webhelper() {
    if(!check_server()) {
        return false;
    }
    else {
        $curr = (file_exists("arkadmin_server/data/version.txt")) ? trim(file_get_contents("arkadmin_server/data/version.txt")) : "curr_not_found";
        $run = json_decode(file_get_contents("http://127.0.0.1:30000/"), true)["version"];
        return ($curr == $run) ? true : false;
    }
}

function check_server_json_bool($key) {
    if(!check_server()) {
        return false;
    }
    else {
        $bool = json_decode(file_get_contents("http://127.0.0.1:30000/"), true)[$key];
        return ($bool == "true") ? true : false;
    }
}

?>