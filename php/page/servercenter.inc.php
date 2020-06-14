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
$tpl_dir = 'app/template/serv/';
$tpl_dir_lists = 'app/template/serv/page/list/';
$tpl_dir_all = 'app/template/all/';
$setsidebar = false;
$serv = new server($url[2]);
$serv->cluster_load();

$ifslave = false; if ($serv->cluster_type() == 0 && $serv->cluster_in()) $ifslave = true;
$ifcadmin = false; if ($serv->cluster_admin() && $ifslave && $serv->cluster_in()) $ifcadmin = true;
$ifckonfig = false; if ($serv->cluster_konfig() && $ifslave && $serv->cluster_in()) $ifckonfig = true;
$ifcmods = false; if ($serv->cluster_mods() && $ifslave && $serv->cluster_in()) $ifcmods = true;

if (!$serv->cluster_in()) {
    $ifcadmin = false;
    $ifckonfig = false;
    $ifcmods = false;
}

$servername = $serv->cfg_read('ark_SessionName');
$qport = $serv->cfg_read('ark_QueryPort');

//tpl
$tpl = new Template('main.htm', $tpl_dir);
$tpl->load();

$globa_json = json_decode(file_get_contents('app/json/serverinfo/'.$url[2].'.json'));

$ssite = $url[3];
$dir = dirToArray("php/subpage/serv");
$exsists = false;
foreach ($dir as $k => $v) {
    if (!is_array($v)) {
        $sitename = str_replace(".inc.php", null, $v);
        $visit = null;
        if ($sitename == $url[3]) {
            include("php/subpage/serv/$v");
            $visit = "aa_active";
            $exsists = true;
        }
        $tpl->r("_$sitename", $visit);
    }
}

if (!$exsists) {
    include('php/subpage/serv/home.inc.php');
    $tpl->r("__home", "aa_active");
}
$tpl->r("__home", null);

if ($serv->cfg_read('ark_TotalConversionMod') == '') $tmod = '<b>{::lang::php::sc::notmod}</b>' ?? $tmod = '<b>'.$serv->cfg_read('ark_TotalConversionMod').'</b>';

//danger list
$danger_listitem = new Template('list_warn_err.htm', $tpl_dir_lists);
$danger_listitem->load();

if ($globa_json->error_count > 0) {
    for ($i=0;$i<count($globa_json->error);$i++) {

        if (strpos($globa_json->error[$i], 'is requested but not installed') !== false) {

            $modid = strstr($globa_json->error[$i], '\'');
            $modid = str_replace('\' to install this mod.', null, $modid);
            $modid = str_replace('\'arkmanager installmod ', null, $modid);

            $json = $steamapi->getmod($modid);

            $type = '{::lang::php::sc::danger::notinstalled} <a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank"><b>'.$json->response->publishedfiledetails[0]->title.'</b></a>';
        } else {
            $type = '{::lang::php::sc::danger::err_notdef}';
        }
        $globa_json->error[$i] = str_replace('] ', null, $globa_json->error[0]);

        $danger_listitem->rif("default_warn", false);
        $danger_listitem->rif("default_err", false);
        $danger_listitem->rif("list", true);
        $danger_listitem->r("type", $type);
        $danger_listitem->r("txt", $globa_json->error[$i]);
        $danger_list .= $danger_listitem->load_var();
    }
}
else {
    // erstelle standart meldung wenn keine "error_count" == 0 ... < 1
    $danger_listitem->rif("default_warn", false);
    $danger_listitem->rif("default_err", true);
    $danger_listitem->rif("list", false);
    $danger_list = $danger_listitem->load_var();
}

//warning list
$warning_listitem = new Template('list_warn_err.htm', $tpl_dir_lists);
$warning_listitem->load();

if ($globa_json->warning_count > 0) {
    for ($i=0;$i<count($globa_json->warning);$i++) {

        if (strpos($globa_json->warning[$i], 'Your ARK server exec could not be found.') !== false) {
            $type = '{::lang::php::sc::warn::serv_notinstalled}';
        } else {
            $type = '{::lang::php::sc::warn::err_notdef}';
        }
        $globa_json->warning[$i] = str_replace('] ', null, $globa_json->warning[$i]);

        $warning_listitem->rif("default_warn", false);
        $warning_listitem->rif("default_err", false);
        $warning_listitem->rif("list", true);
        $warning_listitem->r("type", $type);
        $warning_listitem->r("txt", $globa_json->warning[$i]);
        $warning_list = $warning_listitem->load_var();
    }
}
else {
    // erstelle standart meldung wenn keine "warning_count" == 0 ... < 1
    $warning_listitem->rif("default_warn", true);
    $warning_listitem->rif("default_err", false);
    $warning_listitem->rif("list", false);
    $warning_list = $warning_listitem->load_var();
}

$savedir = $serv->dir_save();
$pl_json = $helper->file_to_json('app/json/saves/pl_' . $serv->name() . '.players');
$player_json = $helper->file_to_json('app/json/saves/player_' . $serv->name() . '.json', false);
$player_json_ar = $helper->file_to_json('app/json/saves/player_' . $serv->name() . '.json', true);
$tribe_json = $helper->file_to_json('app/json/saves/tribes_' . $serv->name() . '.json', false);
$tribe_json_ar = $helper->file_to_json('app/json/saves/tribes_' . $serv->name() . '.json', true);
if (!is_array($player_json)) $player_json = array();
if (!is_array($tribe_json)) $tribe_json = array();
$jhelper = new player_json_helper();

