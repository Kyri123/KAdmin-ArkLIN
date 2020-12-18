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

$ipath  = $KUTIL->path(__ADIR__.'/remote/arkmanager/instances/')["/path/"];
$dir    = scandir($ipath);

for ($i=0;$i<count($dir);$i++) {
    $ifile          = $KUTIL->path($ipath.$dir[$i])["/path"];

    // wenn es ein Verzeichnis ist skippe
    if(is_dir($ifile)) continue;

    $ifile_info     = pathinfo($ifile);
    $checkit        = false;

    if ($ifile_info['extension'] == "cfg" && strpos($ifile_info['filename'], "example") === false) {
        
        $serv       = new server($ifile_info["filename"]);
        if($serv !== false) {
            // Verbinde alle Chatlogs
            $path_tolog     = $KUTIL->path($serv->dirSavegames(true).'/Logs/')["/path/"];
            if(@file_exists($path_tolog) && is_dir($path_tolog)) {
                $dirlog     = scandir($path_tolog);
                $log        = " ";
                asort($dirlog);

                // hole alle Logs und speicher die zwischen
                $files  = [];
                $z      = 0;
                foreach($dirlog as $v) {
                    if(strpos($v, "ServerGame") !== false) {
                        $files[$z]["path"] = $KUTIL->path($path_tolog.$v)["/path"];
                        $files[$z]["time"] = filemtime($files[$z]["path"]);
                        $z++;
                    }
                }

                // Sortiere die Dateien
                usort($files, function($Item1, $Item2) {
                    return ($Item1['time'] - $Item2['time']);
                });

                // füge sie an dem String zusammen
                foreach($files as $k => $v) $log .= $KUTIL->fileGetContents($v["path"]);

                // Speicher Log
                $KUTIL->filePutContents($path_tolog.'ServerPanel.log', $log);
            }

            // erstelle Spielerliste (Online)
            if ($serv->checkRcon()) {
                // Verbinde zum RCON
                $ip         = $_SERVER['SERVER_ADDR'];
                $port       = $serv->cfgRead('ark_RCONPort');
                $pw         = $serv->cfgRead('ark_ServerAdminPassword');

                //serverPLAYER
                $player     = [];
                $pl         = $KUTIL->path(__ADIR__.'/app/json/saves/pl_'.$serv->name().'.players')["/path"];
                $p_str      = $serv->execRcon('listplayers', true);

                // wenn keine Spieler verbunden sind
                if (strpos($p_str, 'No Players Connected') !== false) {
                    $player[0]['name']      = 'NO';
                    $player[0]['steamID']   = 0;
                    $p_str                  = json_encode($player, JSON_INVALID_UTF8_SUBSTITUTE);
                }

                // wenn Spieler verbunden sind
                else {
                    // Splite liste
                    $pli    = 0;
                    $p_str  = str_replace("\r", null, $p_str);
                    $exp_1  = explode("\n", $p_str);

                    // verarbeite spielerliste
                    for ($y=0;$y<count($exp_1);$y++) if (strlen($exp_1[$y])>15) {

                        for ($x=0;$x<$serv->cfgRead('ark_MaxPlayers');$x++) $exp_1[$y] = str_replace($x.". ", null, $exp_1[$y]);
                        $exp_2 = explode(", ", $exp_1[$y]);

                        if(isset($exp_2[1])) {
                            $exp_2[1]                   = str_replace(" ", null, $exp_2[1]);
                            $player[$pli]['name']       = $exp_2[0];
                            $player[$pli]['steamID']    = $exp_2[1];
                            $pli++;
                        }
                    }
                }

                // Speicher Liste
                $KUTIL->filePutContents($pl, json_encode($player, JSON_INVALID_UTF8_SUBSTITUTE));
            }
        }
    }
}