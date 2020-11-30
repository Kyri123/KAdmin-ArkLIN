<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$ipath = __ADIR__.'/remote/arkmanager/instances/';
$dir = scandir($ipath);

for ($i=0;$i<count($dir);$i++) {
    $ifile = $ipath.$dir[$i];
    // wenn es ein Verzeichnis ist skippe
    if(is_dir($ifile)) continue;

    $ifile_info = pathinfo($ipath.$dir[$i]);
    $checkit = false;

    if ($ifile_info['extension'] == "cfg" && strpos($ifile_info['filename'], "example") !== true) {
        
        $serv = new server($ifile_info["filename"]);
        // Verbinde alle Chatlogs
        $path_tolog = $serv->dir_save(true).'/Logs/';
        if(file_exists($path_tolog) && is_dir($path_tolog)) {
            $dirlog = scandir($path_tolog);
            asort($dirlog);
            $log = null;

            // hole alle Logs und speicher die zwischen
            $files = array(); $z = 0;
            foreach($dirlog as $v) {
                if(strpos($v, "ServerGame") !== false) {
                    $files[$z]["path"] = $path_tolog.$v;
                    $files[$z]["time"] = filemtime($path_tolog.$v);

                    $z++;
                }
            }

            // Sortiere die Dateien
            usort($files, function($Item1, $Item2) {
                return $Item1['time'] - $Item2['time'];
            });

            // füge sie an dem String zusammen
            foreach($files as $k => $v) {
                $log .= $KUTIL->fileGetContents($v["path"]);
            }
            
            // Speicher Log
            $log_file = $path_tolog.'ServerPanel.log';
            file_put_contents($log_file, $log);
        }

        // erstelle Spielerliste (Online)
        if ($serv->check_rcon()) {
            // Verbinde zum RCON
            $ip = $_SERVER['SERVER_ADDR'];
            $port = $serv->cfg_read('ark_RCONPort');
            $pw = $serv->cfg_read('ark_ServerAdminPassword');
            $rcon = new Rcon($ip, $port, $pw, 3);
            $rcon->connect();

            //serverPLAYER
            //Überarbeiten???
            $player = array();
            $pl = __ADIR__.'/app/json/saves/pl_'.$serv->name().'.players';
            $rcon->send_command('listplayers');
            $p_str = $rcon->get_response();

            // wenn keine Spieler verbunden sind
            if (strpos($p_str, 'No Players Connected') !== false) {
                $player[0]['name'] = 'NO';
                $player[0]['steamID'] = 0;
                $p_str = json_encode($player, JSON_INVALID_UTF8_SUBSTITUTE);
            }

            // wenn Spieler verbunden sind
            else {
                // Splite liste
                $pli = 0;
                $p_str = str_replace("\r", null, $p_str);
                $exp_1 = explode("\n", $p_str);
                // verarbeite spielerliste
                for ($y=0;$y<count($exp_1);$y++) {
                    if (strlen($exp_1[$y])>15) {
                        for ($x=0;$x<$serv->cfg_read('ark_MaxPlayers');$x++) {
                            $exp_1[$y] = str_replace($x.". ", null, $exp_1[$y]);
                        }
                        $exp_2 = explode(", ", $exp_1[$y]);
                        $exp_2[1] = str_replace(" ", null, $exp_2[1]);

                        $player[$pli]['name'] = $exp_2[0];
                        $player[$pli]['steamID'] = $exp_2[1];
                        $pli++;
                        $p_str = json_encode($player, JSON_INVALID_UTF8_SUBSTITUTE);
                    }
                }
            }

            // Speicher Liste
            file_put_contents($pl, $p_str);

            //Trenne Verbindung vom RCON
            $rcon->disconnect();
        }
    }
}
