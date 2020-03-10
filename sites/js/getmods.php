
<?php


chdir($_SERVER['DOCUMENT_ROOT']);
include('inc/class/server.class.inc.php');
include('inc/class/steamAPI.class.inc.php');
include('inc/class/Template.class.inc.php');
include('inc/func/allg.func.inc.php');
$cfg = $_GET['cfg'];
$resp = null;
$site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

$server = parse_ini_file('remote/arkmanager/instances/'.$cfg.'.cfg');
$mods = $server['ark_GameModIds'];
$mods = explode(',', $mods);
$y = 1;

$api = new steamapi();

$total_count = count($mods);
if($total_count > 1 || $mods[0] != 0) {
    for($i=0;$i<count($mods);$i++) {
        $tpl = new Template('list_mods.htm', 'tpl/serv/sites/list/');
        $tpl->load();
        $json = $api->getmod($mods[$i]);
        $y = $i+1;
        $btns= null;
        if($i == 0) {
            $tpl->replif('ifup', false);
            $tpl->replif('ifdown', true);
        }
        elseif($y != count($mods)) {
            $tpl->replif('ifup', true);
            $tpl->replif('ifdown', true);
        }
        else {
            $tpl->replif('ifup', true);
            $tpl->replif('ifdown', false);
        }
        $tpl->repl('modid', $mods[$i]);
        $tpl->replif('empty', false);
        $tpl->repl('img', $json->response->publishedfiledetails[0]->preview_url);
        $tpl->repl('cfg', $cfg);
        $tpl->repl('title', $json->response->publishedfiledetails[0]->title);
        $tpl->repl('lastupdate', date('d.m.Y - H:m', $json->response->publishedfiledetails[0]->time_updated));
        $resp .= $tpl->loadin();
        $tpl = null;
    }
}
else {
    $tpl = new Template('list_mods.htm', 'tpl/serv/sites/list/');
    $tpl->load();
    $tpl->repl('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
    $tpl->repl('title', "Es wurden keine Mods gefunden");
    $tpl->replif('empty', false);
    $resp .= $tpl->loadin();
    $tpl = null;
}

if(empty($errorMSG)){
    echo json_encode(['code'=>200, 'msg'=>$resp]);
    exit;
}
echo json_encode(['code'=>404, 'msg'=>$errorMSG]);
?>