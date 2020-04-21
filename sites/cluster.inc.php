<?php

// Vars
$tpl_dir = 'tpl/cluster/';
$setsidebar = false;
$cfglist = null;
$pagename = "Cluster System";
$urltop = '<li class="breadcrumb-item">Cluster System</li>';

$tpl = new Template("tpl.htm", $tpl_dir);
$tpl->load();

$clusterjson_path = "data/panel/cluster_data.json";

if(!file_exists($clusterjson_path)) if(!file_put_contents($clusterjson_path, "[]")) die;
$json = $helper->file_to_json($clusterjson_path);

//remove Cluster

if(isset($url[3]) && $url[2] == "removecluster") {
    $key = $url[3];
    if(isset($json[$key])) unset($json[$key]);
    $helper->savejson_exsists($json, $clusterjson_path);
    header("Location: /cluster"); exit;
}

if(isset($url[4]) && $url[2] == "remove") {
    $key = $url[3];
    $cfg = $url[4];
    $array = array_column($json[$key]["servers"], 'server');
    foreach ($array as $k => $v) {
        if($v == $cfg) {
            $i = $k; break;
        }
    }
    if(isset($json[$key]["servers"][$i])) unset($json[$key]["servers"][$i]);
    $helper->savejson_exsists($json, $clusterjson_path);
    header("Location: /cluster"); exit;
}

if(isset($url[5]) && $url[2] == "settype") {
    $key = $url[3];
    $cfgkey = $url[4];
    $to = $url[5];
    if($to > 0) {
        $to = 1;
        $i = 0;
        $f = false;
        $array = array_column($json[$key]["servers"], 'type');
        foreach ($array as $k => $v) {
            if($v == $to) {
                $i = $k; $f = true; break;
            }
        }
        if(!$f) {
            $json[$key]["servers"][$cfgkey]["type"] = 1;
        }
        else {
            $json[$key]["servers"][$i]["type"] = 0;
            $json[$key]["servers"][$cfgkey]["type"] = 1;
        }
    }
    else {
        $json[$key]["servers"][$cfgkey]["type"] = 0;
    }
    $helper->savejson_exsists($json, $clusterjson_path);
    header("Location: /cluster"); exit;
}

