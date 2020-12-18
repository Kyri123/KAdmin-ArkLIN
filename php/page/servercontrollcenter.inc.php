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

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if(!$session_user->perm("servercontrollcenter/show")) {
    header("Location: /401"); exit;
}


// Vars
$tpl_dir        = __ADIR__.'/app/template/core/scc/';
$tpl_dir_all    = __ADIR__.'/app/template/all/';
$setsidebar     = false;
$cfglist        = $map_path = $cfgmlist = null;
$pagename       = "{::lang::php::scc::pagename}";
$urltop         = '<li class="breadcrumb-item">Server Controll Center</li>';
$serv           = new server("tiamat");

//tpl
$tpl            = new Template('tpl.htm', $tpl_dir);

if (isset($_POST["add"]) && $session_user->perm("servercontrollcenter/create")) {
    //prüfe ob das Maximum erreicht wurde
    if($maxpanel_server > (count(scandir($KUTIL->path(__ADIR__."/remote/arkmanager/instances")["/path"]))-2)) {
        while (true) {
            $name       = rndbit(5);
            $path       = __ADIR__."/remote/arkmanager/instances/".$name.".cfg";
            if (!@file_exists($path)) break;
        }

        $arkserverroot  = $servlocdir."server_ID_".$name;
        $logdir         = $servlocdir."server_ID_".$name."_logs";
        $arkbackupdir   = $servlocdir."server_ID_".$name."_backups";
        $ark_QueryPort  = $_POST["port"][1];
        $ark_Port       = $_POST["port"][0];
        $ark_RCONPort   = $_POST["port"][2];
        $cfg            = $KUTIL->fileGetContents(__ADIR__.'/app/data/template.cfg');
        $find           = [
            "{arkserverroot}",
            "{logdir}",
            "{arkbackupdir}",
            "{ark_Port}",
            "{ark_RCONPort}",
            "{ark_QueryPort}",
            "{ark_ServerAdminPassword}"
        ];
        $repl           = [
            $arkserverroot,
            $logdir,
            $arkbackupdir,
            $ark_Port,
            $ark_RCONPort,
            $ark_QueryPort,
            md5(rndbit(10))
        ];
        $cfg            = str_replace($find, $repl, $cfg);
        if(
            ($_POST["port"][0] != "" && is_numeric($_POST["port"][0])) &&
            ($_POST["port"][1] != "" && is_numeric($_POST["port"][1])) &&
            ($_POST["port"][2] != "" && is_numeric($_POST["port"][2]))
        ) {
            if (!file_exists($path) && $ark_QueryPort > 1000) {
                if ($KUTIL->filePutContents($path, $cfg)) {
                    $resp   .= $alert->rd(100);
                    $serv   = new server($name);
                    $serv->cfgSave();

                    // Speicher Rechte für den Benutzer
                    if(isset($_SESSION["id"]) && @file_exists(__ADIR__."/app/json/user/".md5($_SESSION["id"]).".permissions.json")) {
                        $perm_file                          = $KUTIL->fileGetContents(__ADIR__."/app/json/user/permissions_servers.tpl.json");
                        $perm_file                          = str_replace("{cfg}", $name, $perm_file);
                        $default                            = $helper->stringToJson($perm_file);
                        $default[$name]["is_server_admin"]  = 1;
                        $user_permissions                   = $session_user->permissions;

                        if(isset($user_permissions["server"][$name])) {
                            $user_permissions["server"][$name]  = array_replace_recursive($default[$name], $user_permissions["server"][$name]);
                        }
                        else {
                            $user_permissions["server"]         += $default;
                        }

                        $helper->saveFile($user_permissions, __ADIR__."/app/json/user/".md5($_SESSION["id"]).".permissions.json");
                    }
                } else {
                    $resp .= $alert->rd(1);
                }
            } else {
                $resp .= $alert->rd(5);
            }
        }
        else {
            $resp .= $alert->rd(2);
        }
    }
    else {
        $alert->code = 37;
        $alert->r("max_server", $maxpanel_server);
        $resp .= $alert->re();
    }
}
elseif (isset($_POST["add"])) {
    $resp .= $alert->rd(99);
}