$player = null;
$c_pl = 0;

// Spieler
if (is_array($pl_json) && $pl_json[0]["name"] != "NO") {
    for ($i = 0; $i < count($pl_json); $i++) {
        $list_tpl = new Template('list_user.htm', 'app/template/serv/page/list/');
        $list_tpl->load();

        for ($y=0;$y<count($player_json);$y++) {
            if ($pl_json[$i]["steamID"] == $player_json_ar[$y]["SteamId"]) {
                $z = $y;
                break;
            }
        }

        $pl = $jhelper->player($player_json, $z);

        if (is_array($tribe_json)) {
            for ($z = 0; $z < count($tribe_json); $z++) {
                $member = $tribe_json[$z]->Members;

                if (in_array($pl->CharacterName, $member)) {
                    $tribe = $jhelper->tribe($tribe_json, $z);
                    $list_tpl->r('tribe', $tribe->Name);
                    break;
                }
            }
        }

        $list_tpl->r('tribe', '{::lang::php::sc::notribe}');

        if ($pl->Level > 1000) $pl->Level = 0;
        if ($pl->TribeId == 7) $pl->TribeId = null;

        $list_tpl->r('IG:name', $pl->CharacterName);
        $list_tpl->r('IG:Level', $pl->Level);
        $list_tpl->r('lastupdate', converttime($pl->FileUpdated));
        $list_tpl->r('rnd', rndbit(10));
        $list_tpl->r('url', $steamapi->getsteamprofile_class($pl->SteamId)->profileurl);
        $list_tpl->r('img', $steamapi->getsteamprofile_class($pl->SteamId)->avatar);
        $list_tpl->r('steamname', $steamapi->getsteamprofile_class($pl->SteamId)->personaname);

        $list_tpl->r('rm_url', '/servercenter/' . $serv->name() . '/saves/remove/' . $pl->SteamId . '.arkprofile');

        $list_tpl->r('EP', round($pl->ExperiencePoints, '2'));
        $list_tpl->r('SpielerID', $pl->Id);
        $list_tpl->r('TEP', $pl->TotalEngramPoints);
        $list_tpl->r('TID', $pl->TribeId);
        $list_tpl->rif ('empty', true);

        $player .= $list_tpl->load_var();
    }
}
if ($player == null) {
    $list_tpl = new Template('list_user.htm', 'app/template/serv/page/list/');
    $list_tpl->load();
    $list_tpl->r('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
    $list_tpl->rif ('empty', false);
    $list_tpl->r('IG:name', '{::lang::php::sc::no_player_online}');
    $player .= $list_tpl->load_var();
}



$action_list = "<option value=\"\">Aktion wählen...</option>"; $i = 0;
foreach ($action_opt as $key) {
    $array[$key] = array();
    $action_list .= "<option value=\"$key\">".$action_str[$i]."</option>";
    $i++;
}

// JS if & array
$json_para = $helper->file_to_json("app/json/panel/parameter.json");
$para_list = null;
$z = 0;
for ($i=0;$i<count($json_para);$i++) {
    $name = str_replace("--", null, $json_para[$i]["parameter"]);
    $para = new Template('parameter.htm', 'app/template/serv/');
    $para->load();
    
    $t0 = ($json_para[$i]["type"] == 0) ? true : false;
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

if ($txt_alert != null) $resp .= meld_full('info', nl2br($txt_alert), 'Cluster: Alpha Version', null);


$tpl->r('danger_resp', $danger_list);
$tpl->r('warning_resp', $warning_list);

$tpl->r('action_list', $action_list);
$tpl->r('para_list', $para_list);
$tpl->r('clustername', $serv->cluster_name());
$tpl->r('cfg', $url[2]);
$tpl->r('servername', $servername);
$tpl->r('global_IP', $ip);
$tpl->r('con_url', $connect = $globa_json->connect);
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
$tpl->rif ('ifin', $serv->cluster_in());
$tpl->rif ('ifcadmin', $ifcadmin);
$tpl->rif ('ifckonfig', $ifckonfig);
$tpl->rif ('ifcmods', $ifcmods);
$tpl->rif ('ifslave', $ifslave);
$tpl->r("typestr", $clustertype[$serv->cluster_type()]);

//teste state
$onlinestate = false;
if ($serv->statecode() == 2) $onlinestate = true;
$tpl->rif ("ifonline", $onlinestate);
$tpl->r('joinurl', $serv->status()->connect);
// lade in TPL
$pageicon = "<i class=\"fa fa-server\" aria-hidden=\"true\"></i>";
$content = $tpl->load_var();
$btns .= '
    <div class="d-sm-inline-block ">
        <a href="#" class="btn btn-warning btn-icon-split rounded-0" data-toggle="modal" data-target="#warning_modal">
            <span class="icon text-white-50">
                <i class="fas fa-exclamation-circle"></i>
            </span>
            <span class="text">'.$globa_json->warning_count.'</span>
        </a>
        <a href="#" class="btn btn-danger btn-icon-split rounded-0" data-toggle="modal" data-target="#danger_modal">
            <span class="icon text-white-50">
                <i class="fas fa-exclamation-triangle"></i>
            </span>
            <span class="text">'.$globa_json->error_count.'</span>
        </a>
    </div>
';


?>