<?php
require('js_inz.inc.php');

$cfg = $_GET['cfg'];

$resp = null;
$site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

$server = parse_ini_file('remote/arkmanager/instances/'.$cfg.'.cfg');
$mods = $server['ark_GameModIds'];
$mods = explode(',', $mods);
$y = 1;


$savedir = $serv->get_save_dir();
$player_json = $helper->file_to_json('data/saves/player_'.$serv->show_name().'.json');
$tribe_json = $helper->file_to_json('data/saves/tribes_'.$serv->show_name().'.json');
if(!is_array($player_json)) $player_json = array();
if(!is_array($tribe_json)) $tribe_json = array();

$player = null; $c_pl = 0;
print_r($player_json);

// Spieler
if(is_array($player_json)) {
    for($i=0;$i<count($player_json);$i++) {
        $list_tpl = new Template('list_user.htm', 'tpl/serv/sites/list/');
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

        $player .= $list_tpl->loadin();
        $c_pl++;
        break;
    }
}



if(empty($errorMSG)){
    echo json_encode(['code'=>200, 'msg'=>$player]);
    exit;
}
echo json_encode(['code'=>404, 'msg'=>$errorMSG]);
?>