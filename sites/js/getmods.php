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
if($total_count > 1 || $mods[0] > 0) {
    for($i=0;$i<count($mods);$i++) {
        $api->modid = $mods[$i];
        if($api->check_mod()) {
            $mod = $api->getmod_class($mods[$i]);

            $tpl = new Template('list_mods.htm', 'tpl/serv/sites/list/');
            $tpl->load();

            $y = $i + 1;
            $btns = null;

            if ($i == 0 && $total_count > 1) {
                $tpl->replif('ifup', false);
                $tpl->replif('ifdown', true);
            } elseif ($i == 0) {
                $tpl->replif('ifup', false);
                $tpl->replif('ifdown', false);
            } elseif ($y != count($mods)) {
                $tpl->replif('ifup', true);
                $tpl->replif('ifdown', true);
            } else {
                $tpl->replif('ifup', true);
                $tpl->replif('ifdown', false);
            }
            $tpl->repl('modid', $mod->publishedfileid);
            $tpl->replif('empty', true);
            $tpl->repl('img', $mod->preview_url);
            $tpl->repl('cfg', $cfg);
            $tpl->repl('title', $mod->title);
            $tpl->repl('lastupdate', date('d.m.Y - H:i', $mod->time_updated));
            $resp .= $tpl->loadin();
            $tpl = null;
        }
    }
}
else {
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