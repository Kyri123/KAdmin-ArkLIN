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

function check_arkmanager() {
    $cmd = "arkmanager";
    $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
    return !empty($return);
}

function isie() {
    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
        return true;
    }
    elseif (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false)) {
        return true;
    } else {
        return false;
    }
    return false;
}

function check_webhelper() {
    $curr = (file_exists("java/version.txt")) ? trim(file_get_contents("java/version.txt")) : "curr_not_found";
    $run = (file_exists("java/version.txt")) ? trim(file_get_contents("java/run_version.txt")) : "run_not_found";
    return ($curr == $run) ? true : false;
}

?>