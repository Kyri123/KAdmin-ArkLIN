<?php

// Vars
$tpl_dir = 'tpl/userpanel/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "Benutzer";
$urltop = '<li class="breadcrumb-item">Benutzer</li>';

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();
// Code hinzufügen
if(isset($_POST["add"])) {
    $code = rndbit(10);
    $query = "INSERT INTO `ArkAdmin_reg_code` (`code`, `used`, `time`) VALUES ('".$code."', '0', '0')";
    if($mycon->query($query)) {
        $resp = meld(
            'success',
            '<div class="input-group m"><input type="text" class="form-control rounded-0" readonly="true" value="'.$code.'" id="'.$code.'"><span class="input-group-append"><button onclick="copythis(\''.$code.'\')" class="btn btn-primary btn-flat"><i class="fas fa-copy" aria-hidden="true"></i></button></span></div>',
            'Code hinzugefügt:',
            null
        );
    }
    else {
        $resp = meld('danger', 'Konnte Code nicht hinzugefügen.', 'Fehler!', null);
    }
}

// Code löschen
if(isset($url[3]) && $url[2] == "rmcode") {
    $id = $url[3];
    $query = "DELETE FROM `ArkAdmin_reg_code` WHERE (`id`='".$id."')";
    if($mycon->query($query)) {
        $resp = meld('success', 'Code Gelöscht', 'Erfolgreich!', null);
    }
    else {
        $resp = meld('danger', 'Konnte Code nicht löschen.', 'Fehler!', null);
    }
}

// Benutzer löschen
if(isset($_POST["del"])) {
    $id = $_POST["userid"];
    $query = "DELETE FROM `ArkAdmin_users` WHERE (`id`='".$id."')";
    if($mycon->query($query)) {
        $resp = meld('success', 'Benutzer Gelöscht', 'Erfolgreich!', null);
    }
    else {
        $resp = meld('danger', 'Konnte Benutzer nicht löschen.', 'Fehler!', null);
    }
}

// Benutzer (ent-)bannen
if(isset($url[4]) && $url[2] == "tban") {
    $uid = $url[3];
    $set = $url[4];
    if($set == 0) {
        $to = "Entbannt";
    }
    else {
        $to = "Gebannt";
    }
    $query = "UPDATE `ArkAdmin_users` SET `ban`='".$set."' WHERE (`id`='".$uid."')";
    if($mycon->query($query)) {
        $resp = meld('success', 'Benutzer ['.$uid.'] ' . $to, 'Erfolgreich!', null);
    }
    else {
        $resp = meld('danger', 'Konnte Benutzer nicht geändert.', 'Fehler!', null);
    }
}

// Benutzer Liste
$query = 'SELECT * FROM `ArkAdmin_users`';
$mycon->query($query);
$userarray = $mycon->fetchAll();
$dir = dirToArray('remote/arkmanager/instances/');
$userlist = null; $userlist_modal = null;
for($i=1;$i<count($userarray);$i++) {
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

    if($ban < 1) {
        $list->replif("ifban", false);
    }
    else {
        $list->replif("ifban", true);
    }

    $list->repl("regdate", converttime($registerdate));
    $list->repl("lastlogin", converttime($lastlogin));
    $list->repl("email", $email);
    $list->repl("uid", $id);
    $list->repl("username", $username);

    $list->replif("ifmodal", false);
    $userlist .= $list->loadin();

    // Modal
    $list = new Template("list.htm", $tpl_dir);
    $list->load();

    $list->repl("username", $username);
    $list->repl("uid", $id);

    $list->replif("ifmodal", true);
    $userlist_modal .= $list->loadin();
}

// Count Email
$query = 'SELECT * FROM `ArkAdmin_reg_code` WHERE `used` = \'0\'';
$mycon->query($query);
$codearray = $mycon->fetchAll();
$list_codes = null;
if(count($codearray)>0) {
    for($i=0;$i<count($codearray);$i++) {
        $list = new Template("list_codes.htm", $tpl_dir);
        $list->load();
        $list->repl("id", $codearray[$i]["id"]);
        $list->repl("code", $codearray[$i]["code"]);
        $list->replif("ifemtpy", false);

        $list_codes .= $list->loadin();
    }
}
else {
    $list = new Template("list_codes.htm", $tpl_dir);
    $list->load();
    $list->repl("code", "Kein Code gefunden");
    $list->replif("ifemtpy", true);

    $list_codes .= $list->loadin();
}

// lade in TPL
$tpl->repl("list", $userlist);
$tpl->repl("list_modal", $userlist_modal);
$tpl->repl("list_codes", $list_codes);
$tpl->repl("resp", $resp);
$content = $tpl->loadin();
$btns = '<a href="#" class="btn btn-success btn-icon-split" data-toggle="modal" data-target="#addserver">
            <span class="icon text-white-50">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </span>
            <span class="text">Reg-Codes</span>
        </a>';
?>