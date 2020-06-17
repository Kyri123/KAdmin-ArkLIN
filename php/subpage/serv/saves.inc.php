<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::mods::pagename}';
$page_tpl = new Template('saves.htm', 'app/template/serv/page/');
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::mods::pagename}</li>';
$jhelper = new player_json_helper();

if (isset($url[4]) && $url[4] == 'remove' && isset($url[5])) {

    //set vars
    $file_name = $url[5];
    $savedir = $serv->dir_save();

    // Wenn Spieler
    if (strpos($file_name, 'profile') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.arkprofile', null, $file_name);
        $file['name2'] = str_replace('.arkprofile', '.profilebak', $file_name);
        $file['name2'] = $savedir.'/'.$file['name2'];
        $del[0] = 0;
        $del[1] = 0;

        if (file_exists($file['name1'])) {
            if (unlink($file['name1']));
        }
        if (file_exists($file['name2'])) {
            if (unlink($file['name2']));
        }

        $path = 'app/json/saves/player_'.$serv->name().'.json';
        $json = $helper->file_to_json($path);

        for ($i=0;$i<count($json);$i++) {
            $pl = $jhelper->player($json, $i);
            if ($file['tname'] == $pl->SteamId) {
                unset($json[$i]);
                break;
            }
        }
        $json = array_values($json);
        if (file_put_contents($path, $helper->json_to_str($json))) {
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }


    // Wenn Stamm
    elseif (strpos($file_name, 'tribe') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.arktribe', null, $file_name);
        $file['name2'] = str_replace('.arktribe', '.tribebak', $file_name);
        $file['name2'] = $savedir.'/'.$file['name2'];
        $del[0] = 0;
        $del[1] = 0;

        print_r($file);

        if (file_exists($file['name1'])) {
            if (unlink($file['name1']));
        }
        if (file_exists($file['name2'])) {
            if (unlink($file['name2']));
        }
        print_r($del);

        $path = 'app/json/saves/tribes_'.$serv->name().'.json';
        $json = $helper->file_to_json($path);
        for ($i=0;$i<count($json);$i++) {
            $pl = $jhelper->tribe($json, $i);
            if ($file['tname'] == $pl->Id) {
                unset($json[$i]); break;
            }
        }
        $json = array_values($json);
        if (file_put_contents($path, $helper->json_to_str($json))) {
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }


    // Wenn Welt
    elseif (strpos($file_name, '.ark') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.ark', null, $file_name);
        if (unlink($file['name1'])) {
            $arr = dirToArray($serv->dir_save());
            for ($i=0;$i<count($arr);$i++) {
                if (strpos($arr[$i], $file['tname']) !== false) {
                    if (file_exists($savedir.'/'.$arr[$i])) unlink($savedir.'/'.$arr[$i]);
                }
            }
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }
}


$resp = null;
$urls = 'http://dev.aa.chiraya.de/servercenter/'.$url[2].'/mods/';

$serv->cfg_read('arkserverroot');
$savedir = $serv->dir_save();
$player_json = $helper->file_to_json('app/json/saves/player_'.$serv->name().'.json', false);
$tribe_json = $helper->file_to_json('app/json/saves/tribes_'.$serv->name().'.json', false);
if (!is_array($player_json)) $player_json = array();
if (!is_array($tribe_json)) $tribe_json = array();

$player = null; $c_pl = 0;
// Spieler
for ($i=0;$i<count($player_json);$i++) {
    $list_tpl = new Template('list_saves.htm', 'app/template/serv/page/list/');
    $list_tpl->load();

    $pl = $jhelper->player($player_json, $i);

    for ($z = 0; $z < count($tribe_json); $z++) {
        $member = $tribe_json[$z]->Members;

        if (in_array($pl->CharacterName, $member)) {
            $tribe = $jhelper->tribe($tribe_json, $z);
            $list_tpl->r('tribe', $tribe->Name);
            break;
        }
    }

    $list_tpl->r('tribe', '{::lang::php::sc::page::mods::no_tribe}');

    if ($pl->Level > 1000) $pl->Level = 0;
    if ($pl->TribeId == 7) $pl->TribeId = null;

    $list_tpl->r('IG:name', $pl->CharacterName);
    $list_tpl->r('IG:Level', $pl->Level);
    $list_tpl->r('update', converttime($pl->FileUpdated));
    $list_tpl->r('rnd', rndbit(10));
    $list_tpl->r('url', $steamapi->getsteamprofile_class($pl->SteamId)->profileurl);
    $list_tpl->r('img', $steamapi->getsteamprofile_class($pl->SteamId)->avatar);
    $list_tpl->r('steamname', $steamapi->getsteamprofile_class($pl->SteamId)->personaname);

    $list_tpl->r('rm_url', '/servercenter/'.$serv->name().'/saves/remove/'.$pl->SteamId.'.arkprofile');

    $list_tpl->r('EP', round($pl->ExperiencePoints, '2'));
    $list_tpl->r('SpielerID', $pl->Id);
    $list_tpl->r('TEP', $pl->TotalEngramPoints);
    $list_tpl->r('TID', $pl->TribeId);
    $file = $savedir.'/'.$pl->SteamId.'.arkprofile';
    $list_tpl->r('durl', "/".$file);

    $player .= $list_tpl->load_var();
    $c_pl++;
}

$tribe = null; $c_t = 0;
// Stämme

for ($i = 0; $i < count($tribe_json); $i++) {
    $list_tpl = new Template('list_tribes.htm', 'app/template/serv/page/list/');
    $list_tpl->load();

    $pl = $jhelper->tribe($tribe_json, $i);
    $playerlist = null;
    $ct=0;

    $member = $tribe_json[$i]->Members;

    foreach ($member as $key) {
        for ($z=0;$z<count($player_json); $z++) {
            $p = $jhelper->player($player_json, $z);
            if ($p->CharacterName == $key) {
                $playerlist_tpl = new Template('list_tribes_user.htm', 'app/template/serv/page/list/');
                $playerlist_tpl->load();

                $playerlist_tpl->r('IG:name', $p->CharacterName);
                $playerlist_tpl->r('lastupdate', converttime($p->FileUpdated));
                $playerlist_tpl->r('url', $steamapi->getsteamprofile_class($p->SteamId)->profileurl);
                $playerlist_tpl->r('img', $steamapi->getsteamprofile_class($p->SteamId)->avatar);
                $playerlist_tpl->r('steamname', $steamapi->getsteamprofile_class($p->SteamId)->personaname);
                $rank = '<b>{::lang::php::sc::page::mods::member}</b>';

                $playerlist .= $playerlist_tpl->load_var();
                $ct++;
            }
        }
    }

    $list_tpl->r('steamname', $steamapi->getsteamprofile_class($pl->SteamId)->personaname);
    $list_tpl->r('rnd', rndbit(10));
    $list_tpl->r('name', $pl->Name);
    $list_tpl->r('update', converttime($pl->FileUpdated));
    $list_tpl->r('pl', $playerlist);
    $list_tpl->r('count', $ct);
    $list_tpl->r('id', $pl->Id);
    $file = $savedir.'/'.$pl->Id.'.arktribe';
    $list_tpl->r('durl', "/".$file);

    $list_tpl->r('rm_url', '/servercenter/'.$serv->name().'/saves/remove/'.$pl->Id.'.arktribe');

    $tribe .= $list_tpl->load_var();
    $list_tpl = null;
    $c_t++;
}

$world = null; $w_t = 0;
$dirarr = dirToArray($savedir);
// World
for ($i=0;$i<count($dirarr);$i++) {
    if (strpos($dirarr[$i], '.ark')) {
        $file = $savedir.'/'.$dirarr[$i];
        if (file_exists($file)) {
            $list_tpl = new Template('list_world.htm', 'app/template/serv/page/list/');
            $list_tpl->load();
            $time = filemtime($file);

            $name = str_replace('.ark', null, $dirarr[$i]);
            $date_array = date_parse($name);

            $list_tpl->r('name', $name);
            $list_tpl->r('update', converttime($time));
            $list_tpl->r('durl', "/".$file);
            $list_tpl->r('rnd', rndbit(10));

            $list_tpl->r('rm_url', '/servercenter/'.$serv->name().'/saves/remove/'.$dirarr[$i]);

            if (!strpos($name, 'profile') && $date_array["year"] == null) {
                $world .= $list_tpl->load_var();
                $w_t++;
            }
        }
    }
}




$page_tpl->load();
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('urls' ,$urls);
$page_tpl->r('player', $player);
$page_tpl->r('tribe', $tribe);
$page_tpl->r('world', $world);
$page_tpl->r('cp', $c_pl);
$page_tpl->r('ct', $c_t);
$page_tpl->r('cw', $w_t);
$page_tpl->session();
$panel = $page_tpl->load_var();


?>