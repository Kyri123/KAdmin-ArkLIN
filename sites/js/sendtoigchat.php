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
        $text = $_POST['txt'];
        $user = $_POST['user'];

        if(!$rcon->send_command('serverchat ['.$user.']: '.$text)) {
            $msg = alert('danger', 'Konnte nicht ausgef&uuml;hrt werden...', '5px 5px 5px 5px', 'Fehler!');
        }
        else {
            $msg = alert('success', 'Nachricht gesendet <br />'.$text, '5px 5px 5px 5px', 'Gesendet');
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
