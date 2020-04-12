<?php
require('js_inz.inc.php');
$cfg = $_GET['cfg'];
$serv = new server($cfg);
$tpl = new Template("server.htm", "tpl/serv/");
$tpl->load();
$i = false;
$path = "data/serv/" . $serv->show_name() . ".json";
$data = $helper->file_to_json($path);

// Status
$serverstate = 0;
if($serv->check_install() == "FALSE") {
    $serverstate = 3;
}
elseif($data["listening"] == "Yes" && $data["online"] == "Yes" && $data["run"] == "Yes") {
    $serverstate = 2;
}
elseif($data["listening"] == "No" && $data["online"] == "NO" && $data["run"] == "Yes") {
    $serverstate = 1;
}
elseif($data["listening"] == "Yes" && $data["online"] == "NO" && $data["run"] == "Yes") {
    $serverstate = 1;
}
elseif($data["listening"] == "No" && $data["online"] == "Yes" && $data["run"] == "Yes") {
    $serverstate = 1;
}

// State Card
if($serverstate == 0) {
    $serv_state = "Offline";
    $serv_color = "danger";
}
elseif ($serverstate == 1) {
    $serv_state = "Startet";
    $serv_color = "info";
}
elseif ($serverstate == 2) {
    $serv_state = "Online";
    $serv_color = "success";
}
elseif ($serverstate == 3) {
    $serv_state = "Nicht Installiert";
    $serv_color = "warning";
}

// Spieler Card
$pl_pla = $data["aplayers"];
$pl_plmax = $serv->cfg_read("ark_MaxPlayers");
$pl_plpro = $pl_pla / $pl_plmax * 100;
if($serverstate < 2) {
    $pl_modal = null;
    $pl_disabled = 'disabled';
    $pl_btntxt = "Server muss Online sein!";
}
else {
    $pl_modal = 'data-toggle="modal" data-target="#playerlist_modal"';
    $pl_disabled = null;
    $pl_btntxt = "Spieler Zeigen";
}

// Action Card
if($data["next"] == "TRUE") {
    $action_state = "Gesperrt";
    $action_btntxt = "Server muss Frei sein!";
    $action_modal = null;
    $action_color = "danger";
}
elseif($data["next"] == "FALSE") {
    $action_state = "Frei";
    $action_btntxt = "Aktion auswählen <i class=\"fas fa-arrow-circle-right\"></i>";
    $action_modal = 'data-toggle="modal" data-target="#action"';
    $action_color = "success";
}

$tpl->repl("serv_state", $serv_state);
$tpl->repl("serv_color", $serv_color);
$tpl->repl("serv_pid", $serv_pid);

$tpl->repl("pl_plpro", $pl_plpro);
$tpl->repl("pl_disabled", $pl_disabled);
$tpl->repl("pl_plmax", $pl_plmax);
$tpl->repl("pl_pla", $pl_pla);
$tpl->repl("pl_modal", $pl_modal);
$tpl->repl("pl_btntxt", $pl_btntxt);

$tpl->repl("action_color", $action_color);
$tpl->repl("action_state", $action_state);
$tpl->repl("action_btntxt", $action_btntxt);
$tpl->repl("action_modal", $action_modal);

if($_GET["type"] == "cards") $string = $tpl->loadin();
if($_GET["type"] == "img") $string = '<img src="/dist/img/logo/ark.png" style="border-radius: 25rem!important;height: 90px;width: 90px;background-color: #001f3f" class="border-'.$serv_color.'">';

echo $string;


?>