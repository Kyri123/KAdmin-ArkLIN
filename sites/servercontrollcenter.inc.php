<?php

// Vars
$tpl_dir = 'tpl/scc/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "Server Controll Center";
$urltop = '<li class="breadcrumb-item">Server Controll Center</li>';

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();
if(isset($_POST["add"])) {
    $name = $_POST["name"];
    $arkserverroot = $servlocdir."server_ID_".$name;
    $logdir = $servlocdir."server_ID_".$name."_logs";
    $arkbackupdir = $servlocdir."server_ID_".$name."_backups";
    $ark_QueryPort = $_POST["port"];
    $ark_Port = $qport+2;
    $ark_RCONPort = $qport+4;

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

if($_POST["del"]) {
    $serv = new server($_POST["cfg"]);
    $opt = array();
    if(isset($_POST["opt"])) $opt = $_POST["opt"];
    $path_cfg = "remote/arkmanager/instances/".$serv->show_name().".cfg";

    $path = "data/serv/" . $serv->show_name() . ".json";
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


    if(file_exsists($path_cfg) && ($serverstate == 0 || $serverstate == 3)) {
        if(unlink($path_cfg)) {
            $resp = meld('success', 'Server Entfernt', 'Fehler!', null);
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

        $list->repl("map", "dist/img/igmap/".$serv->cfg_read("serverMap").".jpg");
        $list->repl("cfg", $serv->show_name());
        $list->repl("servername", $serv->cfg_read("ark_SessionName"));
        $list->repl("map_str", $serv->cfg_read("serverMap"));
        $list->repl("state_str", $serv_state);
        $list->repl("state_color", $serv_color);
        $list->repl('servadress', $ip.":".$serv->cfg_read("ark_QueryPort"));
        $list->repl('con_url', $connect = $data["connect"]);
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
        $list->repl('con_url', $connect = $data["connect"]);
        $list->repl('ARKSERV', $data["ARKServers"]);

        $list->replif("ifmodal", true);
        $cfgmlist .= $list->loadin();
    }
}


// lade in TPL
$tpl->repl("list", $cfglist);
$tpl->repl("list_modal", $cfgmlist);
$content = $tpl->loadin();
$btns = '<a href="#" class="btn btn-success btn-icon-split" data-toggle="modal" data-target="#addserver">
            <span class="icon text-white-50">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </span>
            <span class="text">Server Hinzufügen</span>
        </a>';
?>