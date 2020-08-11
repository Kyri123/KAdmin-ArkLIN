<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$ipath = 'remote/arkmanager/instances/';
$dir = scandir($ipath);
    for ($i=0;$i<count($dir);$i++) {
        $ifile = $ipath.$dir[$i];
        // wenn es ein Verzeichnis ist skippe
        if(is_dir($ifile)) continue;

        $ifile_info = pathinfo($ipath.$dir[$i]);
        $checkit = false;

        if ($ifile_info['extension'] == "cfg" && strpos($ifile_info['filename'], "example") !== true) {
            //erstelle Server Klasse
            $serv = new server($ifile_info['filename']);

            // Erstelle logdateien
            $log = 'app/json/saves/chat_'.$serv->name().'.log';
            if (!file_exists($log)) file_put_contents($log, " ");
            $pl = 'app/json/saves/pl_'.$serv->name().'.players';
            if (!file_exists($pl)) file_put_contents($pl, " ");

            if ($serv->isinstalled() == "TRUE") {

                // lade Spielstände & Informationen
                $path = $serv->dir_save();
                $container = null;
                $container = new Container();
                $container->LoadDirectory($path);
                $container->LinkPlayersAndTribes();

                // lösche inhalt von vars
                $json_user = $json_tribe = null;

                // holen Stamm Informationen
                $z = 0;
                foreach($container->Tribes as $tribe)
                {
                    $json_tribe[$z]['Id'] = $tribe->Id;
                    $json_tribe[$z]['Name'] = $tribe->Name;
                    $json_tribe[$z]['OwnerId'] = $container->Tribes[0]->Owner->SteamId;
                    $json_tribe[$z]['FileCreated'] = $tribe->FileCreated;
                    $json_tribe[$z]['FileUpdated'] = $tribe->FileUpdated;
                    $json_tribe[$z]['Members'] = $tribe->Members;
                    $z++;
                }

                // holen Spieler Informationen
                $z = 0;
                foreach($container->Players as $Players)
                {
                    $json_user[$z]['Id'] = $Players->Id;
                    $json_user[$z]['SteamId'] = $Players->SteamId;
                    $json_user[$z]['SteamName'] = $Players->SteamName;
                    $json_user[$z]['CharacterName'] = $Players->CharacterName;
                    $json_user[$z]['Level'] = $Players->Level;
                    $json_user[$z]['ExperiencePoints'] = $Players->ExperiencePoints;
                    $json_user[$z]['TotalEngramPoints'] = $Players->TotalEngramPoints;
                    $json_user[$z]['FirstSpawned'] = $Players->FirstSpawned;
                    $json_user[$z]['FileCreated'] = $Players->FileCreated;
                    $json_user[$z]['FileUpdated'] = $Players->FileUpdated;
                    $json_user[$z]['TribeId'] = $Players->TribeId;
                    $z++;
                }

                // Speicher Informationen / encode
                if ($json_user_enc = json_encode($json_user, JSON_INVALID_UTF8_SUBSTITUTE)) file_put_contents('app/json/saves/tribes_'.$serv->name().'.json', $json_user_tribe);
                if ($json_user_tribe = json_encode($json_tribe, JSON_INVALID_UTF8_SUBSTITUTE)) file_put_contents('app/json/saves/player_'.$serv->name().'.json', $json_user_enc);
            }
            // lösche inhalt von vars
            $container = $json_user = $json_tribe = null;

            // erstelle STATUS
            $raw = 'app/json/serverinfo/raw_'.$serv->name().'.json';
            if (file_exists($raw)) {
                $checkit = true; $server = null;

                $jsonfile = 'app/json/serverinfo/'.$serv->name().'.json';
                $raw_jsonfile = $helper->file_to_json('app/json/serverinfo/raw_'.$serv->name().'.json');
                $server = $helper->file_to_json($jsonfile);
                
                // setzte Default daten
                $server['warning_count'] = 0;
                $server['error_count'] = 0;
                $server['error'] = null;
                $server['error'][] = null;
                $server['warning'] = null;
                $server['warning'][] = null;
                $server['aplayers'] = 0;
                $server['players'] = 0;
                $server['pid'] = 'No';
                $server['run'] = 'No';
                $server['online'] = 'No';
                $server['listening'] = 'No';
                $server['installed'] = $serv->isinstalled();

                // verarbeite AA-Server response
                foreach($raw_jsonfile as $k => $v) $server[$k] = $v;
                $server['run'] = ($server['run'] === true) ? "Yes" : "No";

                // lese Status für die Ausführung von Aktionen
                if (!file_exists('sh/serv/jobs_ID_'.$serv->name().'.state')) file_put_contents('sh/serv/jobs_ID_'.$serv->name().'.state', 'TRUE');
                $serv_state = trim(file_get_contents('sh/serv/jobs_ID_'.$serv->name().'.state'));
                $log = 'sh/resp/'.$serv->name().'/last.log';
                if ($serv_state == 'TRUE' && timediff($log, ($webserver['config']['ShellIntervall'] / 1000 + 3))) {
                    $server['next'] = 'FALSE';
                }
                else {
                    $server['next'] = 'TRUE';
                }

                //schreibe Informationen zur verarbeitung
                if ($checkit) {
                    $helper->savejson_create($server, $jsonfile);
                }
            }
            
            // Verbinde alle Chatlogs
            $path_tolog = $serv->dir_save(true).'/Logs/'; echo "<br>";
            if(file_exists($path_tolog) && is_dir($path_tolog)) {
                $dirlog = scandir($path_tolog);
                arsort($dirlog);
                $log = null;

                // hole alle Logs und füge sie an dem String
                foreach($dirlog as $v) {
                    if(strpos($v, "ServerGame") !== false) {
                        $file = $path_tolog.$v; 
                        $log .= file_get_contents($file);
                    }
                } 

                // Speicher Log
                $log_file = $path_tolog.'ServerPanel.log';
                echo file_put_contents($log_file, $log);
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
                $pl = 'app/json/saves/pl_'.$serv->name().'.players';
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


            $helper->savejson_create($server, 'app/json/serverinfo/'.$name.'.json');
        }
    }
?>