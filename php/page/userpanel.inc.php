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
$tpl_dir = 'app/template/userpanel/';
$tpl_dir_all = 'app/template/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "{::lang::php::userpanel::pagename}";
$urltop = "<li class=\"breadcrumb-item\">$pagename</li>";
$user = new userclass();

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();
// Code hinzufügen
if (isset($_POST["add"])) {
    $code = rndbit(10);
    $query = "INSERT INTO `ArkAdmin_reg_code` (`code`, `used`, `time`) VALUES ('".$code."', '0', '0')";
    if ($mycon->query($query)) {
        $alert->code = 100;
        $alert->overwrite_text = '<div class="input-group m"><input type="text" class="form-control rounded-0" readonly="true" value="'.$code.'" id="'.$code.'"><span class="input-group-append"><button onclick="copythis(\''.$code.'\')" class="btn btn-primary btn-flat"><i class="fas fa-copy" aria-hidden="true"></i></button></span></div>';
        $resp = $alert->re();
    } else {
        $alert->code = 3;
        $resp = $alert->re();
    }
}

// Code löschen
if (isset($url[3]) && $url[2] == "rmcode") {
    $id = $url[3];
    $query = "DELETE FROM `ArkAdmin_reg_code` WHERE (`id`='".$id."')";
    if ($mycon->query($query)) {
        $alert->code = 101;
        $alert->overwrite_text = '{::lang::php::userpanel::removed_code}';
        $resp = $alert->re();
    } else {
        $alert->code = 3;
        $resp = $alert->re();
    }
}

// Benutzer löschen
if (isset($_POST["del"])) {
    $id = $_POST["userid"];
    $user->setid($id);
    $tpl->r("del_username", $user->name());
    $query = "DELETE FROM `ArkAdmin_users` WHERE (`id`='".$id."')";
    if ($mycon->query($query)) {
        $alert->code = 101;
        $alert->overwrite_text = '{::lang::php::userpanel::removed_user}';
        $resp = $alert->re();
    } else {
        $alert->code = 3;
        $resp = $alert->re();
    }
}

// Benutzer (ent-)bannen
if (isset($url[4]) && $url[2] == "tban") {
    $uid = $url[3];
    $set = $url[4];
    if ($set == 0) {
        $to = "{::lang::php::userpanel::banned}";
    } else {
        $to = "{::lang::php::userpanel::notbanned}";
    }
    $user->setid($uid);
    $tpl->r("ban_username", $user->name());
    $tpl->r("ban_uid", $uid);
    $tpl->r("ban_to", $to);
    $query = "UPDATE `ArkAdmin_users` SET `ban`='".$set."' WHERE (`id`='".$uid."')";
    if ($mycon->query($query)) {
        $alert->code = 101;
        $alert->overwrite_text = '{::lang::php::userpanel::changed_ban}';
        $resp = $alert->re();
     } else {
        $alert->code = 3;
        $resp = $alert->re();
    }
}

// Benutzer Liste
$query = 'SELECT * FROM `ArkAdmin_users`';
$mycon->query($query);
$userarray = $mycon->fetchAll();
$dir = dirToArray('remote/arkmanager/instances/');
$userlist = null; $userlist_modal = null;
for ($i=1;$i<count($userarray);$i++) {
    $id = $userarray[$i]["id"];
    $username = $userarray[$i]["username"];
    $email = $userarray[$i]["email"];
    $lastlogin = $userarray[$i]["lastlogin"];
    $registerdate = $userarray[$i]["registerdate"];
    $rang = $userarray[$i]["rang"];
    $ban = $userarray[$i]["ban"];



    // Kein Modal
    $list = new Template("list.htm", $tpl_dir);
    $list->load();

    if ($ban < 1) {
        $list->rif ("ifban", false);
    } else {
        $list->rif ("ifban", true);
    }

    $list->r("regdate", converttime($registerdate));
    $list->r("lastlogin", converttime($lastlogin));
    $list->r("email", $email);
    $list->r("uid", $id);
    $list->r("username", $username);

    $list->rif ("ifmodal", false);
    $userlist .= $list->load_var();

    // Modal
    $list = new Template("list.htm", $tpl_dir);
    $list->load();

    $list->r("username", $username);
    $list->r("uid", $id);

    $list->rif ("ifmodal", true);
    $userlist_modal .= $list->load_var();
}

// Count Email
$query = 'SELECT * FROM `ArkAdmin_reg_code` WHERE `used` = \'0\'';
$mycon->query($query);
$codearray = $mycon->fetchAll();
$list_codes = null;
if (count($codearray)>0) {
    for ($i=0;$i<count($codearray);$i++) {
        $list = new Template("list_codes.htm", $tpl_dir);
        $list->load();
        $list->r("id", $codearray[$i]["id"]);
        $list->r("code", $codearray[$i]["code"]);
        $list->rif ("ifemtpy", false);

        $list_codes .= $list->load_var();
    }
} else {
    $list = new Template("list_codes.htm", $tpl_dir);
    $list->load();
    $list->r("code", "{::lang::php::userpanel::nocodefound}");
    $list->rif ("ifemtpy", true);

    $list_codes .= $list->load_var();
}

// lade in TPL
$tpl->r("list", $userlist);
$tpl->r("list_modal", $userlist_modal);
$tpl->r("list_codes", $list_codes);
$tpl->r("resp", $resp);
$pageicon = "<i class=\"fa fa-users\" aria-hidden=\"true\"></i>";
$content = $tpl->load_var();
$btns = '<a href="#" class="btn btn-success btn-icon-split rounded-0" data-toggle="modal" data-target="#addserver">
            <span class="icon text-white-50">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </span>
            <span class="text">{::lang::php::userpanel::btn-regcode}</span>
        </a>';
?>