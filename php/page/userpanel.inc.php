<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if(!$session_user->perm("userpanel/show")) {
    header("Location: /401"); exit;
}

// Vars
$tpl_dir        = __ADIR__.'/app/template/core/userpanel/';
$tpl_dir_all    = __ADIR__.'/app/template/all/';
$setsidebar     = false;
$cfglist        = null;
$pagename       = "{::lang::php::userpanel::pagename}";
$urltop         = "<li class=\"breadcrumb-item\">$pagename</li>";
$kuser          = new userclass();

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

//Benutzergruppen setzten
if(isset($_POST["editgroups"]) && $session_user->perm("all/is_admin")) {
    $groups = isset($_POST["ids"]) ? json_encode($_POST["ids"]) : "[]";
    $users = new userclass($_POST["userid"]);
    if($users->write("rang", $groups)) {
        $resp = $alert->rd(102);
    }
    else {
        $resp = $alert->rd(3);
    }
}
elseif(isset($_POST["editgroups"]))  {
    $resp = $alert->rd(99);
}

// Code hinzufügen
if (isset($_POST["add"]) && $session_user->perm("userpanel/create_code")) {
    $code = rndbit(10);
    $rank = $_POST["rank"];

    if(($rank == 1 && $session_user->perm("all/is_admin")) || $rank == 0) {
        if(is_numeric($rank)) {
            $query = "INSERT INTO `ArkAdmin_reg_code` (`code`, `used`, `time`) VALUES ('$code', '0', '$rank')";
            if ($mycon->query($query)) {
                $alert->code = 100;
                $alert->overwrite_text = '<div class="input-group m"><input type="text" class="form-control rounded-0" readonly="true" value="'.$code.'" id="'.$code.'"><span class="input-group-append"><button onclick="copythis(\''.$code.'\')" class="btn btn-primary btn-flat"><i class="fas fa-copy" aria-hidden="true"></i></button></span></div>';
                $resp = $alert->re();
            } else {
                $resp = $alert->rd(3);
            }
        }
        else {
            $resp = $alert->rd(2);
        }
    }
    else  {
        $resp = $alert->rd(99);
    }
}
elseif(isset($_POST["add"]))  {
    $resp = $alert->rd(99);
}

