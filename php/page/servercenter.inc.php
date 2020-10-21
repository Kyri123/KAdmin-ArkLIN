<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

if (!file_exists("remote/arkmanager/instances/".$url[2].".cfg")) {
   header("Location: /404");
   exit;
}

// Vars
$tpl_dir = 'app/template/core/serv/';
$tpl_dir_lists = 'app/template/lists/serv/main/';
$tpl_dir_all = 'app/template/all/';
$setsidebar = false; $resp_cluster = null;
$serv = new server($url[2]);
exec("ps ax | grep ".$serv->status()->pid, $checkpid); // Prüfe ob der Server Läuft
$serv->cluster_load();
$txt_alert = $site_name = $player = null;

$perm = "server/".$serv->name();
if(!$user->perm("$perm/show")) {
    header("Location: /401"); exit;
}

// server Killen
if(isset($url[5]) && $url[4] == "kill" && $user->perm("$perm/kill")) {
    $jobs->set($serv->name());
    if($jobs->shell("kill ".$serv->status()->pid)) {
        $resp = $alert->rd(111);
    } else {
        $resp = $alert->rd(3);
    }
}
elseif(isset($url[5]) && $url[4] == "kill") {
    $resp = $alert->rd(99);
}

//erstelle SteamAPI von OnlineSpieler
$pl_json = $helper->file_to_json('app/json/saves/pl_' . $serv->name() . '.players', false);
$arr_pl = array();
if (is_array($pl_json)) {
    for ($i = 0; $i < count($pl_json); $i++) {
        $arr_pl[] = $pl_json[$i]->steamID;
    }
}

//erstelle SteamAPI von OnlineSpieler
$pl_json = $helper->file_to_json('app/json/saves/pl_' . $serv->name() . '.players', false);
$arr_pl = array();
if (is_array($pl_json)) {
    for ($i = 0; $i < count($pl_json); $i++) {
        $arr_pl[] = $pl_json[$i]->steamID;
    }
}
//erstelle SteamAPI von Savegames
$player_json = $helper->file_to_json('app/json/saves/player_' . $serv->name() . '.json', false);
$arr_player = array();
if (is_array($player_json)) {
    for ($i = 0; $i < count($player_json); $i++) {
        $arr_player[] = $player_json[$i]->SteamId;
    }
}

$ifslave        = ($serv->cluster_type() == 0   && $serv->cluster_in());
$ifcadmin       = ($serv->cluster_admin()       && $ifslave             && $serv->cluster_in());
$ifckonfig      = ($serv->cluster_konfig()      && $ifslave             && $serv->cluster_in());
$ifwhitelist    = ($serv->cluster_whitelist()   && $ifslave             && $serv->cluster_in());
$ifcmods        = ($serv->cluster_mods()        && $ifslave             && $serv->cluster_in());

$servername     = $serv->cfg_read('ark_SessionName');
$qport          = $serv->cfg_read('ark_QueryPort');

//tpl
$tpl = new Template('main.htm', $tpl_dir);
$tpl->load();

$globa_json = json_decode(file_get_contents('app/json/serverinfo/'.$url[2].'.json'));

$url[3] = (isset($url[3])) ? $url[3] : "home";
$ssite = $url[3];
$dir = dirToArray("php/subpage/serv");
$exsists = false;
foreach ($dir as $k => $v) {
    if (!is_array($v)) {
        $sitename = str_replace(".inc.php", null, $v);
        $visit = null;
        if ($sitename == $url[3]) {
            include("php/subpage/serv/$v");
            $visit = "active";
            $exsists = true;
        }
        $tpl->r("__$sitename", $visit);
        $tpl->rif("___$sitename", $visit == null);
    }
}

if (!$exsists) {
    include('php/subpage/serv/home.inc.php');
    $tpl->r("__home", "active");
}
$tpl->r("__home", null);

if ($serv->cfg_read('ark_TotalConversionMod') == '') $tmod = '<b>{::lang::php::sc::notmod}</b>' ?? $tmod = '<b>'.$serv->cfg_read('ark_TotalConversionMod').'</b>';

$player_online = $serv->status()->aplayersarr;

