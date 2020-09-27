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
    // CASE: Info
    case "info":
        $serv = new server($cfg);
        $tpl = new Template("server.htm", "app/template/core/serv/");
        $tpl->load();
        $i = false;
        $path = "app/json/serverinfo/" . $serv->name() . ".json";
        $data = $helper->file_to_json($path);

        // Status
        $serverstate = $serv->statecode();
        $serv_state = convertstate($serverstate)["str"];
        $serv_color = convertstate($serverstate)["color"];

        // Spieler Card
        $pl_pla = $data["aplayers"];
        $pl_plmax = $serv->cfg_read("ark_MaxPlayers");
        $pl_plpro = $pl_pla / $pl_plmax * 100;
        if ($serverstate < 2) {
            $pl_modal = null;
            $pl_disabled = 'disabled';
            $pl_btntxt = "{::lang::php::async::get::servercenter::main::server_need_online}";
        } else {
            $pl_modal = 'data-toggle="modal" data-target="#playerlist_modal"';
            $pl_disabled = null;
            $pl_btntxt = "{::lang::php::async::get::servercenter::main::showplayer}";
        }

        // Action Card
        if ($data["next"] == "TRUE" && $user->expert()) {
            $action_state = "{::lang::php::async::get::servercenter::main::action_closed}";
            $action_btntxt = "{::lang::php::async::get::servercenter::main::action_pick} <i class=\"fas fa-arrow-circle-right\"></i>";
            $action_modal = 'data-toggle="modal" data-target="#action"';
            $action_color = "danger";
        }
        elseif ($data["next"] == "TRUE") {
            $action_state = "{::lang::php::async::get::servercenter::main::action_closed}";
            $action_btntxt = "{::lang::php::async::get::servercenter::main::action_closed_need_open}";
            $action_modal = null;
            $action_color = "danger";
        }
        elseif ($data["next"] == "FALSE") {
            $action_state = "{::lang::php::async::get::servercenter::main::action_open}";
            $action_btntxt = "{::lang::php::async::get::servercenter::main::action_pick} <i class=\"fas fa-arrow-circle-right\"></i>";
            $action_modal = 'data-toggle="modal" data-target="#action"';
            $action_color = "success";
        }
        

        $tpl->r("serv_state", $serv_state);
        $tpl->r("serv_color", $serv_color);
        $tpl->r('serv_pid', null);

        $tpl->r("pl_plpro", $pl_plpro);
        $tpl->r("pl_disabled", $pl_disabled);
        $tpl->r("pl_plmax", $pl_plmax);
        $tpl->r("pl_pla", $pl_pla);
        $tpl->r("pl_modal", $pl_modal);
        $tpl->r("pl_btntxt", $pl_btntxt);

        $tpl->r("action_color", $action_color);
        $tpl->r("action_state", $action_state);
        $tpl->r("action_btntxt", $action_btntxt);
        $tpl->r("action_modal", $action_modal);

        $tpl->r("cfg", $serv->name());

        $map_path = "app/dist/img/igmap/".$serv->cfg_read("serverMap").".jpg";
        if (!file_exists($map_path)) $map_path = "app/dist/img/igmap/ark.png";

        if ($_GET["type"] == "cards") $string = $tpl->load_var();
        if ($_GET["type"] == "img") $string = '<img src="/'.$map_path.'" style="border-radius: 25rem !important;border-top-width: 3px!important;height: 90px;width: 90px;background-color: #001f3f" class="border-'.$serv_color.'">';
        if ($_GET["type"] == "imgtop") $string = '<img src="/'.$map_path.'" style="border-width: 3px!important;background-color: #001f3f" class="img-size-50 border border-'.$serv_color.'">';

        echo $string;
        break;

    case "actioninfo":
        $action = $_GET["action"];
        $alert->code = 300;
        $i = 0;
        $alert->overwrite_title = "{::lang::php::cfg::action::$action}";
        $alert->overwrite_text = "{::lang::servercenter::infoaction::$action}";
        $alert->overwrite_style = 3;
        if($action != "") echo $alert->re();
    break;

    default:
        echo "Case not found";
    break;
}
$mycon->close();
