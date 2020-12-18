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

// Cluster System
$json   = $helper->fileToJson(__ADIR__."/app/json/panel/cluster_data.json");

foreach ($json as $k => $v) {
    // Cluster System (Synchronisation)

    // Suche Master
    $mcfg           = null;
    $masterisset    = false;
    foreach ($json[$k]["servers"] as $sk => $sv) if ($sv["type"] == 1) $mcfg = $sv["server"];

    if ($mcfg !== null) {
        $masterisset    = true;
        $mcfg           = new server($mcfg);
    }

    // Syncronisiere Administratoren auf Slaves
    if ($json[$k]["sync"]["admin"] && $masterisset && $mcfg !== false) {
        $mastercfg  = $KUTIL->fileGetContents($mcfg->dirSavegames(true)."/AllowedCheaterSteamIDs.txt");
        if($mastercfg !== false) {
            foreach ($json[$k]["servers"] as $sk => $sv) if ($sv["type"] != 1) {
                $serv   = new server($sv["server"]);
                $file   = $serv->dirSavegames(true)."/AllowedCheaterSteamIDs.txt";
                $KUTIL->filePutContents($file, $mastercfg);
            }
        }
    }

    // Syncronisiere Mods auf Slaves
    if ($json[$k]["sync"]["mods"] && $masterisset && $mcfg !== false) {
        foreach ($json[$k]["servers"] as $sk => $sv) if ($sv["type"] != 1) {
            $serv = new server($sv["server"]);
            $serv->cfgWrite("ark_GameModIds", $mcfg->cfgRead("ark_GameModIds"));
            $serv->cfgSave();
        }
    }

    // Syncronisiere Konfigs auf Slaves
    if ($json[$k]["sync"]["konfig"] && $masterisset && $mcfg !== false) {
        foreach ($json[$k]["servers"] as $sk => $sv) {

            //Lade inis & Infos in Array
            $mcfg->iniLoad("Engine.ini", true);
            $ini["Engine.ini"]              = $mcfg->iniGetString();
            $mcfg->iniLoad("GameUserSettings.ini", true);
            $ini["GameUserSettings.ini"]    = $mcfg->iniGetString();
            $mcfg->iniLoad("Game.ini", false);
            $ini["Game.ini"]                = $mcfg->iniGetString();

            if ($sv["type"] != 1) {
                $serv = new server($sv["server"]);
                if($serv !== false) foreach ($ini as $ck => $cv) {
                    $serv->iniLoad($ck, false);
                    $path   = $serv->iniGetPath();
                    if($path != "" && $path != null) $KUTIL->filePutContents($path, ini_save_rdy($cv));
                }
            }
        }
    }
 
    // Syncronisiere Whitelist auf Slaves
    if ($json[$k]["sync"]["whitelist"] && $masterisset) {
        foreach ($json[$k]["servers"] as $sk => $sv) {
            $slavecfg               = new server($sv["server"]);
            if($slavecfg !== false) {
                $whitelistfile_master   = $KUTIL->path($mcfg->dirMain()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt")["/path"];
                $whitelistfile_slave    = $KUTIL->path($slavecfg->dirMain()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt")["/path"];


                if($slavecfg->checkRcon()) {
                    $arr                                    = file($whitelistfile_master);
                    if(!is_array($arr)) $arr                = [];
                    for($i=0;$i<count($arr);$i++) $arr[$i]  = trim($arr[$i]);

                    $arr_slave = file($whitelistfile_slave);
                    if(!is_array($arr_slave)) $arr_slave                = [];
                    for($i=0;$i<count($arr_slave);$i++) $arr_slave[$i]  = trim($arr_slave[$i]);

                    foreach($arr as $user) {
                        if(!in_array($user, $arr_slave)) {
                            $command    = "AllowPlayerToJoinNoCheck $user";
                            $response   = $slavecfg->execRcon($command);
                        }
                    }

                    foreach($arr_slave as $user) {
                        if(!in_array($user, $arr)) {
                            $command    = "DisallowPlayerToJoinNoCheck $user";
                            $response   = $slavecfg->execRcon($command);
                        }
                    }
                }
                else {
                    $KUTIL->filePutContents($whitelistfile_slave, $KUTIL->fileGetContents($whitelistfile_master));
                }
            }
        }
    }

    // Setzte Optionen und Prüfe änderungen -> Starte den Server neu (if Konfig = true/1)
    foreach ($json[$k]["servers"] as $sk => $sv) {

        //var_dump($json[$k]); echo "<hr>";
        $serv       = new server($sv["server"]);
        if($serv !== false) {
            $changes    = false;

            $key        = "arkopt_clusterid";
            $val        = $json[$k]["clusterid"];
            if ((!$serv->cfgKeyExists($key)) || $serv->cfgRead($key) != $val) {
                $changes    = true;
                $serv->cfgWrite($key, $val);
            }

            $key        = "arkopt_ClusterDirOverride";
            $val        = $serv->dirCluster();
            if ((!$serv->cfgKeyExists($key)) || $serv->cfgRead($key) != $val) {
                $changes    = true;
                $serv->cfgWrite($key, $val);
            }

            foreach ($json[$k]["opt"] as $ok => $ov) {
                $key    = "ark_$ok";
                $val    = ($ov == "") ? "False" : str_replace(true, "True", $ov);
                if ((!$serv->cfgKeyExists($key)) || $serv->cfgRead($key) != $val) {
                    $changes    = true;
                    $serv->cfgWrite($key, $val);
                }
            }

            if ($changes) {
                $serv->cfgSave();
                if (intval($ckonfig["clusterestart"]) === 1) $serv->sendAction("restart --warn --saveworld --noautoupdate", true);
            }
        }
    }
}