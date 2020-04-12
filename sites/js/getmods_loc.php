<?php

require('js_inz.inc.php');
$cfg = $_GET['cfg'];
$api = new steamapi();
$serv = new server($cfg);

$resp = null;
$site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

$mods = explode(',', $serv->cfg_read("ark_GameModIds"));
$y = 1;


$total_count = count($mods);



//List Local mods
$array = dirToArray($serv->get_dir()."/ShooterGame/Content/Mods");
$exp = explode(",", $serv->cfg_read("ark_GameModIds"));

foreach($array as $key => $value) {
    $api->modid = $key;
    if($api->check_mod()) {
        $mod = $api->getmod_class($key);

        $tpl = new Template('list_mods_local.htm', 'tpl/serv/sites/list/');
        $tpl->load();
        $y = $i+1;
        $btns= null;
        $installed = false;
        if(in_array($key, $exp)) $installed = true;

        $tpl->repl('modid', $mod->publishedfileid);
        $tpl->repl('steamurl', $mod->file_url);
        $tpl->replif('active', $installed);
        $tpl->repl('img', $mod->preview_url);
        $tpl->repl('cfg', $cfg);
        $tpl->repl('rnd', rndbit(25));
        $tpl->repl('title', $mod->title);
        $tpl->repl('lastupdate', date('d.m.Y - H:i', $mod->time_updated));
        $resp .= $tpl->loadin();
        $tpl = null;
    }
}
if($resp == null) {
    $tpl = new Template('list_mods.htm', 'tpl/serv/sites/list/');
    $tpl->load();
    $tpl->repl('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
    $tpl->repl('title', "Es wurden keine Mods gefunden");
    $tpl->replif('empty', false);
    $resp = $tpl->loadin();
    $tpl = null;
}

echo $resp;
?>