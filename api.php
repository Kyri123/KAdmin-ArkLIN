<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

define("__ADIR__", __DIR__);

// hide errors
$stime = microtime(true);
include(__ADIR__.'/php/inc/config.inc.php');
include(__ADIR__.'/php/class/helper.class.inc.php');
$helper = new helper();
$ckonfig = $helper->file_to_json(__ADIR__.'/php/inc/custom_konfig.json', true);
$site_name = $content = null;

// Deaktiviere Error anzeige
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

//check install
if (!file_exists(__ADIR__."/app/check/subdone")) {
    header('Location: /install.php');
    exit;
}

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$pagename = $pageimg = $titlename = $sidebar = $btns = $urltop = $g_alert = $pageicon = $tpl = null;
$setsidebar = $g_alert_bool = false;

// Connent to MYSQL
include(__ADIR__.'/php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

// Include functions
include(__ADIR__.'/php/functions/allg.func.inc.php');
include(__ADIR__.'/php/functions/check.func.inc.php');
include(__ADIR__.'/php/functions/modify.func.inc.php');
include(__ADIR__.'/php/functions/traffic.func.inc.php');
include(__ADIR__.'/php/functions/util.func.inc.php');

// include classes
include(__ADIR__.'/php/class/xml_helper.class.php');
include(__ADIR__.'/php/class/Template.class.inc.php');
include(__ADIR__.'/php/class/alert.class.inc.php');
include(__ADIR__.'/php/class/rcon.class.inc.php');
include(__ADIR__.'/php/class/savefile_reader.class.inc.php');
include(__ADIR__.'/php/class/user.class.inc.php');
include(__ADIR__.'/php/class/steamAPI.class.inc.php');
include(__ADIR__.'/php/class/server.class.inc.php');
include(__ADIR__.'/php/class/jobs.class.inc.php');

// include inz
include(__ADIR__.'/php/inc/template_preinz.inc.php');

// API

// Prüfe auf berechtigung der API abfrage
$API_path           = __ADIR__."/php/inc/api.json";
$API_array          = $helper->file_to_json($API_path);
$API_active         = boolval($API_array["active"]);
$API_key            = $API_array["key"];

$API_VALIDE_REQUEST = array(
    "allserver",
    "serverinfo",
    "statistiken"
);

if(!isset($_GET["request"]) && !isset($_GET["key"]) || isset($_GET["key"]) && $_GET["key"] != $API_key) {
    echo '{"permissions": false}';
}
else {
    $API_RESPONSE = array();
    $API_REQUEST = $_GET["request"];
    if(!in_array($API_REQUEST, $API_VALIDE_REQUEST)) {
        echo '{"request": "not found"}';
    }
    else {
        /* LEGENDE
         * ! = Pflichtfeld
         * ? = Optional
         * Pflicht/Opt | Var | Option | Beschreibung
         */

        /* REQUEST: allserver
         *
         * Gibt aus Welche Server es gibt
         * ! | opt | full / lite | Wieviele Infos sollen die je jeweiligen Server enthalten: full mit alle dazugehörigen Infos | lite nur Namen
         */
        if($API_REQUEST == "allserver") {
            $opt = isset($_GET["opt"]) ? ($_GET["opt"] == "full" ? "full" : "lite") : "lite";

            // Lite
            if($opt == "lite") {
                $ALL_PATH = __ADIR__."/app/json/serverinfo/all.json";
                if(file_exists($ALL_PATH)) {
                    $ALL_ARRAY = $helper->file_to_json($ALL_PATH);

                    foreach ($ALL_ARRAY["cfgs"] as $ITEM) {
                        $RESPONSE["response"]["server"][] = str_replace(".cfg", null, $ITEM);
                    }

                    echo json_encode($RESPONSE);
                }
                else {
                    echo '{"request": false}';
                }
            }

            // Full
            elseif($opt == "full") {
                $ALL_PATH = __ADIR__."/app/json/serverinfo/all.json";
                if(file_exists($ALL_PATH)) {
                    $ALL_ARRAY = $helper->file_to_json($ALL_PATH);

                    foreach ($ALL_ARRAY["cfgs"] as $ITEM) {
                        $servername = str_replace(".cfg", null, $ITEM);
                        $SERVER_PATH = __ADIR__."/app/json/serverinfo/$servername.json";
                        if(file_exists($SERVER_PATH)) {
                            $SERVER_ARRAY = $helper->file_to_json($SERVER_PATH);

                            if(isset($SERVER_ARRAY["warning_count"]))   unset($SERVER_ARRAY["warning_count"]);
                            if(isset($SERVER_ARRAY["error_count"]))     unset($SERVER_ARRAY["error_count"]);
                            if(isset($SERVER_ARRAY["error"]))           unset($SERVER_ARRAY["error"]);
                            if(isset($SERVER_ARRAY["warning"]))         unset($SERVER_ARRAY["warning"]);

                            $server = new server($servername);

                            $SERVER_ARRAY["mods"]       = $server->cfg_read("ark_GameModIds");
                            $SERVER_ARRAY["statecode"]  = $server->statecode();
                            $SERVER_ARRAY["server_ip"]  = "$ip:".$server->cfg_read("ark_QueryPort");

                            $RESPONSE["response"]["server"][$servername] = $SERVER_ARRAY;
                        }
                    }

                    echo json_encode($RESPONSE);
                }
                else {
                    echo '{"request": false}';
                }
            }
            else {
                echo '{"request": false}';
            }
        }

        /* REQUEST: serverinfo
         *
         * Gibt aus Welche Server es gibt
         * ! | server | xyz | Servernamen
         */
        if($API_REQUEST == "serverinfo") {
            $SERVER_NAME = isset($_GET["server"]) ? $_GET["server"] : "unknown";
            if(file_exists(__ADIR__."/remote/arkmanager/instances/$SERVER_NAME.cfg")) {
                $SERVER_PATH = __ADIR__."/app/json/serverinfo/$SERVER_NAME.json";
                if(file_exists($SERVER_PATH)) {
                    $SERVER_ARRAY = $helper->file_to_json($SERVER_PATH);

                    if(isset($SERVER_ARRAY["warning_count"]))   unset($SERVER_ARRAY["warning_count"]);
                    if(isset($SERVER_ARRAY["error_count"]))     unset($SERVER_ARRAY["error_count"]);
                    if(isset($SERVER_ARRAY["error"]))           unset($SERVER_ARRAY["error"]);
                    if(isset($SERVER_ARRAY["warning"]))         unset($SERVER_ARRAY["warning"]);

                    $server = new server($SERVER_NAME);

                    $SERVER_ARRAY["mods"]       = $server->cfg_read("ark_GameModIds");
                    $SERVER_ARRAY["statecode"]  = $server->statecode();
                    $SERVER_ARRAY["server_ip"]  = "$ip:".$server->cfg_read("ark_QueryPort");

                    $RESPONSE["response"]["serverinfo"] = $SERVER_ARRAY;
                    echo json_encode($RESPONSE);
                }
                else {
                    echo '{"request": false}';
                }
            }
            else {
                echo '{"request": false}';
            }
        }

        /* REQUEST: statistiken
         *
         * Gibt die Statisken aus
         * ! | server | servername | Name des Servers
         * ? | max | int !=< 1 | Wieviele Datensätze sollen ausgegeben werden
         * ? | order | DESC / ASC | Wie soll geordnet werden
         */
        if($API_REQUEST == "statistiken") {
            if(isset($_GET["server"])) {
                $SERVER_NAME = $_GET["server"];
                if(file_exists(__ADIR__."/remote/arkmanager/instances/$SERVER_NAME.cfg")) {
                    $MAX = isset($_GET["max"]) ? intval($_GET["max"]) : 100;
                    if($MAX < 1) $MAX = 1;
                    $ORDER = isset($_GET["order"]) ? ($_GET["max"] == "ASC" ? "ASC" : "DESC") : "DESC";

                    $query = "SELECT * FROM ArkAdmin_statistiken WHERE `server` = '$SERVER_NAME' ORDER BY `time` $ORDER LIMIT $MAX";
                    $mycon->query($query);
                    if($mycon->numRows() > 0) {
                        $RESPONSE["response"]["statistiken"] = $mycon->fetchAll();
                        echo json_encode($RESPONSE);
                    }
                    else {
                        echo '{"request": false}';
                    }
                }
                else {
                    echo '{"request": false}';
                }
            }
            else {
                echo '{"request": false}';
            }
        }
    }
}


//close mysql
$mycon->close();