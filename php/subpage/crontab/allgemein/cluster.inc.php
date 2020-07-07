<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Cluster System
$json = $helper->file_to_json("app/json/panel/cluster_data.json");

foreach ($json as $k => $v) {
    // Cluster System (Synchronisation)

    // Suche Master
    $mcfg = null; $masterisset = false;
    foreach ($json[$k]["servers"] as $sk => $sv) {
        if ($sv["type"] == 1) $mcfg = $sv["server"];
    }
    if ($mcfg != null) {
        $masterisset = true;
        $mcfg = new server($mcfg);
    }

    // Syncronisiere Administratoren auf Slaves
    if ($json[$k]["sync"]["admin"] && $masterisset) {
        $mastercfg = file_get_contents($mcfg->dir_save(true)."/AllowedCheaterSteamIDs.txt");
        foreach ($json[$k]["servers"] as $sk => $sv) {
            if ($sv["type"] != 1) {
                $serv = new server($sv["server"]);
                $file = $serv->dir_save(true)."/AllowedCheaterSteamIDs.txt";
                file_put_contents($file, $mastercfg);
            }
        }
    }

    // Syncronisiere Mods auf Slaves
    if ($json[$k]["sync"]["mods"] && $masterisset) {
        foreach ($json[$k]["servers"] as $sk => $sv) {
            if ($sv["type"] != 1) {
                $serv = new server($sv["server"]);
                $serv->cfg_write("ark_GameModIds", $mcfg->cfg_read("ark_GameModIds"));
                $serv->cfg_save();
            }
        }
    }

    // Syncronisiere Konfigs auf Slaves
    if ($json[$k]["sync"]["konfig"] && $masterisset) {
        foreach ($json[$k]["servers"] as $sk => $sv) {

            //Lade inis & Infos in Array
            $mcfg->ini_load("Engine.ini", true);
            $ini["Engine.ini"] = $mcfg->ini_get_str();
            $mcfg->ini_load("GameUserSettings.ini", true);
            $ini["GameUserSettings.ini"] = $mcfg->ini_get_str();
            $mcfg->ini_load("Game.ini", false);
            $ini["Game.ini"] = $mcfg->ini_get_str();

            if ($sv["type"] != 1) {
                $serv = new server($sv["server"]);
                $serv->ini_get();
                foreach ($ini as $ck => $cv) {
                    $serv->ini_load($ck, false);
                    $path = $serv->ini_get_path();
                    file_put_contents($path, ini_save_rdy($cv));
                }
            }
        }
    }

    // Syncronisiere Whitelist auf Slaves
    if ($json[$k]["sync"]["whitelist"] && $masterisset) {
        foreach ($json[$k]["servers"] as $sk => $sv) {

            $slavecfg = new server($sv["server"]);
            $whitelistfile_master = $mcfg->dir_main()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";
            $whitelistfile_slave = $slavecfg->dir_main()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";


            if($slavecfg->check_rcon()) {
                $arr = file($whitelistfile_master);
                if(!is_array($arr)) $arr = [];
                for($i=0;$i<count($arr);$i++) {
                    $arr[$i] = trim($arr[$i]);
                }

                $arr_slave = file($whitelistfile_slave);
                if(!is_array($arr_slave)) $arr_slave = [];
                for($i=0;$i<count($arr_slave);$i++) {
                    $arr_slave[$i] = trim($arr_slave[$i]);
                }
                
                foreach($arr as $user) {
                    if(!in_array($user, $arr_slave)) {
                        $command = "AllowPlayerToJoinNoCheck $user";
                        $response = $slavecfg->exec_rcon($command);
                    }
                }
    
                foreach($arr_slave as $user) {
                    if(!in_array($user, $arr)) {
                        $command = "DisallowPlayerToJoinNoCheck $user";
                        $response = $slavecfg->exec_rcon($command);
                    }
                }
            }
            else {
                if(file_exists($whitelistfile_master)) file_put_contents($whitelistfile_slave, file_get_contents($whitelistfile_master));
            }
        }
    }

    // Setzte Optionen und Prüfe bei änderungen Starte den Server neu
    foreach ($json[$k]["servers"] as $sk => $sv) {

        //var_dump($json[$k]); echo "<hr>";
        $serv = new server($sv["server"]);
        $changes = false;

        $key = "arkopt_clusterid"; $val = $json[$k]["clusterid"];
        if ((!$serv->cfg_check($key)) || $serv->cfg_read($key) != $val) {
            $changes = true;
            $serv->cfg_write($key, $val);
        }

        $key = "arkopt_ClusterDirOverride"; $val = $serv->dir_cluster();
        if ((!$serv->cfg_check($key)) || $serv->cfg_read($key) != $val) {
            $changes = true;
            $serv->cfg_write($key, $val);
        }

        foreach ($json[$k]["opt"] as $ok => $ov) {
            $key = "ark_$ok"; $val = $ov;
            $val = str_replace(true, "True", $val); if ($val == "") $val = "False";
            if ((!$serv->cfg_check($key)) || $serv->cfg_read($key) != $val) {
                $changes = true;
                $serv->cfg_write($key, $val);
            }
        }

        if ($changes) {
            $serv->cfg_save();
           if ($ckonfig["clusterestart"] == 1) $serv->send_action("restart --warn --saveworld --noautoupdate", true);
        }

    }
}
?>