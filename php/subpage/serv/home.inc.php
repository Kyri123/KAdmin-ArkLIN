<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

$pagename           = '{::lang::php::sc::page::home::pagename}';
$page_tpl           = new Template('home.htm', __ADIR__.'/app/template/sub/serv/');
$urltop             = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfgRead('ark_SessionName').'</a></li>';
$urltop             .= '<li class="breadcrumb-item">{::lang::php::sc::page::home::urltop}</li>';
$adminlist_admin    = $userlist_admin = null;

$page_tpl->load();
$page_tpl->r('cfg' ,$serv->name());
$page_tpl->r('SESSION_USERNAME' ,$user->read("username"));

// Erstelle Dateien wenn die nicht exsistieren
$cheatfile      = $KUTIL->path($serv->dirSavegames(true)."/AllowedCheaterSteamIDs.txt")["/path"];
$whitelistfile  = $KUTIL->path($serv->dirMain()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt")["/path"];
$playerjson     = $helper->fileToJson($KUTIL->path(__ADIR__.'/app/json/steamapi/profile_allg.json')["/path"]);
$playerjs       = isset($playerjson["response"]["players"]) ? $playerjson["response"]["players"] : [];
$count          = is_countable($playerjs) ? count($playerjs): false;

$KUTIL->createFile($cheatfile);
$KUTIL->createFile($whitelistfile);

// Administrator hinzufügen
if (isset($_POST["addadmin"]) && $session_user->perm("$perm/home/admin_send")) {
    $id             = $_POST["id"];
    $cheatcontent   = $KUTIL->fileGetContents($cheatfile);

    // SteamID bzw Input prüfen
    if(is_numeric($id) && $id > 700000000) {
        if(!strpos($cheatcontent, $id)) {
            for ($ix=0;$ix<$count;$ix++) if($id == $playerjs[$ix]["steamid"]) {$i = $ix; break;}
            $content    = $KUTIL->fileGetContents($cheatfile)."\n$id";

            if ($KUTIL->filePutContents($cheatfile, $content)) {
                $alert->code = 100;
                $alert->r("name", isset($playerjs[$i]["personaname"]) ? strval($playerjs[$i]["personaname"]) : $id);
                $alert->overwrite_text = "{::lang::php::sc::page::home::add_admin}";
                $resp   .= $alert->re();
            } else {
                $resp   .= $alert->rd(1);
            }
        }
        else {
            $resp       .= $alert->rd(5);
        }
    } else {
        $resp           .= $alert->rd(2);
    }
}
elseif(isset($_POST["addadmin"])) {
    $resp .= $alert->rd(99);
}

// Entfernte von Adminliste
if (isset($_POST["rm"]) && $session_user->perm("$perm/home/admin_send")) {
    $id             = $_POST["stid"];
    $content        = $KUTIL->fileGetContents($cheatfile);
    // Prüfe ob die ID exsistent ist
    if (substr_count($content, $id) > 0) {
        $content    = str_replace($id, null, $content);
        if ($KUTIL->filePutContents($cheatfile, $content)) {
            $resp   .= $alert->rd(101);
        } else {
            // Melde: Lese/Schreib Fehler
            $resp   .= $alert->rd(1);
        }
    }
}
elseif(isset($_POST["rm"])) {
    $resp .= $alert->rd(99);
}

$serv->cfgRead('arkserverroot');
$savedir        = $serv->dirSavegames();
$player_json    = $helper->fileToJson(__ADIR__.'/app/json/saves/player_'.$serv->name().'.json', false);
$tribe_json     = $helper->fileToJson(__ADIR__.'/app/json/saves/tribes_'.$serv->name().'.json', false);

if (!is_array($player_json))    $player_json    = array();
if (!is_array($tribe_json))     $tribe_json     = array();

// Liste Admins auf
if ($serv->isInstalled() && $session_user->perm("$perm/home/admin_show")) {
    if (@file_exists($cheatfile)) {
        $file   = file($cheatfile);
        $arr    = [];

        if (is_array($file)) for ($i = 0; $i < count($file); $i++) {
            $file[$i]   = trim($file[$i]);
            if(
                $file[$i] != "0" &&
                $file[$i] != "" &&
                $file[$i] != null
            ) $arr[]    = $file[$i];
        }

        if(is_countable($arr) && is_array($arr) && count($arr) > 0) {
            for ($i=0;$i<count($arr);$i++) {
                $list_tpl   = new Template('user_admin.htm', __ADIR__.'/app/template/lists/serv/home/');
                $list_tpl->load();

                $query      = "SELECT * FROM ArkAdmin_players WHERE `server`=? AND `SteamId`=?";
                $query      = $mycon->query($query, $serv->name(), $arr[$i]);

                if($query->numRows() > 0) {
                    $row = $query->fetchArray();
                    $list_tpl->r("stname", isset($steamapi_user[$arr[$i]]["personaname"]) ? $steamapi_user[$arr[$i]]["personaname"] : "#");
                    $list_tpl->r("igname", $row["CharacterName"]);
                }
                else {
                    $list_tpl->r("stname", isset($steamapi_user[$arr[$i]]["personaname"]) ? $steamapi_user[$arr[$i]]["personaname"] : "#");
                    $list_tpl->r("igname", "{::lang::allg::default::noadmin}");
                }

                $list_tpl->r("stid", $arr[$i]);
                $list_tpl->r("url", isset($steamapi_user[$arr[$i]]["profileurl"]) ? $steamapi_user[$arr[$i]]["profileurl"] : "#");
                $list_tpl->r("cfg", $serv->name());
                $list_tpl->r("rndb", rndbit(25));
                $list_tpl->r("img", isset($steamapi_user[$arr[$i]]["avatarmedium"]) ? $steamapi_user[$arr[$i]]["avatarmedium"] : "#");
                $list_tpl->rif("hidebtn", false);

                $adminlist_admin .= $list_tpl->load_var();
            }
        }
    }
    if($adminlist_admin == null) {
        $list_tpl           = new Template('whitelist.htm', __ADIR__.'/app/template/lists/serv/jquery/');

        $list_tpl->load();
        $list_tpl->r("sid", 0);
        $list_tpl->r("name", "{::lang::allg::default::noadmin}");
        $list_tpl->r("cfg", $serv->name());
        $list_tpl->r("rndb", rndbit(25));
        $list_tpl->r("img", "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
        $list_tpl->rif("hidebtn", true);

        $adminlist_admin    .= $list_tpl->load_var();
    }

    $query = "SELECT * FROM ArkAdmin_players WHERE `server`=?";
    $query = $mycon->query($query, $serv->name());

    if($query->numRows() > 0) foreach ($query->fetchAll() as $item) if (!in_array($item["SteamId"], $arr)) $userlist_admin .= "<option value='". $item["SteamId"] ."'>". (isset($steamapi_user[$item["SteamId"]]["personaname"]) ? $steamapi_user[$item["SteamId"]]["personaname"] : "???") ." - ".$item["CharacterName"]."</option>";
} 

// Meldung wenn Clusterseitig Admin & Whitelist deaktiviert ist
if ($ifcadmin)      $resp_cluster   .= $alert->rd(300);
if ($ifwhitelist)   $resp_cluster   .= $alert->rd(304);

$lchatactive = true;
// Meldung wenn wegen fehlender Flagge Whitelist deaktiviert ist
if (!(
    $serv->cfgKeyExists("arkflag_servergamelog") &&
    $serv->cfgKeyExists("arkflag_servergamelogincludetribelogs") &&
    $serv->cfgKeyExists("arkflag_ServerRCONOutputTribeLogs") &&
    $serv->cfgKeyExists("arkflag_logs")
)) {
    $resp_cluster   .= $alert->rd(308, 3);
    $lchatactive    = false;
}

$alert->code                = 202;
$alert->overwrite_style     = 3;
$alert->overwrite_mb        = 0;
$white_alert                = $alert->re();
$page_tpl->rif ("installed", $serv->isInstalled(true));
$page_tpl->rif ('ifwhitelist', $ifwhitelist);
$page_tpl->rif ('rcon', $serv->checkRcon());
$page_tpl->rif ('lchatactive', $lchatactive);
$page_tpl->rif ('whiteactive', $serv->cfgKeyExists("arkflag_exclusivejoin"));
$page_tpl->r('lchatlog', $serv->dirSavegames(true, true).'/Logs/ServerPanel.log');
$page_tpl->r('rconlog', __ADIR__."/app/json/saves/rconlog_".$serv->name().'.txt');
$page_tpl->r('whiteactive_meld', $white_alert);
$page_tpl->r("userlist_admin", $userlist_admin);
$page_tpl->r("adminlist_admin", $adminlist_admin);
$page_tpl->r("pick_whitelist", $serv->cfgKeyExists("exclusivejoin"));
$panel  = $page_tpl->load_var();