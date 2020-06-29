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
    // CASE: Chat LOG
    case "livechat":
        $tpl = new Template('list_chat.htm', 'app/template/serv/page/list/');
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
                    $tpl = new Template('list_chat.htm', 'app/template/serv/page/list/');
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
        if ($resp == null) $resp = '<li class="list-group-item">0 | {::lang::php::async::get::all::getlog::no_log_found}</li>';

        $tpl = new Template("content.htm", "app/template/default/");
        $tpl->load();
        $tpl->r("content", $resp);
        $tpl->echo();
        break;

    // CASE: RCON LOG
    case "rconlog":
        $tpl = new Template('list_chat.htm', 'app/template/serv/page/list/');
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
                    $tpl = new Template('list_chat.htm', 'app/template/serv/page/list/');
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
        if ($resp == null) $resp = '<li class="list-group-item">0 | {::lang::php::async::get::all::getlog::no_log_found}</li>';

        $tpl = new Template("content.htm", "app/template/default/");
        $tpl->load();
        $tpl->r("content", $resp);
        $tpl->echo();
        break;
    default:
        echo "Case not found";
        break;
}
?>