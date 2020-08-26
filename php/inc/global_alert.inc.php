<?php

echo "_________________________________________________1_1_____________".(microtime(true) - $stime)."<br>";
if(check_server()) {
    // check is ArkAdmin-Server newest version
    if(!check_server_json_bool("db_connect")) {
        $alert->code = 35;
        $alert->overwrite_style = 3;
        $g_alert .= $alert->re();
        $g_alert_bool = true;
    }
    // check is ArkAdmin-Server newest version
    if(!check_webhelper()) {
        $alert->code = 31;
        $alert->overwrite_style = 3;
        $g_alert .= $alert->re();
        $g_alert_bool = true;
    }
    // check is ArkAdmin-Server active
    if ($helper->gethelperdiff() > 60) {
        $alert->code = 4;
        $alert->overwrite_style = 3;
        $g_alert .= $alert->re();
        $g_alert_bool = true;
    }
}
else {
    $alert->code = 34;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}

echo "_________________________________________________1_2_____________".(microtime(true) - $stime)."<br>";
// check is PHP version
if(PHP_VERSION_ID < 70300) {
    $alert->code = 303;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}

echo "_________________________________________________1_3_____________".(microtime(true) - $stime)."<br>";
//prüfe ob IE
if (isie()) {
    $alert->code = 200;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}
echo "_________________________________________________1_4_____________".(microtime(true) - $stime)."<br>";
?>