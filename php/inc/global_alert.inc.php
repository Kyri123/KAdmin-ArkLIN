<?php

// check is Webhelper active
if ($helper->gethelperdiff() > 60) {
    $alert->code = 4;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}

// check is Webhelper newest versio
if(!check_webhelper()) {
    $alert->code = 31;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}

// check is Webhelper newest versio
if(!check_server_run()) {
    $alert->code = 34;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}

// check is PHP version
if(PHP_VERSION_ID < 70300) {
    $alert->code = 303;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}

//prüfe ob IE
if (isie()) {
    $alert->code = 200;
    $alert->overwrite_style = 3;
    $g_alert .= $alert->re();
    $g_alert_bool = true;
}
?>