<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg    = $_POST['cfg'];
$case   = $_GET['case'];
$serv   = new server($cfg);
$perm   = "server/$cfg";
$serv->clusterLoad();

switch ($case) {
    // CASE: Mod verschieben
    case "push":
        $bool   = false;
        $resp   = "";
        if($session_user->perm("$perm/mods/changeplace")) {
            $cancel     = false;
            $action     = $_POST["action"];
            $modid      = $_POST["modid"];
            // change order
            $mods       = explode(',', $serv->cfgRead('ark_GameModIds'));
            // Suche Nach Mods
            for ($i=0;$i<count($mods);$i++) {
                if ($mods[$i] == $modid) {
                    // Move Mod nach oben
                    if ($action == 'down') {
                        $iafter         = $i+1;
                        if(!isset($mods[$iafter])) {
                            $cancel     = true;
                            break;
                        }
                        $modid_after    = $mods[$iafter];
                        $mods[$iafter]  = $modid;
                        $mods[$i]       = $modid_after;
                        break;
                    }
                    // Move Mod nach unten
                    if ($action == 'up') {
                        $ibefore        = $i-1;
                        if(!isset($mods[$ibefore])) {
                            $cancel = true;
                            break;
                        }
                        $modid_before       = $mods[$ibefore];
                        $mods[$ibefore]     = $modid;
                        $mods[$i]           = $modid_before;
                        break;
                    }
                }
            }

            $mod_builder = implode(',', $mods);
            // saver

            $serv->cfgWrite('ark_GameModIds', $mod_builder);
            if(!$cancel) {
                $resp   .= $alert->rd($serv->cfgSave() ? 102 : 1);
                $bool   = true;
            }
            else {
                $resp   .= $alert->rd(16);
            }
        }
        else {
            $resp       .= $alert->rd(99);
        }
        echo json_encode(array("success" => $bool, "msg" => $resp));
        break;

    // CASE: Mod verschieben
    case "pushto":
        $bool = false;
        $resp = "";
        if($session_user->perm("$perm/mods/changeplace")) {
            $cancel     = false;
            $to         = intval($_POST["to"]);
            $modid      = $_POST["modid"];
            // change order
            $mods       = explode(',', $serv->cfgRead('ark_GameModIds'));
            // Suche Nach Mods
            for ($i=0;$i<count($mods);$i++) {
                if ($mods[$i] == $modid) {
                    // Move Mod nach oben
                    $modid_new  = $mods[$to];
                    $mods[$to]  = $modid;
                    $mods[$i]   = $modid_new;
                    break;
                }
            }

            $mod_builder = implode(',', $mods);
            // saver

            $serv->cfgWrite('ark_GameModIds', $mod_builder);
            if(!$cancel) {
                $resp   .= $alert->rd($serv->cfgSave() ? 102 : 1);
                $bool   = true;
            }
            else {
                $resp   .= $alert->rd(16);
            }
        }
        else {
            $resp .= $alert->rd(99);
        }
        echo json_encode(array("success" => $bool, "msg" => $resp));
        break;

    case "remove":
        $bool = false;
        $resp = "";
        if($session_user->perm("$perm/mods/remove")) {
            $cancel     = false;
            $modid      = $_POST["modid"];
            // change order
            $mods       = explode(',', $serv->cfgRead('ark_GameModIds'));
            // Suche Nach Mods
            for ($i=0;$i<count($mods);$i++)
                if ($mods[$i] == $modid) {
                    $id = $mods[$i];
                    $mods[$i] = 'removed';
                    break;
                }

            // Modlist Builder
            for ($i=0;$i<count($mods);$i++)
                if ($mods[$i] == 'removed') {
                    if ($ckonfig['uninstall_mod'] == 1) {
                        $jobs->set($serv->name());
                        $jobs->arkmanager('uninstallmod ' . $id);
                    }
                    $removed = true;
                    unset($mods[$i]);
                    break;
                }

            $mod_builder = implode(',', $mods);
            // saver

            $serv->cfgWrite('ark_GameModIds', $mod_builder);
            if(!$cancel) {
                $resp .= $alert->rd($serv->cfgSave() ? 101 : 1);
                $bool = true;
            }
            else {
                $resp .= $alert->rd(16);
            }
        }
        else {
            $resp .= $alert->rd(99);
        }
        echo json_encode(array("success" => $bool, "msg" => $resp));
        break;


    case "remove_installed":
        $bool = false;
        $resp = "";
        if($session_user->perm("$perm/mods/remove")) {
            $path   = $serv->dirMain()."/ShooterGame/Content/Mods/".$_POST["modid"];
            $bool   = true;

            if (@file_exists($path)) {
                // Deinstalliere Mod
                $jobs->set($serv->name());
                $jobs->arkmanager("uninstallmod ".$_POST["modid"]);

                // Melde Locale Mod deinstalliert
                $alert->overwrite_text = "{::lang::php::sc::page::mods::mod_removed_dir}";
                $resp   .= $alert->rd(101);
            }
            else {
                $resp   .= $alert->rd(1);
            }
        }
        else {
            $resp .= $alert->rd(99);
        }
        echo json_encode(array("success" => $bool, "msg" => $resp));
        break;
    default:
        echo "Case not found";
        break;
}
$mycon->close();