// Entfernen von Server
if (isset($_POST["del"]) && $session_user->perm("servercontrollcenter/delete")) {
    $serv           = new server($_POST["cfg"]);
    $opt            = isset($_POST["opt"]) ? (is_array($_POST["opt"]) ? $_POST["opt"] : []) : [];
    $server         = $serv->name();

    // Setze Vars
    $path           = __ADIR__."/app/json/serverinfo/$server.json";
    $data           = $helper->fileToJson($path);
    $arkservdir     = $serv->cfgRead("arkserverroot");
    $arklogdir      = $serv->cfgRead("logdir");
    $arkbkdir       = $serv->cfgRead("arkbackupdir");
    $serverstate    = $serv->stateCode();

    $path_cfg       = $KUTIL->path(__ADIR__."/remote/arkmanager/instances/$server.cfg")["/path"];
    $jobs->set($serv->name());
    if (@file_exists($path_cfg) && ($serverstate == 0 || $serverstate == 3)) {
        if (unlink($path_cfg)) {
            // Entferne alle Dateien von dem Server
            $KUTIL->removeFile(__ADIR__."/app/json/serverinfo/$server.json");
            $KUTIL->removeFile(__ADIR__."/app/json/saves/tribes_$server.json");
            $KUTIL->removeFile(__ADIR__."/app/json/serverinfo/pl_$server.players");
            $KUTIL->removeFile(__ADIR__."/app/json/serverinfo/chat_$server.log");
            $KUTIL->removeFile(__ADIR__."/app/json/serverinfo/player_$server.json");
            $KUTIL->removeFile(__ADIR__."/app/json/servercfg/jobs_$server.json");

            // Wenn gewünscht entferne Verzeichnisse vom Server
            if (in_array('deinstall', $opt)) {
                $jobs->shell("rm -R $arkservdir");
                $jobs->shell("rm -R $arklogdir");
                $jobs->shell("rm -R $arkbkdir");
            }

            // Lösche Datensätze aus der DB
            $mycon->query("DELETE FROM `ArkAdmin_player` WHERE `server`=?", $serv->name());
            $mycon->query("DELETE FROM `ArkAdmin_shell` WHERE `server`=?", $serv->name());
            $mycon->query("DELETE FROM `ArkAdmin_jobs` WHERE `server`=?", $serv->name());
            $mycon->query("DELETE FROM `ArkAdmin_tribe` WHERE `server`=?", $serv->name());
            $mycon->query("DELETE FROM `ArkAdmin_statistiken` WHERE `server`=?", $serv->name());

            $alert->code = 101;
            $alert->overwrite_text = "{::lang::php::scc::serverremoved}";
            $resp .= $alert->re();
        } else {
            $resp .= $alert->rd(1);
        }
    } else {
        $resp .= $alert->rd($serverstate == 0 || $serverstate == 3 ? 7 : 8);
    }
}
elseif (isset($_POST["del"])) {
    $resp .= $alert->rd(99);
}

