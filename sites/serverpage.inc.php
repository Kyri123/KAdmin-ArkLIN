<?php

if(!file_exists("remote/arkmanager/instances/".$url[2].".cfg")) {
   header("Location: /");
   exit;
}

// Vars
$tpl_dir = 'tpl/serv/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$serv = new server($url[2]);
$servername = $serv->cfg_read('ark_SessionName');
$qport = $serv->cfg_read('ark_QueryPort');

//tpl
$tpl = new Template('main.htm', $tpl_dir);
$tpl->load();

$globa_json = json_decode(file_get_contents('data/serv/'.$url[2].'.json'));

if($url[3] == 'mods') {
    $pagename = 'ServerCenter - Modifikationen';
    include('sites/inc/serv/mods.inc.php');
}
elseif($url[3] == 'konfig') {
    $pagename = 'ServerCenter - Konfigurationen';
    include('sites/inc/serv/konfig.inc.php');
}
elseif($url[3] == 'logs') {
    $pagename = 'ServerCenter - Logs';
    include('sites/inc/serv/logs.inc.php');
}
elseif($url[3] == 'saves') {
    $pagename = 'ServerCenter - Savegames';
    include('sites/inc/serv/saves.inc.php');
}
elseif($url[3] == 'jobs') {
    $pagename = 'ServerCenter - Aufgaben';
    include('sites/inc/serv/jobs.inc.php');
}
elseif($url[3] == 'backups') {
    $pagename = 'ServerCenter - Backups';
    include('sites/inc/serv/backups.inc.php');
}
else {
    $pagename = 'ServerCenter - Startseite';
    include('sites/inc/serv/home.inc.php');
}

if($serv->cfg_read('ark_TotalConversionMod') == '') $tmod = '<b>Keine</b>' ?? $tmod = '<b>'.$serv->cfg_read('ark_TotalConversionMod').'</b>';

//danger list
$danger_listitem = '<li class="list-group-item list-group-item-mod">
                        <div class="row p-0">
                            <div class="col-12">
                                <i class="text-danger fas fa-exclamation-triangle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                                <div style="margin-left: 60px;">Keine Fehler gefunden!<br><span class="font-weight-light" style="font-size: 11px;">Alles ist OK!</span></div>
                            </div>
                        </div>
                    </li>';
if($globa_json->error_count > 0) {
    $danger_listitem = null;
    for($i=0;$i<count($globa_json->error);$i++) {

        if(strpos($globa_json->error[$i], 'is requested but not installed') !== false) {

            $modid = strstr($globa_json->error[$i], '\'');
            $modid = str_replace('\' to install this mod.', null, $modid);
            $modid = str_replace('\'arkmanager installmod ', null, $modid);

            $json = $steamapi->getmod($modid);

            $type = 'Nicht Installiert: <a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank"><b>'.$json->response->publishedfiledetails[0]->title.'</b></a>';
        }
        else {
            $type = 'Fehler nicht Definiert';
        }
        $globa_json->error[$i] = str_replace('] ', null, $globa_json->error[0]);

        $danger_listitem .= '<li class="list-group-item list-group-item-mod">
                        <div class="row p-0">
                            <div class="col-12">
                                <i class="text-danger fas fa-exclamation-triangle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                                <div style="margin-left: 60px;">'.$type.'<br><span class="font-weight-light" style="font-size: 11px;">'.$globa_json->error[$i].'</span></div>
                            </div>
                        </div>
                    </li>';
    }
}
//warning list
$warning_listitem = '<li class="list-group-item list-group-item-mod">
                        <div class="row p-0">
                            <div class="col-12">
                                <i class="text-warning fas fa-exclamation-circle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                                <div style="margin-left: 60px;">Keine Warnung gefunden<br><span class="font-weight-light" style="font-size: 11px;">Alles ist OK!</span></div>
                            </div>
                        </div>
                    </li>';
