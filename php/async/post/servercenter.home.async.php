<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

// call functions
require('../main.inc.php');

// create get vars
$cfg    = isset($_GET['cfg'])   ? $_GET['cfg']      : $_POST['cfg'];
$case   = isset($_GET['case'])  ? $_GET['case']     : $_POST['case'];

switch ($case) {
    // CASE: RCON send command
    case "rconsend":
        $serv       = new server($_POST['cfg']);
        $cfg        = $serv->name();
        $json       = $KUTIL->fileGetContents(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json');
        $server     = json_decode($json);

        if($session_user->perm("server/$cfg/home/rcon_send")) {
            //inz RCON
            $ip         = $_SERVER['SERVER_ADDR'];
            $port       = $serv->cfgRead('ark_RCONPort');
            $pw         = $serv->cfgRead('ark_ServerAdminPassword');
            $command    = $_POST['text'];
            $users      = $_POST['user'];

            $exec       = $command != "" ? $serv->execRcon($command) : false;

            if($exec) {
                $code   = '1';
                $msg    = $alert->rd(107);
                $log    = __ADIR__.'/app/json/saves/rconlog_'.$serv->name().'.txt';
                if (@file_exists($log)) {
                    $file   = $KUTIL->fileGetContents($log);
                    $file   = $file."\n".time().'(-/-)['.$users.'] '.$command;
                    $KUTIL->filePutContents($log, $file);
                }
                else {
                    $KUTIL->createFile($log, time().'(-/-)['.$users.'] '.$command);
                }
            } else {
                $code   = '0';
                $alert->code = 12;
                $alert->overwrite_text = "{::lang::php::async::post::servercenter::home::serveroffline}";
                $msg    = $alert->re();
            }
        } else {
            $msg = $alert->rd(99);
        }

        echo json_encode(['code'=>$code, 'msg'=>$msg]);
        break;

    // CASE: LiveChat Send Chat
    case "igchatsend":
        $serv       = new server($_POST['cfg']);
        $json       = $KUTIL->fileGetContents(__ADIR__.'/app/json/serverinfo/'.$serv->name().'.json');
        $server     = json_decode($json);

        if($session_user->perm("server/$cfg/home/livechat_send")) {
            $code           = '0';
            $alert->code    = 12;
            $msg            = $alert->re();
            $text           = $_POST['text'];
            $users          = $_POST['user'];
            $text           = '{' . $users . '} ' . $text;

            $exec           = $text != "" ? $serv->execRcon($text) : false;

            if ($exec) {
                $code       = '1';
                $text       = $_POST['text'];
                $isnull     = $text == null;
                $user       = $_POST['user'];
                $text       = '{' . $user . '} ' . $text;
                $msg    = $alert->rd(107);
            } else {
                $code                   = '0';
                $alert->code            = 12;
                $alert->overwrite_text  = "{::lang::php::async::post::servercenter::home::serveroffline}";
                $msg                    = $alert->re();
            }
        }else {
            $msg = $alert->rd(99);
        }

        echo json_encode(['code'=>$code, 'msg'=>$msg]);
        break;

        // case toggle whitelist
        case "togglewhitelist":
            if($session_user->perm("server/$cfg/home/whitelist_send")) {
                $rcon           = false;
                $id             = $_POST["sid"];
                $serv           = new server($cfg);
                $whitelistfile  = $serv->dirMain() . "/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";
                $arr            = file($whitelistfile);
                for ($i = 0; $i < count($arr); $i++)
                    $arr[$i] = trim($arr[$i]);

                $i = 0;
                if (!$serv->checkRcon()) {
                    $content        = $KUTIL->fileGetContents($whitelistfile);
                    if (in_array($id, $arr)) {
                        $content    = str_replace($id, null, $content);
                    } else {
                        $content    .= "\n\r$id";
                    }
                    $response = ($KUTIL->filePutContents($whitelistfile, $content)) ? 103 : 1;
                } elseif (in_array($id, $arr)) {
                    $command    = "DisallowPlayerToJoinNoCheck $id";
                    $rcon       = true;
                } else {
                    $command    = "AllowPlayerToJoinNoCheck $id";
                    $rcon       = true;
                }
                if ($rcon) $response = $serv->execRcon($command);
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
