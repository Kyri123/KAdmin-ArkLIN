<?php
require('js_inz.inc.php');

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
        $text = $_POST['text'];
        $isnull = false; if($text == null) $isnull = true;
        $user = $_POST['user'];
        $text = '{'.$user.'} '.$text;


        if ($isnull === true) {
            $msg = alert('danger', 'Es wurde kein Text angegeben!', '5px 5px 5px 5px', 'Fehler!');
        }
        elseif(!$rcon->send_command('serverchat '.$text)) {
        $msg = alert('danger', 'Konnte nicht ausgef&uuml;hrt werden...', '5px 5px 5px 5px', 'Fehler!');
        } else {
            $msg = alert('success', '<b>Nachricht gesendet - Bitte kurz warten bis es im Chat auftaucht :)</b> <br />'.$text, '5px 5px 5px 5px', 'Gesendet');
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