if($globa_json->warning_count > 0) {
    $warning_listitem = null;
    for($i=0;$i<count($globa_json->warning);$i++) {

        if(strpos($globa_json->warning[$i], 'Your ARK server exec could not be found.') !== false) {
            $type = 'Server ist nicht Installiert (Exe konnte nicht gefunden werden)';
        }
        else {
            $type = 'Fehler nicht Definiert';
        }
        $globa_json->warning[$i] = str_replace('] ', null, $globa_json->warning[$i]);

        $warning_listitem .= '<li class="list-group-item list-group-item-mod">
                        <div class="row p-0">
                            <div class="col-12">
                                <i class="text-warning fas fa-exclamation-circle rounded -align-left position-absolute" style="font-size: 45px"  height="50" width="50"></i>
                                <div style="margin-left: 60px;">'.$type.'<br><span class="font-weight-light" style="font-size: 11px;">'.$globa_json->warning[$i].'</span></div>
                            </div>
                        </div>
                    </li>';
    }
}

$savedir = $serv->get_save_dir();
$player_json = $helper->file_to_json('data/saves/player_' . $serv->show_name() . '.json');
$tribe_json = $helper->file_to_json('data/saves/tribes_' . $serv->show_name() . '.json');
if (!is_array($player_json)) $player_json = array();
if (!is_array($tribe_json)) $tribe_json = array();
$jhelper = new player_json_helper();


$player = null;
$c_pl = 0;
// Spieler
if (is_array($player_json)) {
    for ($i = 0; $i < count($player_json); $i++) {
        $list_tpl = new Template('list_user.htm', 'tpl/serv/sites/list/');
        $list_tpl->load();

        $pl = $jhelper->player($player_json, $i);

        if (is_array($tribe_json)) {
            for ($z = 0; $z < count($tribe_json); $z++) {
                $tribe = $jhelper->tribe($tribe_json, $z);
                if ($tribe->Id == $pl->TribeId) {
                    $list_tpl->repl('tribe', $tribe->Name);
                }
            }
        }
        $list_tpl->repl('tribe', '[Kein Stamm]');

        if ($pl->Level > 1000) $pl->Level = 0;
        if ($pl->TribeId == 7) $pl->TribeId = null;

        $list_tpl->repl('IG:name', $pl->CharacterName);
        $list_tpl->repl('IG:Level', $pl->Level);
        $list_tpl->repl('lastupdate', converttime($pl->FileUpdated));
        $list_tpl->repl('rnd', rndbit(10));
        $list_tpl->repl('url', $steamapi->getsteamprofile_class($pl->SteamId)->profileurl);
        $list_tpl->repl('img', $steamapi->getsteamprofile_class($pl->SteamId)->avatar);
        $list_tpl->repl('steamname', $steamapi->getsteamprofile_class($pl->SteamId)->personaname);

        $list_tpl->repl('rm_url', '/serverpage/' . $serv->show_name() . '/saves/remove/' . $pl->SteamId . '.arkprofile');

        $list_tpl->repl('EP', round($pl->ExperiencePoints, '2'));
        $list_tpl->repl('SpielerID', $pl->Id);
        $list_tpl->repl('TEP', $pl->TotalEngramPoints);
        $list_tpl->repl('TID', $pl->TribeId);
        $list_tpl->replif('empty', true);

        if(converttime($pl->FileUpdated) != "01.01.1970 01:00")$player .= $list_tpl->loadin();
        $c_pl++;
        break;
    }
}
if ($player == null) {
    $list_tpl = new Template('list_user.htm', 'tpl/serv/sites/list/');
    $list_tpl->load();
    $list_tpl->repl('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
    $list_tpl->replif('empty', false);
    $list_tpl->repl('IG:name', 'Niemand ist Online!');
    $player .= $list_tpl->loadin();
}


$tpl->repl('danger_resp', $danger_listitem);
$tpl->repl('warning_resp', $warning_listitem);

$tpl->repl('cfg', $url[2]);
$tpl->repl('servername', $servername);
$tpl->repl('global_IP', $ip);
$tpl->repl('con_url', $connect = $globa_json->connect);
$tpl->repl('arkservers', $globa_json->ARKServers);
$tpl->repl('QPort', $qport);
$tpl->repl('max_player', $serv->cfg_read('ark_MaxPlayers'));
$tpl->repl('map_str', $serv->cfg_read('serverMap'));
$tpl->repl('tmod', $tmod);
$tpl->repl('last_backup', 'Deaktiviert');
$tpl->repl('url_site', 'http://'.$_SERVER['SERVER_NAME']);
$tpl->repl('panel', $panel);
$tpl->repl('resp', $resp);
$tpl->repl('playerlist', $player);
// lade in TPL
$content = $tpl->loadin();
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