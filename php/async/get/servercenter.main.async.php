<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED
$ROOT   = str_replace("/php/async/main.inc.php", null, $_SERVER["SCRIPT_NAME"]);

require('../main.inc.php');
$cfg    = $_GET['cfg'];
$case   = $_GET['case'];

switch ($case) {
    // CASE: Info
    case "info":
        $serv   = new server($cfg);
        $tpl    = new Template("server.htm", __ADIR__."/app/template/core/serv/");
        $tpl->load();
        $i      = false;
        $path   = __ADIR__."/app/json/serverinfo/" . $serv->name() . ".json";
        $data   = $helper->fileToJson($path);

        // Status
        $serverstate    = $serv->stateCode();
        $serv_state     = convertstate($serverstate)["str"];
        $serv_color     = convertstate($serverstate)["color"];

        // Spieler Card
        $pl_pla         = $data["aplayers"];
        $pl_plmax       = $serv->cfgRead("ark_MaxPlayers");
        $pl_plpro       = $pl_pla / $pl_plmax * 100;

        $pl_modal       = $serverstate < 2 ? null
            : 'data-toggle="modal" data-target="#playerlist_modal"';

        $pl_disabled    = $serverstate < 2 ? 'disabled'
            : null;

        $pl_btntxt      = $serverstate < 2 ? "{::lang::php::async::get::servercenter::main::server_need_online}"
            : "{::lang::php::async::get::servercenter::main::showplayer}";

        // Action Card
        $action_state   = $data["next"] == "TRUE" && $user->expert() ? "{::lang::php::async::get::servercenter::main::action_closed}"
            : ($data["next"] == "TRUE" ? "{::lang::php::async::get::servercenter::main::action_closed}"
                : "{::lang::php::async::get::servercenter::main::action_open}");

        $action_btntxt  = $data["next"] == "TRUE" && $user->expert() ? "{::lang::php::async::get::servercenter::main::action_pick} <i class=\"fas fa-arrow-circle-right\"></i>"
            : ($data["next"] == "TRUE" ? "{::lang::php::async::get::servercenter::main::action_closed_need_open}"
                : "{::lang::php::async::get::servercenter::main::action_pick} <i class=\"fas fa-arrow-circle-right\"></i>");

        $action_modal   = $data["next"] == "TRUE" && $user->expert() ? 'data-toggle="modal" data-target="#action"'
            : ($data["next"] == "TRUE" ? null
                : 'data-toggle="modal" data-target="#action"');

        $action_color   = $data["next"] == "TRUE" && $user->expert() ? "danger"
            : ($data["next"] == "TRUE" ? "danger"
                : "success");
        

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

        $map_file   = __ADIR__."/app/dist/img/igmap/".$serv->cfgRead("serverMap").".jpg";
        $map_path   = file_exists($map_file) ? "$ROOT/app/dist/img/igmap/".$serv->cfgRead("serverMap").".jpg"
            : "$ROOT/app/dist/img/igmap/ark.png";

        $string     = $_GET["type"] == "cards" ? $tpl->load_var()
            : $_GET["type"] == "img" ? '<img src="'.$map_path.'" style="border-radius: 25rem !important;border-top-width: 3px!important;height: 90px;width: 90px;background-color: #001f3f" class="border-'.$serv_color.'">'
                : $_GET["type"] == "imgtop" ? '<img src="'.$map_path.'" style="border-width: 3px!important;background-color: #001f3f" class="img-size-50 border border-'.$serv_color.'">'
                    : $tpl->load_var();

        echo $string;
        break;

    default:
        echo "Case not found";
    break;
}
$mycon->close();
