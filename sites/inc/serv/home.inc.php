<?php

$page_tpl = new Template('home.htm', 'tpl/serv/sites/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Startseite</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->repl('cfg' ,$serv->show_name());
$page_tpl->repl('SESSION_USERNAME' ,$user->name());

/*
$main_dir = $serv->get_save_dir(true);
$file = $main_dir."/AllowedCheaterSteamIDs.txt";
if(!file_exists($file)) file_put_contents($file, 76561198006463179);
$array = file($file);
echo file_get_contents($file);
var_dump($array);
*/
$serv->cfg_read('arkserverroot');
$savedir = $serv->get_save_dir();
$player_json = $helper->file_to_json('data/saves/player_'.$serv->show_name().'.json', false);
$tribe_json = $helper->file_to_json('data/saves/tribes_'.$serv->show_name().'.json', false);
if(!is_array($player_json)) $player_json = array();
if(!is_array($tribe_json)) $tribe_json = array();

/*
foreach ($array as $key => $value) {
    $list_tpl = new Template('list_saves.htm', 'tpl/serv/sites/list/');
    $list_tpl->load();
    echo $key."<hr>";
    if(in_array($value, $player_json)) {
        for ($z = 0; $z < count($tribe_json); $z++) {
            if($value == $player_json[$z]) $i = $z;
            if($value == $player_json[$z]) break;
        }
    }
    echo $i;

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
*/

$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>