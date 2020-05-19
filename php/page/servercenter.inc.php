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

if ($serv->cfg_read('ark_TotalConversionMod') == '') $tmod = '<b>Keine</b>' ?? $tmod = '<b>'.$serv->cfg_read('ark_TotalConversionMod').'</b>';

//danger list
$danger_listitem = '<li class="list-group-item list-group-item-mod">
        <div class="row p-0">
            <div class="col-12">
                <i class="text-danger fas fa-exclamation-triangle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                <div style="margin-left: 60px;">{::lang::php::sc::danger::nodanger_found}<br><span class="font-weight-light" style="font-size: 11px;">{::lang::php::sc::danger::all_ok}</span></div>
            </div>
        </div>
    </li>
';
if ($globa_json->error_count > 0) {
    $danger_listitem = null;
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

        $danger_listitem .= '<li class="list-group-item list-group-item-mod">
                <div class="row p-0">
                    <div class="col-12">
                        <i class="text-danger fas fa-exclamation-triangle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                        <div style="margin-left: 60px;">'.$type.'<br><span class="font-weight-light" style="font-size: 11px;">'.$globa_json->error[$i].'</span></div>
                    </div>
                </div>
            </li>
        ';
    }
}
//warning list
$warning_listitem = '<li class="list-group-item list-group-item-mod">
        <div class="row p-0">
            <div class="col-12">
                <i class="text-warning fas fa-exclamation-circle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                <div style="margin-left: 60px;">{::lang::php::sc::warn::nowarn_found}<br><span class="font-weight-light" style="font-size: 11px;">{::lang::php::sc::warn::all_ok}</span></div>
            </div>
        </div>
    </li>
';
if ($globa_json->warning_count > 0) {
    $warning_listitem = null;
    for ($i=0;$i<count($globa_json->warning);$i++) {

        if (strpos($globa_json->warning[$i], 'Your ARK server exec could not be found.') !== false) {
            $type = '{::lang::php::sc::warn::serv_notinstalled}';
        } else {
            $type = '{::lang::php::sc::warn::err_notdef}';
        }
        $globa_json->warning[$i] = str_replace('] ', null, $globa_json->warning[$i]);

        $warning_listitem .= '<li class="list-group-item list-group-item-mod">
                <div class="row p-0">
                    <div class="col-12">
                        <i class="text-warning fas fa-exclamation-circle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                        <div style="margin-left: 60px;">'.$type.'<br><span class="font-weight-light" style="font-size: 11px;">'.$globa_json->warning[$i].'</span></div>
                    </div>
                </div>
            </li>
        ';
    }
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
                $tribe = $jhelper->tribe($tribe_json, $z);
                if ($tribe->Id == $pl->TribeId) {
                    $list_tpl->r('tribe', $tribe->Name);
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


// JS if & array
$opt_str = array();

$action_list = "<option value=\"\">Aktion wählen...</option>"; $i = 0;
foreach ($action_opt as $key) {
    $array[$key] = array();
    $action_list .= "<option value=\"$key\">".$action_str[$i]."</option>";
    $i++;
}

$json_para = $helper->file_to_json("app/json/panel/parameter.json");
$para_list = null;
for ($i=0;$i<count($json_para);$i++) {
    $opt_str[count($opt_str)] = "'".$json_para[$i]["id_js"]."'";
    $name = str_replace("--", null, $json_para[$i]["parameter"]);
    $para_list .= '
        <div class="icheck-primary mb-3 col-md-6">
              <input type="checkbox" name="para[]" value="'.$json_para[$i]["parameter"].'" id="'.$name.'" disabled>
              <label for="'.$name.'">
                    '.$json_para[$i]["parameter"].' <!--{::lang::php::sc::actions::'.$name.'}-->
              </label>
        </div>
    ';
    if (count($json_para[$i]["for"]) > 0) {
        foreach ($json_para[$i]["for"] as $key) {
            $array[$key][count($array[$key])] = $json_para[$i]["id_js"];
        }
    }
}

$jsfi = null;
foreach ($array as $key => $value) {
    if (is_countable($array[$key])) {
        if (count($array[$key]) > 0) {
            $jsfi .= "if (action === '$key') {\n";
            for ($i=0;$i<count($array[$key]);$i++) {
                $jsfi .= "  $('".$array[$key][$i]."').attr('disabled', false);\n";
            }
            $jsfi .= "}\n";
        }
    }
}

$l = strlen($servername); $lmax = 25;
if ($l > $lmax) {
    $servername = substr($servername, 0 , $lmax) . " ...";
}

if ($txt_alert != null) $resp .= meld_full('info', nl2br($txt_alert), 'Cluster: Alpha Version', null);


$tpl->r('danger_resp', $danger_listitem);
$tpl->r('warning_resp', $warning_listitem);

$tpl->r('jsif', $jsfi);
$tpl->r('js_array', implode(",", $opt_str));
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