<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

$langfrom = $langto = array();

/**
 * Lade Sprachdateien aus der XML
 *
 * @param $langfile
 */
function load_xml($langfile) {
    global $helper;
    // mache XML zu einem Array
    $xml = simplexml_load_file($langfile);
    $xml = $helper->stringToJson($helper->jsonToString($xml), true);

    //splite array um ein im Template einzubinden
    read_xml($xml, "::lang");
}

/**
 * Verarbeitet die Sprachdateien
 *
 * @param $array
 * @param $key
 */
function read_xml($array, $key) {
    global $langfrom, $langto;

    foreach ($array as $k => $v) {
        $mkey = $key."::$k";
        if (is_array($v)) {
            read_xml($v, $mkey);
        } else {
            array_push($langfrom, "{".$mkey."}");
            array_push($langto, nl2br($v));
        }
    }
}

$lang_pick = isset($_COOKIE["lang"]) ? $_COOKIE["lang"] : "de_de";
$langfile = __ADIR__."/app/lang/".$lang_pick."/";
if (!file_exists($langfile)) $langfile = __ADIR__."/app/lang/de_de/";

// Lade Sprachdateien
$arr = scandir($langfile);
foreach ($arr as $item) {
    if (
        pathinfo($langfile . $item, PATHINFO_EXTENSION) == "xml" &&
        $lang_pick != "debug"
    ) {
        if(
            $item != "." &&
            $item != ".."
        ) load_xml($langfile . $item);
    }
}






