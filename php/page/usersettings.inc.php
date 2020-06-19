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
$tpl_dir = 'app/template/usersettings/';
$resp = null; $logout = false;
$pagename = "{::lang::php::home::pagename}";
$urltop = "<li class=\"breadcrumb-item\">$pagename</li>";
$path = "app/json/user/".md5($_SESSION["id"]).".json";

// Speichern (Benutzerdaten)
if(isset($_POST["saveuser"])) {
    //EMail
    if($_POST["email"] != $user->email()) {
        if($user->write("email", $_POST["email"])) {
            $logout = true;
        }
        else {
            $alert->code = 3;
            $resp = $alert->re();
        }
    }

    //Username
    if($_POST["username"] != $user->name()) {
        if($user->write("username", $_POST["username"])) {
            $logout = true;
        }
        else {
            $alert->code = 3;
            $resp = $alert->re();
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
                $resp = $alert->re();
            }
        }
        else {
            $alert->code = 27;
            $resp = $alert->re();
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
    $json["expert"] = (isset($_POST["json"]["expert"])) ? 1 : 0;
    if ($helper->savejson_create($json, $path)) {
        $alert->code = 102;
        $resp = $alert->re();
    } else {
        $alert->code = 1;
        $resp = $alert->re();
    }
}

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

//rplacer
$tpl->r("resp", $resp);
$tpl->r("username", $user->name());
$tpl->r("email", $user->email());
$tpl->r("c_expert", ($user->expert()) ? "checked" : null);

// sendet alles an Index
$content = $tpl->load_var();
$pageicon = "<i class=\"fas fa-tachometer-alt\" aria-hidden=\"true\"></i>";
?>