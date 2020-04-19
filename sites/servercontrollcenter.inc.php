<?php

// Vars
$tpl_dir = 'tpl/scc/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "Server Controll Center";
$urltop = '<li class="breadcrumb-item">Server Controll Center</li>';
$serv = new server("tiamat");

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();
if(isset($_POST["add"])) {
    $name = $_POST["name"];
    $arkserverroot = $servlocdir."server_ID_".$name;
    $logdir = $servlocdir."server_ID_".$name."_logs";
    $arkbackupdir = $servlocdir."server_ID_".$name."_backups";
    $ark_QueryPort = $_POST["port"];
    $ark_Port = $ark_QueryPort+2;
    $ark_RCONPort = $ark_QueryPort+4;

    $cfg = file_get_contents('data/template.cfg');
    $find = array(
        "{arkserverroot}",
        "{logdir}",
        "{arkbackupdir}",
        "{ark_Port}",
        "{ark_RCONPort}",
        "{ark_QueryPort}"
    );
    $repl = array(
        $arkserverroot,
        $logdir,
        $arkbackupdir,
        $ark_Port,
        $ark_RCONPort,
        $ark_QueryPort
    );
    $cfg = str_replace($find, $repl, $cfg);
    $path = "remote/arkmanager/instances/".$name.".cfg";
    if(!file_exists($path) && $ark_QueryPort > 1000) {
        if(file_put_contents($path, $cfg)) {
            $resp = meld('success', 'Server erstellt!', 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Konnte Server nicht erstellen.', 'Fehler!', null);
        }
    } else {
        $resp = meld('danger', 'Konnte Server nicht erstellen da dieser bereits exsistiert oder Der Port unter 1000 ist.', 'Fehler!', null);
    }
}

if(isset($_POST["del"])) {
    $serv = new server($_POST["cfg"]);
    $opt = array();
    if(isset($_POST["opt"])) $opt = $_POST["opt"];
    $server = $serv->show_name();

    $path = "data/serv/$server.json";
    $data = $helper->file_to_json($path);
    $arkservdir = $serv->cfg_read("arkserverroot");
    $arklogdir = $serv->cfg_read("logdir");
    $arkbkdir = $serv->cfg_read("arkbackupdir");

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

    $path_cfg = "remote/arkmanager/instances/$server.cfg";
    $jobs->set($serv->show_name());
    if(file_exists($path_cfg) && ($serverstate == 0 || $serverstate == 3)) {
        if(unlink($path_cfg)) {
            if(file_exists("data/serv/$server.json")) unlink("data/serv/$server.json");
            if(file_exists("data/saves/tribes_$server.json")) unlink("data/saves/tribes_$server.json");
            if(file_exists("data/serv/pl_$server.players")) unlink("data/serv/pl_$server.players");
            if(file_exists("data/serv/chat_$server.log")) unlink("data/serv/chat_$server.log");
            if(file_exists("data/serv/player_$server.json")) unlink("data/serv/player_$server.json");
            if(file_exists("data/config/jobs_$server.json")) unlink("data/config/jobs_$server.json");
            $resp = meld('success', 'Server Entfernt', 'Erfolgreich!', null);
            if(in_array('deinstall', $opt)) {
                $jobs->create_shell("rm -R ".$arkservdir);
                $jobs->create_shell("rm -R ".$arklogdir);
                $jobs->create_shell("rm -R ".$arkbkdir);
            }
        }
        else {
            $resp = meld('danger', 'Konnte Server nicht Löschen.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Konnte Server nicht finden oder dieser ist noch Online.', 'Fehler!', null);
    }
}

$dir = dirToArray('remote/arkmanager/instances/');
for($i=0;$i<count($dir);$i++) {
    if($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv = new server($dir[$i]);
        $list = new Template("list.htm", $tpl_dir);
        $list->load();


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
        $map_path = "dist/img/igmap/".$serv->cfg_read("serverMap").".jpg";
        if(!file_exists($map_path)) $map_path = "dist/img/igmap/ark.png";
        $list->repl("map", $map_path);
        $list->repl("cfg", $serv->show_name());
        $list->repl("servername", $serv->cfg_read("ark_SessionName"));
        $list->repl("map_str", $serv->cfg_read("serverMap"));
        $list->repl("state_str", $serv_state);
        $list->repl("state_color", $serv_color);
        $list->repl('servadress', $ip.":".$serv->cfg_read("ark_QueryPort"));
        $list->repl('con_url', $data["connect"]);
        $list->repl('ARKSERV', $data["ARKServers"]);

        $list->replif("ifmodal", false);
        $cfglist .= $list->loadin();


        $list = new Template("list.htm", $tpl_dir);
        $list->load();

        $list->repl("map", "dist/img/igmap/".$serv->cfg_read("serverMap").".jpg");
        $list->repl("cfg", $serv->show_name());
        $list->repl("servername", $serv->cfg_read("ark_SessionName"));
        $list->repl("map_str", $serv->cfg_read("serverMap"));
        $list->repl("state_str", $serv_state);
        $list->repl("state_color", $serv_color);
        $list->repl('servadress', $ip.":".$serv->cfg_read("ark_QueryPort"));
        $list->repl('con_url', $data["connect"]);
        $list->repl('ARKSERV', $data["ARKServers"]);

        $list->replif("ifmodal", true);
        $cfgmlist .= $list->loadin();
    }
}


// lade in TPL
$tpl->repl("list", $cfglist);
$tpl->repl("list_modal", $cfgmlist);
$tpl->repl("resp", $resp);
$content = $tpl->loadin();
$pageicon = "<i class=\"fa fa-server\" aria-hidden=\"true\"></i>";
$btns = '<a href="#" class="btn btn-success btn-icon-split rounded-0" data-toggle="modal" data-target="#addserver">
            <span class="icon text-white-50">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </span>
            <span class="text">Server Hinzufügen</span>
        </a>';
?>