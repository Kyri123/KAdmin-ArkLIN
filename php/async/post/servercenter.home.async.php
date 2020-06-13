<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// call functions
require('../main.inc.php');

// create get vars
$cfg = $_GET['cfg'];
$case = $_GET['case'];

switch ($case) {
    // CASE: RCON send command
    case "rconsend":
        $serv = new server($_POST['cfg']);
        $json = file_get_contents('app/json/serverinfo/'.$serv->name().'.json');
        $server = json_decode($json);

        if ($server->online == 'Yes' && $serv->cfg_read('ark_RCONEnabled') == 'True' && $serv->cfg_read('ark_ServerAdminPassword') != '') {
            $code = '0';
            $alert->code = 12;
            $msg = $alert->re();

            //inz RCON
            $ip = $_SERVER['SERVER_ADDR'];
            $port = $serv->cfg_read('ark_RCONPort');
            $pw = $serv->cfg_read('ark_ServerAdminPassword');
            $rcon = new Rcon($ip, $port, $pw, 3);

            if ($rcon->connect()) {
                $code = '1';
                $command = $_POST['text'];
                $isnull = false; if ($command == "") $isnull = true;
                $user = $_POST['user'];


                if ($isnull === true) {
                    $alert->code = 2;
                    $msg = $alert->re();
                }
                elseif (!$rcon->send_command($command)) {
                    $alert->code = 12;
                    $msg = $alert->re();
                } else {
                    $resp = $rcon->get_response();
                    $alert->code = 107;
                    //$alert->r("command", $command); TODO: rausfinden wieso hier kein $alert geht...
                    //$alert->r("response", trim($resp)); TODO: rausfinden wieso hier kein $alert geht...
                    $msg = $alert->re();
                    $log = 'app/json/saves/rconlog_'.$serv->name().'.txt';
                    if (file_exists($log)) {
                        $file = file_get_contents($log);
                        $file = $file."\n".time().'(-/-)['.$user.'] '.$command;
                        if (file_put_contents($log, $file));
                    }
                    else {
                        if (file_put_contents($log, time().'(-/-)['.$user.'] '.$command));
                    }
                }
                $rcon->disconnect();
            }
        } else {
            $code = '0';
            $alert->code = 12;
            $alert->overwrite_text = "{::lang::php::async::post::servercenter::home::serveroffline}";
            $msg = $alert->re();
        }
        echo json_encode(['code'=>$code, 'msg'=>$msg]);
        break;

    // CASE: RCON Send Chat
    case "igchatsend":
        $serv = new server($_POST['cfg']);
        $json = file_get_contents('app/json/serverinfo/'.$serv->name().'.json');
        $server = json_decode($json);

        if ($server->online == 'Yes' && $serv->cfg_read('ark_RCONEnabled') == 'True' && $serv->cfg_read('ark_ServerAdminPassword') != '') {
            $code = '0';
            $alert->code = 12;
            $msg = $alert->re();

            //inz RCON
            $ip = $_SERVER['SERVER_ADDR'];
            $port = $serv->cfg_read('ark_RCONPort');
            $pw = $serv->cfg_read('ark_ServerAdminPassword');
            $rcon = new Rcon($ip, $port, $pw, 3);

            if ($rcon->connect()) {
                $code = '1';
                $text = $_POST['text'];
                $isnull = false; if ($text == null) $isnull = true;
                $user = $_POST['user'];
                $text = '{'.$user.'} '.$text;


                if ($isnull === true) {
                    $alert->code = 2;
                    $msg = $alert->re();
                }
                elseif (!$rcon->send_command('serverchat '.$text)) {
                    $alert->code = 12;
                    $msg = $alert->re();
                }
                else {
                    $alert->code = 107;
                    $msg = $alert->re();
                }
                $rcon->disconnect();
            }
        } else {
            $code = '0';
            $alert->code = 12;
            $alert->overwrite_text = "{::lang::php::async::post::servercenter::home::serveroffline}";
            $msg = $alert->re();
        }

        echo json_encode(['code'=>$code, 'msg'=>$msg]);
        break;
    default:
        echo "Case not found";
        break;
}
?>