<?php
$jhelper = new player_json_helper();

if(isset($url[4]) && $url[4] == 'remove' && isset($url[5])) {

    //set vars
    $file_name = $url[5];
    $savedir = $serv->get_save_dir();

    // Wenn Spieler
    if(strpos($file_name, 'profile') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.arkprofile', null, $file_name);
        $file['name2'] = str_replace('.arkprofile', '.profilebak', $file_name);
        $file['name2'] = $savedir.'/'.$file['name2'];
        $del[0] = 0;
        $del[1] = 0;

        if(file_exists($file['name1'])) {
            if(unlink($file['name1']));
        }
        if(file_exists($file['name2'])) {
            if(unlink($file['name2']));
        }

        $path = 'data/saves/player_'.$serv->show_name().'.json';
        $json = $helper->file_to_json($path);

        for($i=0;$i<count($json);$i++) {
            $pl = $jhelper->player($json, $i);
            if($file['tname'] == $pl->SteamId) {
                unset($json[$i]);
                break;
            }
        }
        $json = array_values($json);
        print_r($json);
        if(file_put_contents($path, $helper->json_to_str($json))) {
            header('Location: /serverpage/'.$serv->show_name().'/saves/');
            exit;
        }
    }


    // Wenn Stamm
    elseif(strpos($file_name, 'tribe') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.arktribe', null, $file_name);
        $file['name2'] = str_replace('.arktribe', '.tribebak', $file_name);
        $file['name2'] = $savedir.'/'.$file['name2'];
        $del[0] = 0;
        $del[1] = 0;

        print_r($file);

        if(file_exists($file['name1'])) {
            if(unlink($file['name1']));
        }
        if(file_exists($file['name2'])) {
            if(unlink($file['name2']));
        }
        print_r($del);

        $path = 'data/saves/tribes_'.$serv->show_name().'.json';
        $json = $helper->file_to_json($path);
        for($i=0;$i<count($json);$i++) {
            $pl = $jhelper->tribe($json, $i);
            if($file['tname'] == $pl->Id) {
                unset($json[$i]); break;
            }
        }
        $json = array_values($json);
        if(file_put_contents($path, $helper->json_to_str($json))) {
            header('Location: /serverpage/'.$serv->show_name().'/saves/');
            exit;
        }
    }


    // Wenn Welt
    elseif(strpos($file_name, '.ark') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.ark', null, $file_name);
        if(unlink($file['name1'])) {
            $arr = dirToArray($serv->get_save_dir());
            for($i=0;$i<count($arr);$i++) {
                if(strpos($arr[$i], $file['tname']) !== false) {
                    if(file_exists($savedir.'/'.$arr[$i])) unlink($savedir.'/'.$arr[$i]);
                }
            }
            header('Location: /serverpage/'.$serv->show_name().'/saves/');
            exit;
        }
    }
}


$resp = null;
$urls = 'http://dev.aa.chiraya.de/serverpage/'.$url[2].'/mods/';

$serv->cfg_read('arkserverroot');
$savedir = $serv->get_save_dir();
$player_json = $helper->file_to_json('data/saves/player_'.$serv->show_name().'.json', false);
$tribe_json = $helper->file_to_json('data/saves/tribes_'.$serv->show_name().'.json', false);
if(!is_array($player_json)) $player_json = array();
if(!is_array($tribe_json)) $tribe_json = array();


$player = null; $c_pl = 0;
// Spieler
if(is_array($player_json)) {
    for($i=0;$i<count($player_json);$i++) {
        $list_tpl = new Template('list_saves.htm', 'tpl/serv/sites/list/');
        $list_tpl->load();

        $pl = $jhelper->player($player_json, $i);

        if(is_array($tribe_json)) {
            for ($z = 0; $z < count($tribe_json); $z++) {
                $tribe = $jhelper->tribe($tribe_json, $z);
                if ($tribe->Id == $pl->TribeId) {
                    $list_tpl->repl('tribe', $tribe->Name);
                }
            }
        }
        $list_tpl->repl('tribe', '[Kein Stamm]');

        if($pl->Level > 1000) $pl->Level = 0;
        if($pl->TribeId == 7) $pl->TribeId = null;

        $list_tpl->repl('IG:name', $pl->CharacterName);
        $list_tpl->repl('IG:Level', $pl->Level);
        $list_tpl->repl('lastupdate', converttime($pl->FileUpdated));
        $list_tpl->repl('rnd', rndbit(10));
        $list_tpl->repl('url', $steamapi->getsteamprofile_class($pl->SteamId)->profileurl);
        $list_tpl->repl('img', $steamapi->getsteamprofile_class($pl->SteamId)->avatar);
        $list_tpl->repl('steamname', $steamapi->getsteamprofile_class($pl->SteamId)->personaname);

        $list_tpl->repl('rm_url', '/serverpage/'.$serv->show_name().'/saves/remove/'.$pl->SteamId.'.arkprofile');

        $list_tpl->repl('EP', round($pl->ExperiencePoints, '2'));
        $list_tpl->repl('SpielerID', $pl->Id);
        $list_tpl->repl('TEP', $pl->TotalEngramPoints);
        $list_tpl->repl('TID', $pl->TribeId);
        $file = $savedir.'/'.$pl->SteamId.'.arkprofile';
        $list_tpl->repl('durl', "/".$file);

        $player .= $list_tpl->loadin();
        $c_pl++;
    }
}