$dir = dirToArray($KUTIL->path(__ADIR__.'/remote/arkmanager/instances/')["/path"]);
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv   = new server($dir[$i]);
        $list   = new Template("list.htm", $tpl_dir);
        $path   = $KUTIL->path(__ADIR__."/app/json/serverinfo/" . $serv->name() . ".json")["/path"];
        $data   = $helper->fileToJson($path);

        $serv->clusterLoad();
        $list->load();


        $serverstate    = $serv->stateCode();
        $converted      = convertstate($serverstate);
        $serv_state     = $converted["str"];
        $serv_color     = $converted["color"];
        $mapbg          = @file_exists(__ADIR__.'/app/dist/img/backgrounds/' . $serv->cfgRead('serverMap') . '.jpg')    ? '/app/dist/img/backgrounds/' . $serv->cfgRead('serverMap') . '.jpg'   : '/app/dist/img/backgrounds/bg.jpg';
        $mapimg         = @file_exists(__ADIR__.'/app/dist/img/igmap/' . $serv->cfgRead('serverMap') . '.jpg')          ? '/app/dist/img/igmap/' . $serv->cfgRead('serverMap') . '.jpg'         : '/app/dist/img/logo/ark.png';
        $map_path       = !file_exists($map_path) ? __ADIR__."/app/dist/img/igmap/ark.png" : $KUTIL->path(__ADIR__."/app/dist/img/igmap/".$serv->cfgRead("serverMap").".jpg")["/path"];

        $list->r("map", $map_path);
        $list->r("cfg", $serv->name());
        $list->r("servername", $serv->cfgRead("ark_SessionName"));
        $list->r("clustername", $serv->clusterRead("name"));
        $list->r("clusterid", $serv->clusterRead("id"));
        $list->rif ("ifincluster", $serv->clusterIn());
        $list->r("map_str", $serv->cfgRead("serverMap"));
        $list->r("state_str", $serv_state);
        $list->r("state_color", $serv_color);
        $list->r('servadress', $ip.":".$serv->cfgRead("ark_QueryPort"));
        $list->r('con_url', isset($data["connect"]) ? $data["connect"] : null);
        $list->r('ARKSERV', isset($data["ARKServers"]) ? $data["ARKServers"] : null);

        $list->r('bg_img', $mapbg);
        $list->r('server_img', $mapimg);

        $list->rif ("ifmodal", false);
        $cfglist .= $list->load_var();

        $list = new Template("list.htm", $tpl_dir);
        $list->load();

        $list->r("map", __ADIR__."/app/dist/img/igmap/".$serv->cfgRead("serverMap").".jpg");
        $list->r("cfg", $serv->name());
        $list->r("servername", $serv->cfgRead("ark_SessionName"));
        $list->r("map_str", $serv->cfgRead("serverMap"));
        $list->r("state_str", $serv_state);
        $list->r("state_color", $serv_color);
        $list->r('servadress', $ip.":".$serv->cfgRead("ark_QueryPort"));
        $list->r('con_url', isset($data["connect"]) ? $data["connect"] : "");
        $list->r('ARKSERV', isset($data["ARKServers"]) ? $data["ARKServers"] : "");

        $list->rif ("ifmodal", true);
        $cfgmlist .= $list->load_var();
    }
}

$resp       .= $maxpanel_server > (count(scandir(__ADIR__."/remote/arkmanager/instances"))-2) ? null : $alert->rd(309);

// lade in TPL
$tpl->load();
$tpl->r("list", $cfglist);
$tpl->r("list_modal", $cfgmlist);
$tpl->r("resp", $resp);
$tpl->rif("not_max", $maxpanel_server > (count(scandir(__ADIR__."/remote/arkmanager/instances"))-2));
$content    = $tpl->load_var();
$pageicon   = "<i class=\"fa fa-server\" aria-hidden=\"true\"></i>";
if($session_user->perm("servercontrollcenter/create") && $maxpanel_server > (count(scandir(__ADIR__."/remote/arkmanager/instances"))-2)) $btns = '<span  data-toggle="popover_action" data-content="{::lang::scc::tooltip::create::text}" data-original-title="{::lang::scc::tooltip::create::title}">
                                                                <a href="#" class="btn btn-outline-success btn-icon-split rounded-0" data-toggle="modal" data-target="#addserver" title="">
                                                                    <span class="icon">
                                                                        <i class="fas fa-plus" aria-hidden="true"></i>
                                                                    </span>
                                                                </a>
                                                            </span>';