// Code löschen
if (isset($url[3]) && $url[2] == "rmcode" && $session_user->perm("userpanel/delete_code")) {
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
elseif (isset($url[3]) && $url[2] == "rmcode") {
    $resp = $alert->rd(99);
}

// Benutzer löschen
if (isset($_POST["del"]) && $session_user->perm("userpanel/delete_user")) {
    $id = $_POST["userid"];
    $kuser->setid($id);
    $tpl->r("del_username", $kuser->read("username"));
    $query = "DELETE FROM `ArkAdmin_users` WHERE (`id`='".$id."')";
    if ($mycon->query($query)) {
        if(file_exists(__ADIR__."/app/json/user/".md5($id).".permissions.json")) unlink(__ADIR__."/app/json/user/".md5($id).".permissions.json");
        if(file_exists(__ADIR__."/app/json/user/".md5($id).".json")) unlink(__ADIR__."/app/json/user/".md5($id).".json");
        $mycon->query("DELETE FROM `ArkAdmin_user_cookies` WHERE (`userid`='".$id."')");
        $alert->code = 101;
        $alert->overwrite_text = '{::lang::php::userpanel::removed_user}';
        $resp = $alert->re();
    } else {
        $alert->code = 3;
        $resp = $alert->re();
    }
}
elseif (isset($_POST["del"])) {
    $resp = $alert->rd(99);
}

// Benutzer (ent-)bannen
if (isset($url[4]) && $url[2] == "tban" && $session_user->perm("userpanel/ban_user")) {
    $uid = $url[3];
    $set = $url[4];
    if (!$set == 0) {
        $to = "{::lang::php::userpanel::banned}";
    } else {
        $to = "{::lang::php::userpanel::notbanned}";
    }
    $kuser->setid($uid);
    $tpl->r("ban_username", $kuser->read("username"));
    $tpl->r("ban_uid", $uid);
    $tpl->r("ban_to", $to);
    $query = "UPDATE `ArkAdmin_users` SET `ban`='".$set."' WHERE (`id`='".$uid."')";
    if ($mycon->query($query)) {
        $alert->code = 102;
        $alert->overwrite_text = '{::lang::php::userpanel::changed_ban}';
        $resp = $alert->re();
     } else {
        $alert->code = 3;
        $resp = $alert->re();
    }
}
elseif (isset($url[4]) && $url[2] == "tban") {
    $resp = $alert->rd(99);
}

// Benutzer Liste
$query      = 'SELECT * FROM `ArkAdmin_users`';
$dir        = dirToArray(__ADIR__.'/remote/arkmanager/instances/');
$userarray  = $mycon->query($query)->fetchAll();
$userlist   = null; $userlist_modal = null;
for ($i=1;$i<count($userarray);$i++) {
    // Setzte User in der Klasse
    $kuser->setid($userarray[$i]["id"]);

    // Vars
    $user_permissions   = null;
    $id                 = $kuser->read("id");
    $username           = $kuser->read("username");
    $email              = $kuser->read("email");
    $lastlogin          = $kuser->read("lastlogin");
    $registerdate       = $kuser->read("registerdate");
    $rang               = $kuser->read("rang");
    $ban                = $kuser->read("ban");

    // Kein Modal
    $list = new Template("list.htm", $tpl_dir);
    $list->load();


    $GROUP_SQUERY   = "SELECT * FROM `ArkAdmin_user_group` ORDER BY `id`";
    $QUERY          = $mycon->query($GROUP_SQUERY);
    $ADDLIST        = null;
    if($QUERY->numRows() > 0) {
        $GROUP_ARR = $QUERY->fetchAll();
        foreach ($GROUP_ARR as $KEY => $ITEM) {
            $ID         = $i.md5($ITEM["id"]);
            $SEL        = count($kuser->group_array) > 0 ? (in_array($ITEM["id"], $kuser->group_array) ? "checked" : "") : "";
            $ADDLIST    .= "
                <div class=\"icheck-primary\">
                    <input type=\"checkbox\" id=\"$ID\" name=\"ids[]\" value=\"$ITEM[id]\" $SEL>
                    <label for=\"$ID\">
                        $ITEM[name]
                    </label>
                </div>
            ";
        }
    }

    // Schreibe infos in das Template
    $list->r("addlist", $ADDLIST);
    $list->r("regdate", converttime($registerdate));
    $list->r("lastlogin", converttime($lastlogin));
    $list->r("email", $email);
    $list->r("uid", $id);
    $list->r("rank", "<span class='text-".((!$kuser->perm("allg/is_admin")) ? "success" : "danger")."'>{::lang::php::userpanel::".((!$kuser->perm("allg/is_admin")) ? "user" : "admin")."}</span>");
    $list->r("username", $username);

    // prüfe ob der User gebant ist, deaktivere Modal & prüfe ob die ID man selbst ist
    $list->rif ("ifban", boolval($ban));
    $list->rif ("ifmodal", false);
    $list->rif ("self", ($id == $_SESSION["id"] || $kuser->perm("all/is_admin")));

    // Schreibe Template in var
    $userlist .= $list->load_var();

    // Modal
    $list = new Template("list.htm", $tpl_dir);
    $list->load();

    $list->r("username", $username);
    $list->r("uid", $id);

    $list->rif ("ifmodal", true);
    $userlist_modal .= $list->load_var();
}

// Liste Codes auf
$list_codes = null;
if($session_user->perm("userpanel/show_codes")) {
    $query = 'SELECT * FROM `ArkAdmin_reg_code` WHERE `used` = \'0\'';
    $mycon->query($query);
    $codearray = $mycon->fetchAll();
    if (count($codearray)>0) {
        for ($i=0;$i<count($codearray);$i++) {
            $list = new Template("codes.htm", $tpl_dir);
            $list->load();
            $list->r("rank", "<span class='text-".(($codearray[$i]["time"] == 0) ? "success" : "danger")."'>{::lang::php::userpanel::".(($codearray[$i]["time"] == 0) ? "user" : "admin")."}</span>");
            $list->r("code", $codearray[$i]["code"]);
            $list->r("id", $codearray[$i]["id"]);
            $list->rif ("ifemtpy", false);

            $list_codes .= $list->load_var();
        }
    } else {
        $list = new Template("codes.htm", $tpl_dir);
        $list->load();
        $list->r("code", "{::lang::php::userpanel::nocodefound}");
        $list->rif ("ifemtpy", true);

        $list_codes .= $list->load_var();
    }
}

// lade in TPL
$tpl->r("list", $userlist);
$tpl->r("list_modal", $userlist_modal);
$tpl->r("list_codes", $list_codes);
$tpl->r("resp", $resp);
$pageicon = "<i class=\"fa fa-users\" aria-hidden=\"true\"></i>";
$content = $tpl->load_var();
if($session_user->perm("userpanel/create_code")) $btns = '<a href="#" class="btn btn-outline-success btn-icon-split rounded-0" data-toggle="modal" data-target="#addserver">
            <span class="icon">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </span>
            <span class="text">{::lang::php::userpanel::btn-regcode}</span>
        </a>';
