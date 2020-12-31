<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Vars
$tpl_dir    = __ADIR__.'/app/template/core/usersettings/';
$resp       = null; $logout = false;
$pagename   = "{::lang::usersettings::pagename}";
$urltop     = "<li class=\"breadcrumb-item\">$pagename</li>";
$path       = $KUTIL->path(__ADIR__."/app/json/user/".md5($_SESSION["id"]).".json")["/path"];

// Speichern (Benutzerdaten)
if(isset($_POST["saveuser"])) {
    //EMail
    if($_POST["email"] != $session_user->read("email")) {
        if($session_user->write("email", $_POST["email"])) {
            header("Location: $ROOT/logout");
            exit;
        }
        else {
            $resp       .= $alert->rd(3);
        }
    }

    //Username
    if($_POST["username"] != $session_user->read("username")) {
        if($session_user->write("username", $_POST["username"])) {
            header("Location: $ROOT/logout");
            exit;
        }
        else {
            $resp       .= $alert->rd(3);
        }
    }

    //EMail
    if($_POST["pw1"] != "" && $_POST["pw2"] != "") {
        $pw1    = md5($_POST["pw1"]);
        $pw2    = md5($_POST["pw2"]);
        if($pw2 == $pw1) {
            if($session_user->write("password", $pw1)) {
                header("Location: $ROOT/logout");
                exit;
            }
            else {
                $resp   .= $alert->rd(3);
            }
        }
        else {
            $resp       .= $alert->rd(27);
        }
    }
}

// Speichern (Benutzer Einstellungen)
if(isset($_POST["savepanel"])) {
    $json               = $helper->fileToJson($path, true);
    $json["expert"]     = isset($_POST["json"]["expert"]) && $session_user->perm("usersettings/expert") ? 1 : 0;
    $json["konfig"]     = isset($_POST["json"]["konfig"]) ? 1 : 0;
    $resp .= $alert->rd($helper->saveFile($json, $path) ? 102 : 1);
}

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

//rplacer
$tpl->r("resp", $resp);
$tpl->r("username", $session_user->read("username"));
$tpl->r("email", $session_user->read("email"));
$tpl->r("c_expert", ($session_user->expert()) ? "checked" : null);
$tpl->r("c_konfig", ($session_user->show_mode("konfig")) ? "checked" : null);

// sendet alles an Index
$content    = $tpl->load_var();
$pageicon   = "<i class=\"fas fa-tachometer-alt\" aria-hidden=\"true\"></i>";