$tribe = null; $c_t = 0;
// Stämme
if(is_array($tribe_json)) {
    for ($i = 0; $i < count($tribe_json); $i++) {
        $list_tpl = new Template('list_tribes.htm', 'tpl/serv/sites/list/');
        $list_tpl->load();

        $pl = $jhelper->tribe($tribe_json, $i);
        $playerlist = null;
        $ct=0;

        if(is_array($player_json)) {
            for ($z=0;$z<count($player_json); $z++) {
                $p = $jhelper->player($player_json, $z);
                //print_r($pl); echo "<br />";
                //print_r($p); echo "<br />";
                if ($pl->Id == $p->TribeId) {

                    $playerlist_tpl = new Template('list_tribes_user.htm', 'tpl/serv/sites/list/');
                    $playerlist_tpl->load();

                    $playerlist_tpl->repl('IG:name', $p->CharacterName);
                    $playerlist_tpl->repl('lastupdate', converttime($p->FileUpdated));
                    $playerlist_tpl->repl('url', $steamapi->getsteamprofile_class($p->SteamId)->profileurl);
                    $playerlist_tpl->repl('img', $steamapi->getsteamprofile_class($p->SteamId)->avatar);
                    $playerlist_tpl->repl('steamname', $steamapi->getsteamprofile_class($p->SteamId)->personaname);

                    if($pl->OwnerId == $p->Id) {
                        $rank = '<b>Besitzer:</b>';
                    }
                    else {
                        $rank = '<b>Member:</b>';
                    }

                    $playerlist .= $playerlist_tpl->loadin();
                    $ct++;
                }
            }
        }

        $list_tpl->repl('steamname', $steamapi->getsteamprofile_class($pl->SteamId)->personaname);
        $list_tpl->repl('rnd', rndbit(10));
        $list_tpl->repl('name', $pl->Name);
        $list_tpl->repl('update', converttime($pl->FileUpdated));
        $list_tpl->repl('pl', $playerlist);
        $list_tpl->repl('count', $ct);
        $list_tpl->repl('id', $pl->Id);
        $file = $savedir.'/'.$pl->Id.'.arktribe';
        $list_tpl->repl('durl', "/".$file);

        $list_tpl->repl('rm_url', '/serverpage/'.$serv->show_name().'/saves/remove/'.$pl->Id.'.arktribe');

        $tribe .= $list_tpl->loadin();
        $list_tpl = null;
        $c_t++;
    }
}

$world = null; $w_t = 0;
$dirarr = dirToArray($savedir);
// World
for($i=0;$i<count($dirarr);$i++) {
    if(strpos($dirarr[$i], '.ark')) {
        $file = $savedir.'/'.$dirarr[$i];
        if(file_exists($file)) {
            $list_tpl = new Template('list_world.htm', 'tpl/serv/sites/list/');
            $list_tpl->load();
            $time = filemtime($file);

            $name = str_replace('.ark', null, $dirarr[$i]);

            $list_tpl->repl('name', $name);
            $list_tpl->repl('update', converttime($time));
            $list_tpl->repl('durl', "/".$file);

            $list_tpl->repl('rm_url', '/serverpage/'.$serv->show_name().'/saves/remove/'.$dirarr[$i]);

            $world .= $list_tpl->loadin();
            $w_t++;
        }
    }
}



$page_tpl = new Template('saves.htm', 'tpl/serv/sites/');
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Savegames</li>';

$page_tpl->load();
$page_tpl->repl('cfg' ,$url[2]);
$page_tpl->repl('urls' ,$urls);
$page_tpl->repl('player', $player);
$page_tpl->repl('tribe', $tribe);
$page_tpl->repl('world', $world);
$page_tpl->repl('cp', $c_pl);
$page_tpl->repl('ct', $c_t);
$page_tpl->repl('cw', $w_t);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>