// Spieler
if (is_array($player_online) && is_countable($player_online) && count($player_online) > 0 && $user->perm("$perm/show_players")) {
    for ($i = 0; $i < count($player_online); $i++) {
        $list_tpl = new Template('user.htm', 'app/template/lists/serv/main/');
        $list_tpl->load();

        // Hole Daten
        $fsteamid = null;
        foreach($steamapi_user as $k => $v) {
            if(isset($player_online[$i]["name"]) && $v["personaname"]) if($v["personaname"] == $player_online[$i]["name"]) {
                $fsteamid = $k;
                break;
            }
        }

        $found = true;
        if($fsteamid != null) {
            $query = "SELECT * FROM ArkAdmin_players WHERE `server`='".$serv->name()."' AND `SteamId`='".$fsteamid."'";
            $query = $mycon->query($query);

            if($query->numRows() > 0) {
                $row = $query->fetchArray();

                $img                = $steamapi_user[$fsteamid]["avatar"];
                $SteamId            = $fsteamid;
                $surl               = $steamapi_user[$fsteamid]["profileurl"];
                $steamname          = $steamapi_user[$fsteamid]["personaname"];
                $IG_level           = $row["Level"];
                $xp                 = $row["ExperiencePoints"];
                $SpielerID          = $row["id"];
                $FileUpdated        = $row["FileUpdated"];
                $TribeId            = $row["TribeId"];
                $TotalEngramPoints  = $row["TotalEngramPoints"];
                $TribeName          = $row["TribeName"];
                $IG_name            = $row["CharacterName"] == "" ? (!isset($player_online[$i]["name"]) ? "Unkown" : $player_online[$i]["name"]) : $row["CharacterName"];
            }
            else {
                $found = false;
            }
        }
        else {
            $found = false;
        }

        if(!$found) {
            $xp                     = 0;
            $SpielerID              = 0;
            $TotalEngramPoints      = 0;
            $SteamId                = $fsteamid;
            $img                    = "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/";
            $surl                   = "#unknown";
            $steamname              = $player_online[$i]["name"];
            $FileUpdated            = time();
            $TribeId                = 7;
            $TribeName              = null;
            $IG_name                = $player_online[$i]["name"];
            $IG_level               = 0;
        }

        $time = TimeCalc($player_online[$i]["time"], ($player_online[$i]["time"] > 3600 ? "h" : "m"), "disabled");

        $list_tpl->r('tribe', (($TribeName != null) ? $TribeName : '{::lang::php::sc::notribe}'));
        $list_tpl->r('IG:name', $IG_name);
        $list_tpl->r('IG:Level', $IG_level);
        $list_tpl->r('lastupdate', converttime($FileUpdated));
        $list_tpl->r('rnd', rndbit(10));
        $list_tpl->r('url', $surl);
        $list_tpl->r('img', $img);
        $list_tpl->r('steamname', $steamname);
        $list_tpl->r('rm_url', '/servercenter/' . $serv->name() . '/saves/remove/' . $SteamId . '.arkprofile');
        $list_tpl->r('EP', $xp);
        $list_tpl->r('SpielerID', $SpielerID);
        $list_tpl->r('TEP', $TotalEngramPoints);
        $list_tpl->r('TID', $TribeId);
        $list_tpl->r('IG:online', round($time["int"], 2) . ' ' . $time["lang"]);
        $list_tpl->rif ('empty', true);

        $player .= $list_tpl->load_var();
    }
}
if ($player == null) {
    $list_tpl = new Template('user.htm', 'app/template/lists/serv/main/');
    $list_tpl->load();
    $list_tpl->r('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
    $list_tpl->rif ('empty', false);
    $list_tpl->r('IG:name', '{::lang::php::sc::no_player_online}');
    $list_tpl->r('IG:online', '');
    $player .= $list_tpl->load_var();
}

// Aktionen & Beschreibungen
$action_list = "<option value=\"\">{::lang::servercenter::jobs::section::jobs::task::option::default}</option>"; $i = 0;
$actioninfo_arr = [];
foreach ($action_opt as $key) {
    $action_list .= "<option value=\"$key\">{::lang::php::cfg::action::$key}</option>";

    // Aktionen Infos array für JS
    $actioninfo_arr[$key]['title'] = "{::lang::php::cfg::action::$key}";
    $actioninfo_arr[$key]['text'] = "{::lang::servercenter::infoaction::$key}";

    $i++;
}

// JS if & array
$json_para = $helper->file_to_json("app/json/panel/parameter.json");
$para_list = null;
$z = 0;
for ($i=0;$i<count($json_para);$i++) {
    $name = str_replace("--", null, $json_para[$i]["parameter"]);
    $para = new Template('parameter.htm', 'app/template/core/serv/');
    $para->load();
    
    $t0 = $json_para[$i]["type"] == 0;
    $t1 = ($json_para[$i]["type"] == 0) ? false : true;
        
    $para->r("name", str_replace("--", null, $json_para[$i]["parameter"]));
    $para->r("parameter", $json_para[$i]["parameter"]);
    $para->r("i", $z);
    $para->rif("type0", $t0);
    $para->rif("type1", $t1);

    if($json_para[$i]["type"] == 1) $z++;
    $para_list .= $para->load_var();
}

$l = strlen($servername); $lmax = 25;
if ($l > $lmax) {
    $servername = substr($servername, 0 , $lmax) . " ...";
}

$mapbg = file_exists('app/dist/img/backgrounds/' . $serv->cfg_read('serverMap') . '.jpg') ? '/app/dist/img/backgrounds/' . $serv->cfg_read('serverMap') . '.jpg' : '/app/dist/img/backgrounds/bg.jpg';

$tpl->r('action_list', $action_list);
$tpl->r('para_list', $para_list);
$tpl->r('clustername', $serv->cluster_name());
$tpl->r('cfg', $url[2]);
$tpl->r('servername', $servername);
$tpl->r('global_IP', $ip);
$tpl->r('con_url', $connect = str_replace($serv->cfg_read("ark_Port"), $serv->cfg_read("ark_QueryPort"), $serv->status()->connect));
$tpl->r('arkservers', $globa_json->ARKServers);
$tpl->r('QPort', $qport);
$tpl->r('max_player', $serv->cfg_read('ark_MaxPlayers'));
$tpl->r('map_str', $serv->cfg_read('serverMap'));
$tpl->r('tmod', $tmod);
$tpl->r('last_backup', 'Deaktiviert');
$tpl->r('url_site', 'http://'.$_SERVER['SERVER_NAME']);
$tpl->r('panel', $panel);
$tpl->r('resp', $resp);
$tpl->r('playerlist', $player);
$tpl->r ('rcon_meld', $alert->rd(305, 3));
$tpl->r ('cluster_meld', $resp_cluster);
$tpl->r ('installed_int', $serv->isinstalled() == "TRUE" ? 1 : 0);
$tpl->r ('exp_int', intval($user->expert()));
$tpl->r ('timestamp', time());
$tpl->r ('lang_arr', json_encode($actioninfo_arr));
$tpl->r ('bg_img', $mapbg);
$tpl->rif ('rcon', $serv->check_rcon());
$tpl->rif ('ifin', $serv->cluster_in());
$tpl->rif ('ifcadmin', $ifcadmin);
$tpl->rif ('ifckonfig', $ifckonfig);
$tpl->rif ('ifcmods', $ifcmods);
$tpl->rif ('ifslave', $ifslave);
$tpl->rif ('modsupport', $serv->mod_support());
$tpl->r("typestr", ($serv->cluster_in()) ? $clustertype[$serv->cluster_type()] : null);

//teste state
$onlinestate = false;
if ($serv->statecode() == 2) $onlinestate = true;
$tpl->rif ("ifonline", $onlinestate);
$tpl->rif ('expert', $user->expert());
$tpl->r('joinurl', $connect);
// lade in TPL
$pageicon = "<i class=\"fa fa-server\" aria-hidden=\"true\"></i>";
$content = $tpl->load_var();
$running = false;
foreach ($checkpid as $item) if(strpos($item, $serv->name())) $running = true;
if($running && $user->perm("$perm/kill")) $btns .= '
        <a href="/servercenter/'.$serv->name().'/'.$url[3].'/kill/'.$serv->status()->pid.'" class="btn btn-outline-danger btn-icon-split rounded-0" 
        data-toggle="popover_action" title="" data-content="{::lang::servercenter::kill_text}" data-original-title="{::lang::servercenter::kill_titel}">
            <span class="icon">
                <i class="fas fa-power-off"></i>
            </span>
        </a>
';