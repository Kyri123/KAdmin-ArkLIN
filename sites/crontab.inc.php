<?php


$tpl_crontab = new Template('crontab.htm', 'tpl/system/');
$tpl_crontab->load();
$re = null;
$root_dir = $_SERVER['DOCUMENT_ROOT'];

$pagename = 'Crontab';
$job = $url[2];
$re = null;
$time = time();

$timediff['shell'] = 15;
$timediff['player'] = 3;

//function
function filter_end ($str) {
    if(strpos($str, 'Yes') !== false) {
        return 'Yes';
    }
    else {
        return 'No';
    }
}

//Für Serverstatus
if($job == "status") {
    $re .= "Lese Status...";
    $mainfile = null;
    $dir = dirToArray('remote/arkmanager/instances/');
    for($i=0;$i<count($dir);$i++) {
        $server[$i]['cfg'] = $dir[$i];
        $re .= 'Read... ' . $dir[$i] . '<br />';
        if($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
            $c_serv = new server($dir[$i]);
            
            $shell_path = 'sh/serv/check_server_ID_'.$c_serv->show_name().'.sh';
            // Shell Command
            $shell_command = 'echo "" > '.$root_dir.'/sh/serv/check_server_ID_'.$dir[$i].'.sh ;arkmanager status @'.$c_serv->show_name().' > '.$root_dir.'/sh/resp/'.$c_serv->show_name().'/status.log ;exit';

            // Erstelle Dateien & Verzeichnis...
            if(!file_exists('sh/resp/'.$c_serv->show_name())) mkdir('sh/resp/'.$c_serv->show_name());
            if(!file_exists('remote/serv/server_ID_'.$c_serv->show_name().'_logs')) mkdir('remote/serv/server_ID_'.$c_serv->show_name().'_logs');
            if(!file_exists('sh/resp/'.$c_serv->show_name().'/last.log')) file_put_contents('sh/resp/'.$c_serv->show_name().'/last.log', null);
            if(!file_exists('data/serv/'.$c_serv->show_name().'.json')) file_put_contents('data/serv/'.$c_serv->show_name().'.json', '{}');
            if(!file_exists('sh/serv/jobs_ID_'.$c_serv->show_name().'.sh')) file_put_contents('sh/serv/jobs_ID_'.$c_serv->show_name().'.sh', null);
            if(!file_exists('sh/serv/sub_jobs_ID_'.$c_serv->show_name().'.sh')) file_put_contents('sh/serv/sub_jobs_ID_'.$c_serv->show_name().'.sh', null);

            if(!file_exists($shell_path)) file_put_contents($shell_path, null);
            $file_time = filemtime($log_file);
            $diff = $time - $file_time;
            if($diff > $timediff['shell']) {
                file_put_contents($shell_path, $shell_command);
            }

            $mainfile .= 'screen -d -m -t check_server_ID_'.$dir[$i].' sh '.$root_dir.'/sh/serv/check_server_ID_'.$dir[$i].".sh\n";
            $mainfile .= 'screen -d -m -t jobs_ID_'.$dir[$i].' sh '.$root_dir.'/sh/serv/jobs_ID_'.$dir[$i].".sh\n";
            $mainfile .= 'screen -d -m -t sub_jobs_ID_'.$dir[$i].' sh '.$root_dir.'/sh/serv/sub_jobs_ID_'.$dir[$i].".sh\n";

        }
    }
    //Schreibe main.sh
    $mainfile = str_replace("\r", null, $mainfile);
    file_put_contents('sh/main.sh', $mainfile);
}


