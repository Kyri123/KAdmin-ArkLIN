<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Vars
$tpl_dir = 'app/template/core/scc/';
$tpl_dir_all = 'app/template/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "{::lang::php::scc::pagename}";
$urltop = '<li class="breadcrumb-item">Server Controll Center</li>';
$serv = new server("tiamat");

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

if (isset($_POST["add"])) {
    //$name = $_POST["name"];
    while (true) {
        $name = rndbit(5);
        $path = "remote/arkmanager/instances/".$name.".cfg";
        if (!file_exists($path)) {
            break;
        }
    }
    $arkserverroot = $servlocdir."server_ID_".$name;
    $logdir = $servlocdir."server_ID_".$name."_logs";
    $arkbackupdir = $servlocdir."server_ID_".$name."_backups";
    $ark_QueryPort = $_POST["port"];
    $ark_Port = $ark_QueryPort+2;
    $ark_RCONPort = $ark_QueryPort+4;

    $cfg = file_get_contents('app/data/template.cfg');
    $find = array(
        "{arkserverroot}",
        "{logdir}",
        "{arkbackupdir}",
        "{ark_Port}",
        "{ark_RCONPort}",
        "{ark_QueryPort}",
        "{ark_ServerAdminPassword}"
    );
    $repl = array(
        $arkserverroot,
        $logdir,
        $arkbackupdir,
        $ark_Port,
        $ark_RCONPort,
        $ark_QueryPort,
        md5(rndbit(10))
    );
    $cfg = str_replace($find, $repl, $cfg);
    if (!file_exists($path) && $ark_QueryPort > 1000) {
        if (file_put_contents($path, $cfg)) {
            $alert->code = 100;
            $resp = $alert->re();
            $serv = new server($name);
            $serv->cfg_save();
        } else {
            $alert->code = 1;
            $resp = $alert->re();
        }
    } else {
        $alert->code = 5;
        $resp = $alert->re();
    }
}

if (isset($_POST["del"])) {
    $serv = new server($_POST["cfg"]);
    $opt = array();
    if (isset($_POST["opt"])) $opt = $_POST["opt"];
    $server = $serv->name();

    $path = "app/json/serverinfo/$server.json";
    $data = $helper->file_to_json($path);
    $arkservdir = $serv->cfg_read("arkserverroot");
    $arklogdir = $serv->cfg_read("logdir");
    $arkbkdir = $serv->cfg_read("arkbackupdir");
    $serverstate = $serv->statecode();

    $path_cfg = "remote/arkmanager/instances/$server.cfg";
    $jobs->set($serv->name());
    if (file_exists($path_cfg) && ($serverstate == 0 || $serverstate == 3)) {
        if (unlink($path_cfg)) {
            if (file_exists("app/json/serverinfo/$server.json")) unlink("app/json/serverinfo/$server.json");
            if (file_exists("app/json/saves/tribes_$server.json")) unlink("app/json/saves/tribes_$server.json");
            if (file_exists("app/json/serverinfo/pl_$server.players")) unlink("app/json/serverinfo/pl_$server.players");
            if (file_exists("app/json/serverinfo/chat_$server.log")) unlink("app/json/serverinfo/chat_$server.log");
            if (file_exists("app/json/serverinfo/player_$server.json")) unlink("app/json/serverinfo/player_$server.json");
            if (file_exists("app/json/servercfg/jobs_$server.json")) unlink("app/json/servercfg/jobs_$server.json");
            if (in_array('deinstall', $opt)) {
                $jobs->shell("rm -R ".$arkservdir);
                $jobs->shell("rm -R ".$arklogdir);
                $jobs->shell("rm -R ".$arkbkdir);
            }
            $alert->code = 101;
            $alert->overwrite_text = "{::lang::php::scc::serverremoved}";
            $resp = $alert->re();
        } else {
            $alert->code = 1;
            $resp = $alert->re();
        }
    } else {
        if ($serverstate == 0 || $serverstate == 3) $alert->code = 7;
        if (!file_exists($path_cfg)) $alert->code = 8;
        $resp = $alert->re();
    }
}

$dir = dirToArray('remote/arkmanager/instances/');
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv = new server($dir[$i]);
        $serv->cluster_load();
        $list = new Template("list.htm", $tpl_dir);
        $list->load();

        $path = "app/json/serverinfo/" . $serv->name() . ".json";
        $data = $helper->file_to_json($path);

        $serverstate = $serv->statecode();
        $converted = convertstate($serverstate);
        $serv_state = $converted["str"];
        $serv_color = $converted["color"];

        $map_path = "app/dist/img/igmap/".$serv->cfg_read("serverMap").".jpg";
        if (!file_exists($map_path)) $map_path = "app/dist/img/igmap/ark.png";
        $list->r("map", $map_path);
        $list->r("cfg", $serv->name());
        $list->r("servername", $serv->cfg_read("ark_SessionName"));
        $list->r("clustername", $serv->cluster_name());
        $list->r("clusterid", $serv->cluster_clusterid());
        $list->rif ("ifincluster", $serv->cluster_in());
        $list->r("map_str", $serv->cfg_read("serverMap"));
        $list->r("state_str", $serv_state);
        $list->r("state_color", $serv_color);
        $list->r('servadress', $ip.":".$serv->cfg_read("ark_QueryPort"));
        $list->r('con_url', $data["connect"]);
        $list->r('ARKSERV', $data["ARKServers"]);

        $list->rif ("ifmodal", false);
        $cfglist .= $list->load_var();


        $list = new Template("list.htm", $tpl_dir);
        $list->load();

        $list->r("map", "app/dist/img/igmap/".$serv->cfg_read("serverMap").".jpg");
        $list->r("cfg", $serv->name());
        $list->r("servername", $serv->cfg_read("ark_SessionName"));
        $list->r("map_str", $serv->cfg_read("serverMap"));
        $list->r("state_str", $serv_state);
        $list->r("state_color", $serv_color);
        $list->r('servadress', $ip.":".$serv->cfg_read("ark_QueryPort"));
        $list->r('con_url', $data["connect"]);
        $list->r('ARKSERV', $data["ARKServers"]);

        $list->rif ("ifmodal", true);
        $cfgmlist .= $list->load_var();
    }
}


// lade in TPL
$tpl->r("list", $cfglist);
$tpl->r("list_modal", $cfgmlist);
$tpl->r("resp", $resp);
$content = $tpl->load_var();
$pageicon = "<i class=\"fa fa-server\" aria-hidden=\"true\"></i>";
$btns = '<a href="#" class="btn btn-success btn-icon-split rounded-0" data-toggle="modal" data-target="#addserver">
            <span class="icon text-white-50">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </span>
            <span class="text">{::lang::php::scc::btn_addserver}</span>
        </a>';
?>