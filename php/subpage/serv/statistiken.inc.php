<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$user->perm("$perm/statistiken/show")) {
    header("Location: /401");
    exit;
}

$pagename = '{::lang::php::sc::page::statistiken::pagename}';
$page_tpl = new Template('statistiken.htm', 'app/template/sub/serv/');
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::statistiken::pagename}</li>';
$server = $serv->name();

$resp = null;

// Speicher Optionen
if(isset($_POST["OPT"])) {
    setcookie($server."_offset", $_POST["OFFSET"]); $_COOKIE[$server."_offset"] = $_POST["OFFSET"];
    setcookie($server."_limit", $_POST["LIMIT"]); $_COOKIE[$server."_limit"] = $_POST["LIMIT"];
    setcookie($server."_order", $_POST["ORDER"]); $_COOKIE[$server."_order"] = $_POST["ORDER"];
    $resp .= $alert->rd(102);
}

$query_count = $mycon->query("SELECT * FROM ArkAdmin_statistiken WHERE `server` = '$server'")->numRows();
$limit = isset($_COOKIE[$server."_limit"]) ? $_COOKIE[$server."_limit"] : 50;
$pager_c = ceil($query_count / $limit);
$offset = 0;
if(isset($_COOKIE[$server."_offset"])) {
    $offset_new = $_COOKIE[$server."_offset"] * $limit;
    if($offset_new < $query_count) $offset = $offset_new;
}
$order = isset($_COOKIE[$server."_order"]) ? ($_COOKIE[$server."_order"] != "DESC" ? " ASC" : " DESC") : " DESC";

$pages_list = null;
for($i = 0; $i < $pager_c ; $i++) $pages_list .= "<option value='".$i."' ".($i == (isset($_COOKIE[$server."_offset"]) ? $_COOKIE[$server."_offset"] : 0) ? "Selected" : "").">".($i+1)."</option>";

// Erstelle Liste
$list["item"] = $list["modal"] = null;
$query = "SELECT * FROM ArkAdmin_statistiken WHERE `server` = '$server' ORDER BY `time`$order LIMIT $limit OFFSET $offset";
$mycon->query($query);
if($mycon->numRows() > 0) {
    $array = $mycon->fetchAll();
    foreach ($array as $k => $item) {
        $string = trim(utf8_encode($item["serverinfo_json"]));
        $string = str_replace("\n", null, $string);

        // wandel Informationen in Array
        $infos = json_decode($string, true);

        // Erstelle Templates
        $list_item = new Template("items.htm", "app/template/lists/serv/statistiken/");
        $list_item->load();
        $list_modal = new Template("modals.htm", "app/template/lists/serv/statistiken/");
        $list_modal->load();

        // TODO: Remove;
        //if($k == 1) var_dump($infos);

        // Status des Servers
        $serverstate = 0;
        if ($infos["listening"] == "Yes" && $infos["online"] == "Yes" && $infos["run"]) {
            $serverstate = 2;
        }
        elseif ($infos["listening"] == "No" && $infos["online"] == "No" && $infos["run"]) {
            $serverstate = 1;
        }
        elseif ($infos["listening"] == "Yes" && $infos["online"] == "No" && $infos["run"]) {
            $serverstate = 1;
        }
        elseif ($infos["listening"] == "No" && $infos["online"] == "Yes" && $infos["run"]) {
            $serverstate = 1;
        }

        $players = null;
        if(is_countable($infos["aplayersarr"])) foreach ($infos["aplayersarr"] as $pitem) {
            $on = round($pitem["time"] / 60, 0);
            $players[] = "<b>" . $pitem["name"] . "</b> - $on {::lang::allg::minutes}";
        }

        // Liste: Item
        $list_item->r("status_color", convertstate($serverstate)["color"]);
        $list_item->r("status_text", convertstate($serverstate)["str"]);
        $list_item->r("date", converttime($item["time"]));
        $list_item->r("player", (is_countable($infos["aplayersarr"]) ? count($infos["aplayersarr"]) : 0) . '/' . $infos["players"]);
        $list_item->r("id", $item["id"]);

        $list["item"] .= $list_item->load_var();

        // Liste: Modal
        $list_modal->r("date", converttime($item["time"]));
        $list_modal->r("status_color", convertstate($serverstate)["color"]);
        $list_modal->r("id", $item["id"]);
        $list_modal->r("ServerMap", $infos["ServerMap"] != "" ? $infos["ServerMap"] : "{::lang::php::sc::page::statistiken::notonline}");
        $list_modal->r("ServerName", $infos["ServerName"] != "" ? $infos["ServerName"] : "{::lang::php::sc::page::statistiken::notonline}");
        $list_modal->r("ping", $infos["ping"] != "" ? $infos["ping"] : "{::lang::php::sc::page::statistiken::notonline}");
        $list_modal->r("player", $players != null ? implode("</br>", $players) : "{::lang::php::sc::page::statistiken::notonline}");

        $list["modal"] .= $list_modal->load_var();
    }
}


$page_tpl->load();

$page_tpl->r('__L_10', $limit == 10 ? "SELECTED" : "");
$page_tpl->r('__L_50', $limit == 50 ? "SELECTED" : "");
$page_tpl->r('__L_100', $limit == 100 ? "SELECTED" : "");
$page_tpl->r('__L_250', $limit == 250 ? "SELECTED" : "");
$page_tpl->r('ASC', $order == " ASC" ? "SELECTED" : "");
$page_tpl->r('DESC', $order == " DESC" ? "SELECTED" : "");
$page_tpl->r('pages', $pages_list);
$page_tpl->r('cdata', $query_count);

$page_tpl->r('resp', $resp);
$page_tpl->r('list', $list["item"]);
$page_tpl->r('modal_list', $list["modal"]);
$panel = $page_tpl->load_var();

$player = null;

