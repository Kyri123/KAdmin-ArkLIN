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
$cfg = (isset($_GET['cfg'])) ? $_GET['cfg'] : $_POST['cfg'];
$case = (isset($_GET['case'])) ? $_GET['case'] : $_POST['case'];

switch ($case) {
    // CASE: RCON send command
    case "rconsend":
        $serv = new server($_POST['cfg']);
        $cfg = $serv->name();
        $json = file_get_contents(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json');
        $server = json_decode($json);

        if($session_user->perm("server/$cfg/home/rcon_send")) {
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
                        $msg = $alert->re();
                        $log = __ADIR__.'/app/json/saves/rconlog_'.$serv->name().'.txt';
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
        } else {
            $msg = $alert->rd(99);
        }
        echo json_encode(['code'=>$code, 'msg'=>$msg]);
        break;

    // CASE: LiveChat Send Chat
    case "igchatsend":
        $serv = new server($_POST['cfg']);
        $json = file_get_contents(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json');
        $server = json_decode($json);

        if($session_user->perm("server/$cfg/home/livechat_send")) {
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
                    $isnull = false;
                    if ($text == null) $isnull = true;
                    $user = $_POST['user'];
                    $text = '{' . $user . '} ' . $text;


                    if ($isnull === true) {
                        $alert->code = 2;
                        $msg = $alert->re();
                    } elseif (!$rcon->send_command('serverchat ' . $text)) {
                        $alert->code = 12;
                        $msg = $alert->re();
                    } else {
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
        }else {
            $msg = $alert->rd(99);
        }

        echo json_encode(['code'=>$code, 'msg'=>$msg]);
        break;

        // case toggle whitelist
        case "togglewhitelist":
            if($session_user->perm("server/$cfg/home/whitelist_send")) {
                $rcon = false;
                $id = $_POST["sid"];
                $serv = new server($cfg);
                $whitelistfile = $serv->dir_main() . "/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";
                $arr = file($whitelistfile);
                for ($i = 0; $i < count($arr); $i++) {
                    $arr[$i] = trim($arr[$i]);
                }
                $i = 0;
                if (!$serv->check_rcon()) {
                    $content = file_get_contents($whitelistfile);
                    if (in_array($id, $arr)) {
                        $content = str_replace($id, null, $content);
                    } else {
                        $content .= "\n\r$id";
                    }
                    $response = (file_put_contents($whitelistfile, $content)) ? 105 : 1;
                } elseif (in_array($id, $arr)) {
                    $command = "DisallowPlayerToJoinNoCheck $id";
                    $rcon = true;
                } else {
                    $command = "AllowPlayerToJoinNoCheck $id";
                    $rcon = true;
                }
                if ($rcon) $response = $serv->exec_rcon($command);
                echo $alert->rd($response);
            } else {
                echo $alert->rd(99);
            }
        break;
    default:
        echo "Case not found";
        break;
}
$mycon->close();
