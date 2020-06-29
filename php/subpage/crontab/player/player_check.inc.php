<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/
$dir = dirToArray('remote/arkmanager/instances/');
    for ($i=0;$i<count($dir);$i++) {
        $checkit = false;
        if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
            $serv = new server($dir[$i]);

            $log = 'app/json/saves/chat_'.$serv->name().'.log';
            if (!file_exists($log)) file_put_contents($log, " ");
            $pl = 'app/json/saves/pl_'.$serv->name().'.players';
            if (!file_exists($pl)) file_put_contents($pl, " ");

            if ($serv->isinstalled() == "TRUE") {

                $path = $serv->dir_save();
                $container = null;
                $container = new Container();
                $container->LoadDirectory($path);
                $container->LinkPlayersAndTribes();

                $json_user = null;
                $json_tribe = null;

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

                if (!$json_user_enc = json_encode($json_user, JSON_INVALID_UTF8_SUBSTITUTE)) echo '<br />false!<br />';
                if (!$json_user_tribe = json_encode($json_tribe, JSON_INVALID_UTF8_SUBSTITUTE)) echo '<br />false!<br />';

                file_put_contents('app/json/saves/tribes_'.$serv->name().'.json', $json_user_tribe);
                file_put_contents('app/json/saves/player_'.$serv->name().'.json', $json_user_enc);

            }
            $container = null;
            $json_user = null;
            $json_tribe = null;

            // erstelle STATUS
            $log_file = 'sh/resp/'.$serv->name().'/status.log';
            $time = time();
            $file_time = filemtime($log_file);
            if (!file_put_contents($log_file.'2', sh_crontab(file_get_contents($log_file)))) exit;
            $log_file = 'sh/resp/'.$serv->name().'/status.log2';
            $log_file = 'sh/resp/'.$serv->name().'/status.log';
            $diff = $time - $file_time;
            if ($diff > $timediff['player']) {
                $checkit = true;
                if ($fn = fopen($log_file,"r")) {
                    $jsonfile = 'app/json/serverinfo/'.$serv->name().'.json';

                    $search  = array(
                        " ",
                        'Serveronline',
                        'ActivePlayers',
                        'Players',
                        'ServerPID',
                        'Serverrunning',
                        'Serverlistening',
                        'Serverversion',
                        'ServerbuildID'
                    );
                    $replace = array(
                        null,
                        'online',
                        'aplayers',
                        'players',
                        'pid',
                        'run',
                        'listening',
                        'version',
                        'bid'
                    );

                    $server['warning_count'] = 0;
                    $server['error_count'] = 0;
                    $server['error'] = null;
                    $server['error'][] = null;
                    $server['warning'] = null;
                    $server['warning'][] = null;
                    $server['online'] = 'NO';
                    $server['aplayers'] = 0;
                    $server['players'] = 0;
                    $server['pid'] = 'NO';
                    $server['run'] = 'NO';
                    $server['listening'] = 'NO';
                    $server['installed'] = $serv->isinstalled();


                    $ier = 0;
                    $wer = 0;

                    while(!feof($fn))  {
                        $result = fgets($fn);
                        $result = sh_crontab($result);
                        if (!strpos($result, "'status'")) {
                            if (strpos($result, 'http:') || strpos($result, 'steam:')) {
                                $exp = explode("link:", $result);
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                $key = "connect";
                                if ($exp[0] == "ARKServers") $key = "ARKServers";
                                $server[$key] = sh_crontab($exp[1]);;
                            }
                            elseif (strpos($result, 'ERROR')) {
                                $exp = explode("ERROR", $result);
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                $server['error'][$ier] = sh_crontab($exp[1]);;
                                $ier++;
                            }
                            elseif (strpos($result, 'WARN')) {
                                $exp = explode("WARN", $result);
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                $server['warning'][$ier] = sh_crontab($exp[1]);;
                                $wer++;
                            }
                            else {
                                $exp = explode(":", $result);
                                $key = str_replace($search, $replace, $exp[0]);
                                if ($key != "ServerName") $exp[1] = str_replace(" ", null, $exp[1]);
                                if ($key == "ServerName") {
                                    $str = $exp[1];
                                    $expstr = explode(" - (", $str);
                                    $server['version'] = str_replace(array(")", "v"), array(null, null), $expstr[1]);
                                }
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                if ($key != null) $server[$key] = sh_crontab($exp[1]);
                            }
                        }
                        $server['cfg'] = $name;
                    }
                    if ($ier > 0) $server['error_count'] = $ier;
                    if ($wer > 0) $server['warning_count'] = $wer;
                    $server['ARKServers'] = "https://arkservers.net/server/".$ip.":".$serv->cfg_read("ark_QueryPort");
                    fclose($fn);

                    $count_serv_max++;

                }

                if (!file_exists('sh/serv/jobs_ID_'.$serv->name().'.state')) file_put_contents('sh/serv/jobs_ID_'.$serv->name().'.state', 'TRUE');
                $serv_state = file_get_contents('sh/serv/jobs_ID_'.$serv->name().'.state');
                $serv_state = str_replace("\n", null, $serv_state);
                $serv_state = str_replace(" ", null, $serv_state);
                $log = 'sh/resp/'.$serv->name().'/last.log';
                $filetime = filemtime($log);
                $diff = time()-$filetime;
                if ($serv_state == 'TRUE' && $diff > 10) {
                    $server['next'] = 'FALSE';
                }
                else {
                    $server['next'] = 'TRUE';
                }
                if ($checkit) {
                    $helper->savejson_create($server, $jsonfile);
                }
            }

            if ($server['online'] == 'Yes' && $serv->cfg_read('ark_RCONEnabled') == 'True' && $serv->cfg_read('ark_ServerAdminPassword') != '') {
                $ip = $_SERVER['SERVER_ADDR'];
                $port = $serv->cfg_read('ark_RCONPort');
                $pw = $serv->cfg_read('ark_ServerAdminPassword');
                $rcon = new Rcon($ip, $port, $pw, 3);
                $rcon->connect();


                //serverCHAT
                $log = 'app/json/saves/chat_'.$serv->name().'.log';
                $file = file_get_contents($log);
                $file_old = $file;
                $rcon->send_command('getchat');
                $resp = $rcon->get_response();
                $exp = explode("\n", $resp);
                $string = null;
                for ($u=0;$u<count($exp);$u++) {
                    $string .= "\n".time()."(-/-)".$exp[$u];
                }
                $file = $file.$string;

                if(
                    strlen($file_old) < strlen($file) &&
                    strpos($resp, 'But no response') !== false &&
                    strpos($resp, 'Connection refused') !== false
                ) file_put_contents($log, $file);


                //serverPLAYER
                $player = array();
                $pl = 'app/json/saves/pl_'.$serv->name().'.players';
                $rcon->send_command('listplayers');
                $p_str = $rcon->get_response();
                if (strpos($p_str, 'No Players Connected') !== false) {
                    $player[0]['name'] = 'NO';
                    $player[0]['steamID'] = 0;
                    $p_str = json_encode($player, JSON_INVALID_UTF8_SUBSTITUTE);
                }
                else {
                    $pli = 0;
                    $p_str = str_replace("\r", null, $p_str);
                    $exp_1 = explode("\n", $p_str);
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
                file_put_contents($pl, $p_str);

                //disco
                $rcon->disconnect();
            }


            $helper->savejson_create($server, 'app/json/serverinfo/'.$name.'.json');
        }
    }
?>