//Für Spielerliste & Auswertung von Server Status
elseif($job == "player") {
    $dir = dirToArray('remote/arkmanager/instances/');
    for($i=0;$i<count($dir);$i++) {
        if($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
            $serv = new server($dir[$i]);

            $log = 'data/saves/chat_'.$serv->show_name().'.log';
            if(!file_exists($log)) file_put_contents($log, " ");
            $pl = 'data/saves/pl_'.$serv->show_name().'.players';
            if(!file_exists($pl)) file_put_contents($pl, " ");

            $path = $serv->get_save_dir();
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
                $json_tribe[$z]['OwnerId'] = $tribe->OwnerId;
                $json_tribe[$z]['FileCreated'] = $tribe->FileCreated;
                $json_tribe[$z]['FileUpdated'] = $tribe->FileUpdated;
                $z++;
            }

            $z = 0;
            foreach($container->Players as $Players)
            {
                #print_r($Players->Id);
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


            if(!$json_user_enc = json_encode($json_user, JSON_INVALID_UTF8_SUBSTITUTE)) echo '<br />false!<br />';
            if(!$json_user_tribe = json_encode($json_tribe, JSON_INVALID_UTF8_SUBSTITUTE)) echo '<br />false!<br />';


            file_put_contents('data/saves/tribes_'.$serv->show_name().'.json', $json_user_tribe);
            file_put_contents('data/saves/player_'.$serv->show_name().'.json', $json_user_enc);

            $container = null;
            $json_user = null;
            $json_tribe = null;

            echo 'done for:'.$serv->show_name();



            // erstelle STATUS
            $log_file = 'sh/resp/'.$serv->show_name().'/status.log';
            $time = time();
            $file_time = filemtime($log_file);
            $diff = $time - $file_time;
            if($diff > $timediff['player']) {
                if($fn = fopen($log_file,"r")) {
                    $re .= 'make JSON... '.$serv->show_name().'<br />';
                    $json = file_get_contents('data/serv/'.$serv->show_name().'.json');
                    $server = json_decode($json);
                    $name = $serv->show_name();
                    $ier = 0;
                    $wer = 0;
                    $server->warning = null;
                    $server->warning[0] = null;
                    $server->warning_count = 0;
                    $server->error_count = 0;
                    $server->error = null;
                    $server->error[0] = null;
                    $server->online = 'NO';
                    $server->aplayers = 0;
                    $server->players = 0;
                    $server->pid = 'NO';
                    $server->run = 'NO';
                    $server->listening = 'NO';

                    $server->installed = $serv->check_install();

                    while(!feof($fn))  {
                        $result = fgets($fn);
                        $server->cfg = $name;
                        if(strpos($result, 'listening') !== false) {
                            $str = filtersh($result);
                            $str = explode(":", $str);
                            $server->listening = filter_end($str[1]);
                        }


                        if(strpos($result, 'running') !== false) {
                            $str = filtersh($result);
                            $str = explode(":", $str);
                            $server->run = filter_end($str[1]);
                        }


                        if(strpos($result, 'pid') !== false) {
                            $str = filtersh($result);
                            $str = explode(":", $str);
                            $server->warning[$wer] = $str[1];
                            $server->pid = $str[1];
                        }


                        if(strpos($result, 'Players') !== false && strpos($result, 'Active') === false) {
                            $str = filtersh($result);
                            $str = explode(":", $str);
                            $server->players = $str[1];
                        }


                        if(strpos($result, 'Active') !== false) {
                            $str = filtersh($result);
                            $str = explode(":", $str);
                            $server->aplayers = $str[1];
                        }


                        if(strpos($result, 'version') !== false) {
                            $str = filtersh($result);
                            $str = explode(":", $str);
                            $server->version = $str[1];
                        }


                        if(strpos($result, 'connect link') !== false) {
                            $str = filtersh($result);
                            $str = explode("link:", $str);
                            $server->connect = $str[1];
                        }


                        if(strpos($result, 'ARKServers link') !== false) {
                            $str = filtersh($result);
                            $str = explode("link:", $str);
                            $server->ARKServers = $str[1];
                        }


                        if(strpos($result, 'online') !== false) {
                            $str = filtersh($result);
                            $str = explode(":", $str);
                            $server->online = filter_end($str[1]);
                        }


                        if(strpos($result, 'ERROR') !== false) {
                            $str = filtersh($result);
                            $str = explode("ERROR", $str);
                            $server->error[$ier] = $str[1];
                            $ier++;
                        }


                        if(strpos($result, 'WARN') !== false) {
                            $str = filtersh($result);
                            $str = explode("WARN", $str);
                            $server->warning[$ier] = $str[1];
                            $wer++;
                        }


                        if(strpos($result, 'PID') !== false) {
                            $str = filtersh($result);
                            $str = explode("WARN", $str);
                            $server->pid = $str[1];
                        }


                        if(strpos($result, 'build ID') !== false) {
                            $str = filtersh($result);
                            $str = explode("WARN", $str);
                            $server->bid = $str[1];
                        }

                    }
                    if($ier > 0) $server->error_count = $ier;
                    if($wer > 0) $server->warning_count = $wer;
                    fclose($fn);

                    $count_serv_max++;

                }
                $server = json_encode($server);
                if(file_put_contents('data/serv/'.$name.'.json', $server));
            }

            $server = file_get_contents('data/serv/'.$name.'.json');
            $server = json_decode($server);
            $file = 'sh/serv/jobs_ID_'.$serv->show_name().'.sh';
            $txt = file_get_contents($file);
            if(!file_exists('sh/serv/jobs_ID_'.$serv->show_name().'.state')) file_put_contents('sh/serv/jobs_ID_'.$serv->show_name().'.state', 'FALSE');
            $serv_state = file_get_contents('sh/serv/jobs_ID_'.$serv->show_name().'.state');
            $serv_state = str_replace("\n", null, $serv_state);
            $serv_state = str_replace(" ", null, $serv_state);
            echo $serv_state;
            if(strpos($serv_state, 'TRUE') !== false) {
                $server->next = 'FALSE';
            }
            elseif(strpos($serv_state, 'FALSE') !== false) {
                $server->next = 'TRUE';
            }

            if($server->online == 'Yes' && $serv->cfg_read('ark_RCONEnabled') == 'True' && $serv->cfg_read('ark_ServerAdminPassword') != '') {
                $ip = $_SERVER['SERVER_ADDR'];
                $port = $serv->cfg_read('ark_RCONPort');
                $pw = $serv->cfg_read('ark_ServerAdminPassword');
                $rcon = new Rcon($ip, $port, $pw, 3);
                $rcon->connect();


                //serverCHAT
                $log = 'data/saves/chat_'.$serv->show_name().'.log';
                $file = file_get_contents($log);
                $rcon->send_command('getchat');
                $resp = $rcon->get_response();
                $exp = explode("\n", $resp);
                $string = null;
                for ($u=0;$u<count($exp);$u++) {
                    $string .= "\n".time()."(-/-)".$exp[$u];
                }
                $file = $file.$string;

                if(strpos($resp, 'But no response') !== false) {null;}
                elseif(strpos($resp, 'Connection refused') !== false) {null;}
                else {
                    file_put_contents($log, $file);
                }


                //serverPLAYER
                $player = array();
                $pl = 'data/saves/pl_'.$serv->show_name().'.players';
                $rcon->send_command('listplayers');
                $p_str = $rcon->get_response();
                if(strpos($p_str, 'No Players Connected') !== false) {
                    $player[0]['name'] = 'NO';
                    $player[0]['steamID'] = 0;
                    $p_str = json_encode($player, JSON_INVALID_UTF8_SUBSTITUTE);
                }
                else {
                    $pli = 0;
                    $p_str = str_replace("\r", null, $p_str);
                    $exp_1 = explode("\n", $p_str);
                    for($y=0;$y<count($exp_1);$y++) {
                        if(strlen($exp_1[$y])>15) {
                            for($x=0;$x<$serv->cfg_read('ark_MaxPlayers');$x++) {
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


            $server = json_encode($server);
            if(file_put_contents('data/serv/'.$name.'.json', $server));
        }
        echo $dir[$i];
    }
}

// Allgemeine Aufgaben
$file = 'data/serv/all.json';
$json_all = json_decode(file_get_contents($file), true);
$json_all = null;
$on = 0;
$max = 0;
$z = 0;
$s = 0;
$dir = dirToArray('remote/arkmanager/instances/');
for($i=0;$i<count($dir);$i++) {
    if($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $servdata = new server($dir[$i]);

        $data = parse_ini_file('remote/arkmanager/instances/'.$dir[$i].'.cfg');
        $json = json_decode(file_get_contents('data/serv/'.$dir[$i].'.json'));

        $json->online = filtersh($json->online);
        $json_all['cfgs'][$s] = $servdata->show_name().'.cfg';

        $max++;
        $z++;
        if($json->online == 'Yes') {
            $on++;
        }
        $path = 'data/saves/chat_'.$dir[$i].'.log';

        $array = file($path);
        $y = sizeof($array);
        $filestring = null;
        for($z=0;$z<$y;$z++) {
            $string = json_encode($array[$z]);
            if(strpos($string, '(-\/-)') !== false) {
                $exp = explode('(-\/-)', $string);
                $string = $exp[1];
            }
            $string = str_replace(" ", null, $string);
            $string = str_replace("\n", null, $string);
            $string = str_replace('\n', null, $string);
            $string = str_replace('\u0000\u0000', null, $string);
            $string = str_replace("\"", null, $string);
            if($string != null && $string != "") {
                $filestring .= $array[$z];
            }
        }
        file_put_contents($path, $filestring);
        $s++;
    }
}

$json_all['maxserv'] = $max;
$json_all['onserv'] = $on;
print_r($json_all);
$json_all = json_encode($json_all, true);
if(file_put_contents($file, $json_all));





$tpl_crontab->repl('re', $re);
?>