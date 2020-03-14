<?php
require('./js_inz.inc.php');

$serv = new server($_POST['cfg']);
$json = file_get_contents('data/serv/'.$serv->show_name().'.json');
$server = json_decode($json);

if($server->online == 'Yes' && $serv->cfg_read('ark_RCONEnabled') == 'True' && $serv->cfg_read('ark_ServerAdminPassword') != '') {
    $code = '0';
    $msg = alert('danger', 'Konnte keine Verbindung herstellen...', '5px 5px 5px 5px', 'Fehler!');

    //inz RCON
    $ip = $_SERVER['SERVER_ADDR'];
    $port = $serv->cfg_read('ark_RCONPort');
    $pw = $serv->cfg_read('ark_ServerAdminPassword');
    $rcon = new Rcon($ip, $port, $pw, 3);

    if($rcon->connect()) {
        $code = '1';
        $command = $_POST['text'];
        $isnull = false; if($command == null) $isnull = true;
        $user = $_POST['user'];


        if ($isnull === true) {
            $msg = alert('danger', 'Es wurde kein Befehl angegeben!', '5px 5px 5px 5px', 'Fehler!');
        }
        elseif(!$rcon->send_command($command)) {
            $msg = alert('danger', 'Konnte nicht ausgef&uuml;hrt werden...', '5px 5px 5px 5px', 'Fehler!');
        } else {
            $resp = $rcon->get_response();
            $msg = alert('success', '<b>Rcon Gesendet: <i style="color:#00a65a !important">'.$command.'</i></b><hr />'.nl2br($resp), '5px 5px 5px 5px', 'Gesendet');
            $log = 'data/saves/rconlog_'.$serv->show_name().'.txt';
            if(file_exists($log)) {
                $file = file_get_contents($log);
                $file = $file."\n".time().'(-/-)['.$user.'] '.$command;
                if(file_put_contents($log, $file));
            }
            else {
                    if(file_put_contents($log, time().'(-/-)['.$user.'] '.$command));
            }
        }
        $rcon->disconnect();
    }
}
else {
    $code = '0';
    $msg = alert('danger', 'Server ist offline, RCON ist deaktiviert oder kein Admin Passwort gesetzt!', '5px 5px 5px 5px', 'Fehler!');
}

echo json_encode(['code'=>$code, 'msg'=>$msg]);
?>
