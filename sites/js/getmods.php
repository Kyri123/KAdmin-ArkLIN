
<?php


chdir($_SERVER['DOCUMENT_ROOT']);
include('inc/class/server.class.inc.php');
include('inc/class/steamAPI.class.inc.php');
include('inc/func/allg.func.inc.php');
$cfg = $_GET['cfg'];
$resp = null;
$site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

$server = parse_ini_file('remote/arkmanager/instances/'.$cfg.'.cfg');
$mods = $server['ark_GameModIds'];
$mods = explode(',', $mods);
$y = 1;

$api = new steamapi();

for($i=0;$i<count($mods);$i++) {
    $json = $api->getmod($mods[$i]);
    $y = $i+1;
    $btns= null;
    if($i == 0) {
        $btns = '
                                    <a href="http://dev.aa.chiraya.de/serverpage/'.$cfg.'/mods/bot/'.$mods[$i].'" class="btn btn-info">
                                        <span class="icon text-white">
                                            <i class="fas fa-arrow-down"></i>
                                        </span>
                                    </a>';
    }
    elseif($y != count($mods)) {
        $btns = '
                                    <a href="http://dev.aa.chiraya.de/serverpage/'.$cfg.'/mods/top/'.$mods[$i].'" class="btn btn-info">
                                        <span class="icon text-white">
                                            <i class="fas fa-arrow-up"></i>
                                        </span>
                                    </a>
                                    <a href="http://dev.aa.chiraya.de/serverpage/'.$cfg.'/mods/bot/'.$mods[$i].'" class="btn btn-info">
                                        <span class="icon text-white">
                                            <i class="fas fa-arrow-down"></i>
                                        </span>
                                    </a>';
    }
    else {
        $btns = '
                                    <a href="http://dev.aa.chiraya.de/serverpage/'.$cfg.'/mods/top/'.$mods[$i].'" class="btn btn-info">
                                        <span class="icon text-white">
                                            <i class="fas fa-arrow-up"></i>
                                        </span>
                                    </a>';
    }
    $resp .= '<li class="list-group-item">
                        <div class="row p-0">
                            <div class="col-12">
                                <img class="rounded -align-left position-absolute" src="'.$json->response->publishedfiledetails[0]->preview_url.'" height="50" width="50">
                                <div class="ml-auto float-right  btn-group">
                                    '.$btns.'
                                    <a href="http://dev.aa.chiraya.de/serverpage/'.$cfg.'/mods/remove/'.$mods[$i].'" class="btn btn-danger">
                                        <span class="icon text-white">
                                            <i class="fas fa-times"></i>
                                        </span>
                                    </a>
                                    <a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$mods[$i].'" class="btn btn-dark" target="_blank">
                                        <span class="icon text-white">
                                            <i class="fab fa-steam-symbol"></i>
                                        </span>
                                    </a>
                                </div>
                                <div style="margin-left: 60px;">'.$json->response->publishedfiledetails[0]->title.'<br /><span class="font-weight-light" style="font-size: 11px;"> '.$mods[$i].' | Updated: '.date('d.m.Y - H:m', $json->response->publishedfiledetails[0]->time_updated).'</span></div>                               
                            </div>
                        </div>
                    </li>';
}


if(empty($errorMSG)){
    echo json_encode(['code'=>200, 'msg'=>$resp]);
    exit;
}
echo json_encode(['code'=>404, 'msg'=>$errorMSG]);
?>