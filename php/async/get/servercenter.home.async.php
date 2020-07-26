<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg = $_GET['cfg'];
$case = $_GET['case'];

switch ($case) {
    // CASE: Whitelist list
    case "loadwhite":
        //erstelle SteamAPI von Savegames

        $serv = new server($cfg);
        $whitelistfile = $serv->dir_main()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";
        $file = file($whitelistfile);
        $arr = [];

        if (is_array($file)) {
            for ($i = 0; $i < count($file); $i++) {
                $find = array("\n", "\r", " ");
                $file[$i] = str_replace($find, null, $file[$i]);
                if($file[$i] != "0" && $file[$i] != "" && $file[$i] != null) $arr[] = $file[$i];
            }
        }
        
        $steamapi->getsteamprofile_list("whitelist_".$serv->name(), $arr, 0);
        $file = $helper->file_to_json('app/json/steamapi/profile_whitelist_'.$serv->name().'.json', true)["response"]["players"];

        for ($i=0;$i<count($file);$i++) {
            $list_tpl = new Template('whitelist.htm', 'app/template/lists/serv/jquery/');
            $list_tpl->load();

            $list_tpl->r("sid", $file[$i]["steamid"]);
            $list_tpl->r("url", $file[$i]["profileurl"]);
            $list_tpl->r("cfg", $serv->name());
            $list_tpl->r("rndb", rndbit(25));
            $list_tpl->r("name", $file[$i]["personaname"]);
            $list_tpl->r("img", $file[$i]["avatarmedium"]);

            $adminlist_admin .= $list_tpl->load_var();
        }
        echo $adminlist_admin;
    break;


    // CASE: Chat LOG
    case "livechat":
        $tpl = new Template('chat.htm', 'app/template/lists/serv/jquery/');
        $tpl->load();
        $serv = new server($_GET['cfg']);
        $path = 'app/json/saves/chat_'.$serv->name().'.log';
        if (file_exists($path)) {
            $filearray = file($path);
            $resp = null;
            $z = count($filearray)-1;
            $ib = 0;
            for ($i=0;$i<count($filearray);$i++) {
                if ($filearray[$z] != null) {
                    $exp = explode('(-/-)', $filearray[$z]);
                    $tpl = new Template('chat.htm', 'app/template/lists/serv/jquery/');
                    $tpl->load();
                    $tpl->r('msg', $exp[1]);
                    $tpl->r('time', date('d.m.Y - H:i:s', $exp[0]));
                    $tpl->r('i', $ib);
                    $resp .= $tpl->load_var();
                }
                $z--;
                $ib++;
                if ($ib>99) break;
            }
        }
        if ($resp == null) $resp = '<tr><td>{::lang::php::async::get::all::getlog::no_log_found}</i></td></tr>';

        $tpl = new Template("content.htm", "app/template/universally/default/");
        $tpl->load();
        $tpl->r("content", $resp);
        $tpl->echo();
    break;

    case "load":
        $list = new Template("load_list.htm", "app/template/universally/jquery/");
        $list->load();
        $list->echo();
    break;

    // CASE: RCON LOG
    case "rconlog":
        $tpl = new Template('chat.htm', 'app/template/lists/serv/jquery/');
        $tpl->load();
        $serv = new server($_GET['cfg']);
        $path = 'app/json/saves/rconlog_'.$serv->name().'.txt';
        $resp = null;
        if (file_exists($path)) {
            $filearray = file($path);
            $z = count($filearray)-1;
            $ib = 0;
            for ($i=0;$i<count($filearray);$i++) {
                if ($filearray[$z] != null) {
                    $exp = explode('(-/-)', $filearray[$z]);
                    $tpl = new Template('chat.htm', 'app/template/lists/serv/jquery/');
                    $tpl->load();
                    $tpl->r('msg', $exp[1]);
                    $tpl->r('time', date('d.m.Y - H:i:s', $exp[0]));
                    $tpl->r('i', $ib);
                    $resp .= $tpl->load_var();
                }
                $z--;
                $ib++;
                if ($ib>99) break;
            }
        }
        if ($resp == null) $resp = '<tr><td>{::lang::php::async::get::all::getlog::no_log_found}</i></td></tr>';

        $tpl = new Template("content.htm", "app/template/universally/default/");
        $tpl->load();
        $tpl->r("content", $resp);
        $tpl->echo();
        break;
    default:
        echo "Case not found";
        break;
}
?>