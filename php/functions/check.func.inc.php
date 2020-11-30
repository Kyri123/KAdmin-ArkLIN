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
    $header = @get_headers("http://127.0.0.1:".$webserver['config']['port']."/data");
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
    global $webserver, $KUTIL;
    if(!check_server()) {
        return false;
    }
    else {
        $json_string = file_get_contents("http://127.0.0.1:".$webserver['config']['port']."/data");
        $string = html_entity_decode(trim(utf8_encode($json_string)));
        $string = str_replace("\n", null, $string);

        $curr = (file_exists(__ADIR__."/arkadmin_server/data/version.txt")) ? trim($KUTIL->fileGetContents(__ADIR__."/arkadmin_server/data/version.txt")) : "curr_not_found";
        $run = json_decode($string, true)["version"];
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
    global $webserver, $KUTIL;
    if(!check_server()) {
        return false;
    }
    else {
        $json_string = file_get_contents("http://127.0.0.1:".$webserver['config']['port']."/data");
        $string = html_entity_decode(trim(utf8_encode($json_string)));
        $string = str_replace("\n", null, $string);

        // wandel Informationen in Array
        $bool = json_decode($string, true)[$key];
        return ($bool == "true") ? true : false;
    }
}