//Füge server zum Cluster
if(isset($_POST["addserver"])) {
    $key = $_POST["key"];
    $cfg = $_POST["server"];
    if($cfg != "") {
        $no = true;
        foreach ($json as $mk => $mv) {
            if(array_search($cfg, array_column($json[$mk]["servers"], 'server')) !== FALSE) $no = false;
        }
        if($no) {
            $i = count($json[$key]["servers"]);
            $cluster =  $json[$key]["name"];
            $json[$key]["servers"][$i]["server"] = $cfg;
            $json[$key]["servers"][$i]["type"] = 0;
            $server = new server($cfg);

            if($helper->savejson_exsists($json, $clusterjson_path)) {
                $resp = meld('success', "Server <b>".$server->cfg_read("ark_SessionName")."</b> zum Cluster <b>".$cluster."</b> hinzufüget", 'Erfolgreich!', null);
            }
            else {
                $resp = meld('danger', 'Cluster konnte nicht gespeichert werden', 'Fehler!', null);
            }
        }
        else {
            $resp = meld('danger', 'Server ist bereits in einem Cluster System', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Kein Server wurde ausgewählt!', 'Fehler!', null);
    }
}


// Editiere einen Cluster
if(isset($_POST["editcluster"])) {
    //set vars
    $i = $_POST["key"];
    $cluster = $_POST["name"];
    $clustermd5 = md5($_POST["name"]);

    //sync opt
    $sync["admin"] = true; if(!isset($_POST["admin"])) $sync["admin"] = false;
    $sync["mods"] = true; if(!isset($_POST["mods"])) $sync["mods"] = false;
    $sync["konfig"] = true; if(!isset($_POST["konfig"])) $sync["konfig"] = false;

    // options / rules
    $opt["NoTransferFromFiltering"] = true; if(!isset($_POST["NoTransferFromFiltering"])) $opt["NoTransferFromFiltering"] = false;
    $opt["NoTributeDownloads"] = true; if(!isset($_POST["NoTributeDownloads"])) $opt["NoTributeDownloads"] = false;
    $opt["PreventDownloadSurvivors"] = true; if(!isset($_POST["PreventDownloadSurvivors"])) $opt["PreventDownloadSurvivors"] = false;
    $opt["PreventUploadSurvivors"] = true; if(!isset($_POST["PreventUploadSurvivors"])) $opt["PreventUploadSurvivors"] = false;
    $opt["PreventDownloadItems"] = true; if(!isset($_POST["PreventDownloadItems"])) $opt["PreventDownloadItems"] = false;
    $opt["PreventUploadItems"] = true; if(!isset($_POST["PreventUploadItems"])) $opt["PreventUploadItems"] = false;
    $opt["PreventDownloadDinos"] = true; if(!isset($_POST["PreventDownloadDinos"])) $opt["PreventDownloadDinos"] = false;
    $opt["PreventUploadDinos"] = true; if(!isset($_POST["PreventUploadDinos"])) $opt["PreventUploadDinos"] = false;

    if($cluster != null && ($clustermd5 == $json[$i]["clusterid"] || array_search($clustermd5, array_column($json, 'clusterid')) === FALSE)) {
        $json[$i]["name"] = $cluster;
        $json[$i]["clusterid"] = $clustermd5;
        $json[$i]["sync"] = $sync;
        $json[$i]["opt"] = $opt;

        if($helper->savejson_exsists($json, $clusterjson_path)) {
            $resp = meld('success', "Cluster <b>$cluster</b> Bearbeitet! ClusterID: <b>$clustermd5</b>", 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Cluster konnte nicht gespeichert werden', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Kein Clusternamen oder dieser exsistiert bereits!', 'Fehler!', null);
    }
}

// erstelle ein Cluster
if(isset($_POST["add"])) {
    //set vars
    $cluster = $_POST["name"];
    $clustermd5 = md5($_POST["name"]);

    //sync opt
    $sync["admin"] = true; if(!isset($_POST["admin"])) $sync["admin"] = false;
    $sync["mods"] = true; if(!isset($_POST["mods"])) $sync["mods"] = false;
    $sync["konfig"] = true; if(!isset($_POST["konfig"])) $sync["konfig"] = false;

    // options / rules
    $opt["NoTransferFromFiltering"] = true; if(!isset($_POST["NoTransferFromFiltering"])) $opt["NoTransferFromFiltering"] = false;
    $opt["NoTributeDownloads"] = true; if(!isset($_POST["NoTributeDownloads"])) $opt["NoTributeDownloads"] = false;
    $opt["PreventDownloadSurvivors"] = true; if(!isset($_POST["PreventDownloadSurvivors"])) $opt["PreventDownloadSurvivors"] = false;
    $opt["PreventUploadSurvivors"] = true; if(!isset($_POST["PreventUploadSurvivors"])) $opt["PreventUploadSurvivors"] = false;
    $opt["PreventDownloadItems"] = true; if(!isset($_POST["PreventDownloadItems"])) $opt["PreventDownloadItems"] = false;
    $opt["PreventUploadItems"] = true; if(!isset($_POST["PreventUploadItems"])) $opt["PreventUploadItems"] = false;
    $opt["PreventDownloadDinos"] = true; if(!isset($_POST["PreventDownloadDinos"])) $opt["PreventDownloadDinos"] = false;
    $opt["PreventUploadDinos"] = true; if(!isset($_POST["PreventUploadDinos"])) $opt["PreventUploadDinos"] = false;

    if($cluster != null && (count($json) < 1 || array_search($clustermd5, array_column($json, 'clusterid')) === FALSE)) {

        $clusterarray["name"] = $cluster;
        $clusterarray["clusterid"] = $clustermd5;
        $clusterarray["sync"] = $sync;
        $clusterarray["opt"] = $opt;
        $clusterarray["servers"] = array();

        if(array_push($json, $clusterarray)) {
            if($helper->savejson_exsists($json, $clusterjson_path)) {
                $resp = meld('success', "Cluster <b>$cluster</b> erstellt! ClusterID: <b>$clustermd5</b>", 'Erfolgreich!', null);
            }
            else {
                $resp = meld('danger', 'Cluster konnte nicht gespeichert werden', 'Fehler!', null);
            }
        }
        else {
            $resp = meld('danger', 'Cluster konnte nicht erzeugt werden', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Kein Clusternamen oder dieser exsistiert bereits!', 'Fehler!', null);
    }
}


//Fixed (bis ich ne andere lösung gefunden habe......)
// TODO: Fixe
// TODO: [{"server":"TestCluster01","type":0},{"server":"TestCluster03","type":0},{"server":"TestCluster02","type":0}]
// TODO Wird nach entfernen eines Datensatzes zu {"0":{"server":"TestCluster01","type":0},"1":{"server":"TestCluster03","type":0}}
$i = 0;
foreach ($json as $mk => $mv) {
    $old = $json[$mk]["servers"];
    $json[$mk]["servers"] = array();
    foreach ($old as $k => $v) {
        $array["server"] = $v["server"];
        $array["type"] = $v["type"];
        array_push($json[$mk]["servers"], $array);
        $i++;
    }
}
$helper->savejson_exsists($json, $clusterjson_path);

$json = $helper->file_to_json($clusterjson_path);
$list = null;
foreach ($json as $mk => $mv) {
    $listtpl = new Template("list_clusters.htm", $tpl_dir);
    $listtpl->load();

    $serverlist = null;
    $alert = null;


    $count = 0; if(isset($json[$mk]["servers"])) $count = count($json[$mk]["servers"]);

    if($count > 0) {
        $x = 0;
        foreach ($json[$mk]["servers"] as $key) {
            $listserv = new Template("_lists.htm", $tpl_dir);
            $listserv->load();
            $listserv->replif("ifserver", true);
            $server = new server($key["server"]);
            $data = $server->readdata();

            $color_type = "green";
            $master = false;
            if($key["type"] > 0) {
                $color_type = "blue";
                $master = true;
            }

            $listserv->replif("ifmaster", $master);
            $listserv->repl("servername", $server->cfg_read("ark_SessionName"));
            $listserv->repl("cfg", $server->show_name());
            $listserv->repl("cfgkey", $x);
            $listserv->repl("key", $mk);
            $listserv->repl("curr", $data->aplayers);
            $listserv->repl("type", $clustertype[$key["type"]]);
            $listserv->repl("max", $server->cfg_read("ark_MaxPlayers"));
            $listserv->repl("color", convertstate($server->get_state())["color"]);
            $listserv->repl("color_type", $color_type);
            $listserv->repl("state", convertstate($server->get_state())["str"]);

            $x++;

            $serverlist .= $listserv->loadin();
        }
    }

    $list_sync = null;
    $list_opt = null;

    //sync
    if($json[$mk]["sync"]["admin"]) $list_sync .= "<tr><td>Administratoren</td></tr>";
    if($json[$mk]["sync"]["mods"]) $list_sync .= "<tr><td>Mods</td></tr>";
    if($json[$mk]["sync"]["konfig"]) $list_sync .= "<tr><td>Konfigurationen</td></tr>";

    $listtpl->replif("Administratoren", $json[$mk]["sync"]["admin"]);
    $listtpl->replif("Mods", $json[$mk]["sync"]["mods"]);
    $listtpl->replif("Konfigurationen", $json[$mk]["sync"]["konfig"]);
    //opt
    if($json[$mk]["opt"]["NoTransferFromFiltering"]) $list_opt .= "<tr><td>NoTransferFromFiltering</td></tr>";
    if($json[$mk]["opt"]["NoTributeDownloads"]) $list_opt .= "<tr><td>NoTributeDownloads</td></tr>";
    if($json[$mk]["opt"]["PreventDownloadSurvivors"]) $list_opt .= "<tr><td>PreventDownloadSurvivors</td></tr>";
    if($json[$mk]["opt"]["PreventUploadSurvivors"]) $list_opt .= "<tr><td>PreventUploadSurvivors</td></tr>";
    if($json[$mk]["opt"]["PreventDownloadItems"]) $list_opt .= "<tr><td>PreventDownloadItems</td></tr>";
    if($json[$mk]["opt"]["PreventUploadItems"]) $list_opt .= "<tr><td>PreventUploadItems</li></tr>";
    if($json[$mk]["opt"]["PreventDownloadDinos"]) $list_opt .= "<tr><td>PreventDownloadDinos</td></tr>";
    if($json[$mk]["opt"]["PreventUploadDinos"]) $list_opt .= "<tr><td>PreventUploadDinos</td></tr>";


    $listtpl->replif("NoTransferFromFiltering", $json[$mk]["opt"]["NoTransferFromFiltering"]);
    $listtpl->replif("NoTributeDownloads", $json[$mk]["opt"]["NoTributeDownloads"]);
    $listtpl->replif("PreventDownloadSurvivors", $json[$mk]["opt"]["PreventDownloadSurvivors"]);
    $listtpl->replif("PreventUploadSurvivors", $json[$mk]["opt"]["PreventUploadSurvivors"]);
    $listtpl->replif("PreventDownloadItems", $json[$mk]["opt"]["PreventDownloadItems"]);
    $listtpl->replif("PreventUploadItems", $json[$mk]["opt"]["PreventUploadItems"]);
    $listtpl->replif("PreventDownloadDinos", $json[$mk]["opt"]["PreventDownloadDinos"]);
    $listtpl->replif("PreventUploadDinos", $json[$mk]["opt"]["PreventUploadDinos"]);

    if(count($json[$mk]["servers"]) == 0 || array_search(1, array_column($json[$mk]["servers"], 'type')) === FALSE) {
        $txt = "Achtung es wurde kein Master oder Server gefunden! Bitte Prüfe dies.";
        $alert = meld_full('danger', nl2br($txt), 'Kein Master oder Server gesetzt!', null, "mb-0");
    }

    if($serverlist == null) $serverlist = "<tr><td colspan='5'>Kein Server wurde gesetzt | <a href=\"javascript:void()\" data-toggle=\"modal\" data-target=\"#addservtocluster".$json[$mk]["clusterid"]."\">Server Hinzufügen</a> </td></tr>";
    if($list_sync == null) $list_sync = "<tr><td colspan='5'>Synchronisation wurde nicht gesetzt | <a href=\"javascript:void()\" data-toggle=\"modal\" data-target=\"#options".$json[$mk]["clusterid"]."\">Einstellungen</a> </td></tr>";
    if($list_opt == null) $list_opt = "<tr><td colspan='5'>Keine Optionen wurde gesetzt | <a href=\"javascript:void()\" data-toggle=\"modal\" data-target=\"#options".$json[$mk]["clusterid"]."\">Einstellungen</a> </td></tr>";

    $listtpl->repl("alert", $alert);
    $listtpl->repl("key", $mk);
    $listtpl->repl("list_sync", $list_sync);
    $listtpl->repl("list_opt", $list_opt);
    $listtpl->repl("servercount", $count);
    $listtpl->repl("serverlist", $serverlist);
    $listtpl->repl("clustername", $json[$mk]["name"]);
    $listtpl->repl("clusterid", $json[$mk]["clusterid"]);
    $list .= $listtpl->loadin();
}

$cfg_array = $helper->file_to_json("data/serv/all.json");

foreach ($cfg_array["cfgs"] as $key) {
    $cfg = str_replace(".cfg", null, $key);
    $server = new server($cfg);
    $no = true;
    foreach ($json as $mk => $mv) {
        if(array_search($cfg, array_column($json[$mk]["servers"], 'server')) !== FALSE) $no = false;
    }
    if($no) $sel_serv .= "<option value='$cfg'>".$server->cfg_read("ark_SessionName")."</option>";
}


// TODO: remove
$resp .= meld_full('info', nl2br($txt_alert), 'Alpha Version', null);
$tpl->repl("list", $list);
$tpl->repl("resp", $resp);
$tpl->repl("sel_serv", $sel_serv);
$content = $tpl->loadin();
$pageicon = "<i class=\"fas fa-random\"></i>";
$btns = '<a href="#" class="btn btn-success btn-icon-split rounded-0" data-toggle="modal" data-target="#addcluster">
            <span class="icon text-white-50">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </span>
            <span class="text">Cluster Erstellen</span>
        </a>';
?>