<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

/**
 * Prüft ob der ArkAdmin-Server Online ist
 * @return bool
 */
function check_server()
{
    global $webserver;
    $header = @get_headers("http://127.0.0.1:".$webserver['config']['port']."/");
    return is_array($header);
}

/**
 * Prüft ob der Browser IE ist
 *
 * @return bool
 */
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

/**
 * Prüft den Arkadmin-Serber
 *
 * @return bool
 */
function check_webhelper() {
    global $webserver;
    if(!check_server()) {
        return false;
    }
    else {
        $curr = (file_exists("arkadmin_server/data/version.txt")) ? trim(file_get_contents("arkadmin_server/data/version.txt")) : "curr_not_found";
        $run = json_decode(file_get_contents("http://127.0.0.1:".$webserver['config']['port']."/"), true)["version"];
        return ($curr == $run) ? true : false;
    }
}

/**
 * Prüft daten vom Arkadmin-Server
 *
 * @param $key
 * @return bool
 */
function check_server_json_bool($key) {
    global $webserver;
    if(!check_server()) {
        return false;
    }
    else {
        $bool = json_decode(file_get_contents("http://127.0.0.1:".$webserver['config']['port']."/"), true)[$key];
        return ($bool == "true") ? true : false;
    }
}

