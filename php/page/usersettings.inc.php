<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Vars
$tpl_dir = __ADIR__.'/app/template/core/usersettings/';
$resp = null; $logout = false;
$pagename = "{::lang::usersettings::pagename}";
$urltop = "<li class=\"breadcrumb-item\">$pagename</li>";
$path = __ADIR__."/app/json/user/".md5($_SESSION["id"]).".json";

// Speichern (Benutzerdaten)
if(isset($_POST["saveuser"])) {
    //EMail
    if($_POST["email"] != $user->read("email")) {
        if($user->write("email", $_POST["email"])) {
            $logout = true;
        }
        else {
            $alert->code = 3;
            $resp .= $alert->re();
        }
    }

    //Username
    if($_POST["username"] != $user->read("username")) {
        if($user->write("username", $_POST["username"])) {
            $logout = true;
        }
        else {
            $alert->code = 3;
            $resp .= $alert->re();
        }
    }

    //EMail
    if($_POST["pw1"] != "" && $_POST["pw2"] != "") {
        $pw1 = md5($_POST["pw1"]);
        $pw2 = md5($_POST["pw2"]);
        if($pw2 == $pw1) {
            if($user->write("password", $pw1)) {
                $logout = true;
            }
            else {
                $alert->code = 3;
                $resp .= $alert->re();
            }
        }
        else {
            $alert->code = 27;
            $resp .= $alert->re();
        }
    }

    //logout
    if($logout) {
        header('Location: /logout');
        exit;
    }
}

// Speichern (Benutzer Einstellungen)
if(isset($_POST["savepanel"])) {
    $json = $helper->file_to_json($path, true);
    $json["expert"] = (isset($_POST["json"]["expert"]) && $session_user->perm("usersettings/expert")) ? 1 : 0;
    $json["konfig"] = (isset($_POST["json"]["konfig"])) ? 1 : 0;
    if ($helper->saveFile($json, $path)) {
        $alert->code = 102;
        $resp .= $alert->re();
    } else {
        $alert->code = 1;
        $resp .= $alert->re();
    }
}

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

//rplacer
$tpl->r("resp", $resp);
$tpl->r("username", $user->read("username"));
$tpl->r("email", $user->read("email"));
$tpl->r("c_expert", ($user->expert()) ? "checked" : null);
$tpl->r("c_konfig", ($user->show_mode("konfig")) ? "checked" : null);

// sendet alles an Index
$content = $tpl->load_var();
$pageicon = "<i class=\"fas fa-tachometer-alt\" aria-hidden=\"true\"></i>";
