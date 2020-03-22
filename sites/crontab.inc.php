<?php


$tpl_crontab = new Template('crontab.htm', 'tpl/system/');
$tpl_crontab->load();
$re = null;
$root_dir = $_SERVER['DOCUMENT_ROOT'];

$pagename = 'Crontab';
$job = $url[2];
$re = null;
$time = time();

$timediff['shell'] = 20;
$timediff['player'] = 5;

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
            if(!file_exists($c_serv->cfg_read("logdir"))) mkdir($c_serv->cfg_read("logdir"));
            if(!file_exists($c_serv->cfg_read("arkserverroot"))) mkdir($c_serv->cfg_read("arkserverroot"));
            if(!file_exists($c_serv->cfg_read("arkbackupdir"))) mkdir($c_serv->cfg_read("arkbackupdir"));

            if(!file_exists($shell_path)) file_put_contents($shell_path, null);
            $file_time = filemtime($shell_path);
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
        $checkit = false;
        if($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
            $serv = new server($dir[$i]);

            $log = 'data/saves/chat_'.$serv->show_name().'.log';
            if(!file_exists($log)) file_put_contents($log, " ");
            $pl = 'data/saves/pl_'.$serv->show_name().'.players';
            if(!file_exists($pl)) file_put_contents($pl, " ");

            if($serv->check_install() == "TRUE") {

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

            }
            $container = null;
            $json_user = null;
            $json_tribe = null;

            // erstelle STATUS
            $log_file = 'sh/resp/'.$serv->show_name().'/status.log';
            $time = time();
            $file_time = filemtime($log_file);
            if(!file_put_contents($log_file.'2', sh_crontab(file_get_contents($log_file)))) exit;
            file_put_contents($serv->show_name().'.log', sh_crontab(file_get_contents($log_file)));
            $log_file = 'sh/resp/'.$serv->show_name().'/status.log2';
            $log_file = 'sh/resp/'.$serv->show_name().'/status.log';
            $diff = $time - $file_time;
            if($diff > $timediff['player']) {
                $checkit = true;
                if($fn = fopen($log_file,"r")) {
                    $jsonfile = 'data/serv/'.$serv->show_name().'.json';

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
                    $server['installed'] = $serv->check_install();


                    $ier = 0;
                    $wer = 0;

                    while(!feof($fn))  {
                        $result = fgets($fn);
                        $result = sh_crontab($result);
                        if(!strpos($result, "'status'")) {
                            if(strpos($result, 'http:') || strpos($result, 'steam:')) {
                                $exp = explode("link:", $result);
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                $key = "connect";
                                if($exp[0] == "ARKServers") $key = "ARKServers";
                                $server[$key] = sh_crontab($exp[1]);;
                            }
                            elseif(strpos($result, 'ERROR')) {
                                $exp = explode("ERROR", $result);
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                $server['error'][$ier] = sh_crontab($exp[1]);;
                                $ier++;
                            }
                            elseif(strpos($result, 'WARN')) {
                                $exp = explode("WARN", $result);
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                $server['warning'][$ier] = sh_crontab($exp[1]);;
                                $wer++;
                            }
                            else {
                                $exp = explode(":", $result);
                                $key = str_replace($search, $replace, $exp[0]);
                                if($key != "ServerName") $exp[1] = str_replace(" ", null, $exp[1]);
                                if($key == "ServerName") {
                                    $str = $exp[1];
                                    $expstr = explode(" - (", $str);
                                    $server['version'] = str_replace(array(")", "v"), array(null, null), $expstr[1]);
                                }
                                $exp[1] = str_replace("\n", null, $exp[1]);
                                if($key != null) $server[$key] = sh_crontab($exp[1]);
                            }
                        }
                        $server['cfg'] = $name;
                    }
                    if($ier > 0) $server['error_count'] = $ier;
                    if($wer > 0) $server['warning_count'] = $wer;
                    $server['ARKServers'] = "https://arkservers.net/server/".$ip.":".$serv->cfg_read("ark_QueryPort");
                    fclose($fn);

                    $count_serv_max++;

                }

                $file = 'sh/serv/jobs_ID_'.$serv->show_name().'.sh';
                $txt = file_get_contents($file);
                if(!file_exists('sh/serv/jobs_ID_'.$serv->show_name().'.state')) file_put_contents('sh/serv/jobs_ID_'.$serv->show_name().'.state', 'True');
                $serv_state = file_get_contents('sh/serv/jobs_ID_'.$serv->show_name().'.state');
                $serv_state = str_replace("\n", null, $serv_state);
                $serv_state = str_replace(" ", null, $serv_state);
                if($serv_state== 'TRUE') {
                    $server['next'] = 'FALSE';
                }
                elseif($serv_state== 'FALSE') {
                    $server['next'] = 'TRUE';
                }
                if($checkit) {
                    $helper->savejson_create($server, $jsonfile);
                }
            }

            if($server['online'] == 'Yes' && $serv->cfg_read('ark_RCONEnabled') == 'True' && $serv->cfg_read('ark_ServerAdminPassword') != '') {
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


            $helper->savejson_create($server, 'data/serv/'.$name.'.json');
        }
    }
}

//jobs
elseif($job == "jobs") {
    $dir = dirToArray('remote/arkmanager/instances/');
    for($i=0;$i<count($dir);$i++) {
        if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
            $serv = new server($dir[$i]);
            $jobs = new jobs();
            $jobs->set($serv->show_name());
            $cpath = "data/config/jobs_" . $serv->show_name() . ".json";
            if(file_exists($cpath)) {
                $json = $helper->file_to_json($cpath, true);
                if(count($json['jobs']) > 0) {
                    for($z=0;$z<count($json['jobs']);$z++) {
                        $diff =  time() - $json['jobs'][$z]['datetime'];
                        if($diff >= 0 && $json['jobs'][$z]['active'] == "true") {
                            if($diff > $json['jobs'][$z]['intervall']) {
                                $x = $diff / $json['jobs'][$z]['intervall'];
                                $x = floor($x);
                                $x = $x * $json['jobs'][$z]['intervall'];
                            }
                            else {
                                $x = $json['jobs'][$z]['intervall'];
                            }
                            $nextrun = $json['jobs'][$z]['datetime'] + $x;
                            $jobs->create($json['jobs'][$z]['action'].' '.$json['jobs'][$z]['parameter']);
                            $json['jobs'][$z]['datetime'] = $nextrun;
                        }
                    }
                }
                // Backup & Update
                $key[0] = 'backup'; $key[1] = 'update';
                for($z=0;$z<count($key);$z++) {
                    if($json['option'][$key[$z]]['active'] == "true") {
                        $diff =  time() - $json['option'][$key[$z]]['datetime'];
                        if($diff >= 0) {
                            if($diff > $json['option'][$key[$z]]['intervall']) {
                                $x = $diff / $json['option'][$key[$z]]['intervall'];
                                $x = floor($x);
                                $x = $x * $json['option'][$key[$z]]['intervall'];
                            }
                            else {
                                $x = $json['option'][$key[$z]]['intervall'];
                            }
                            $nextrun = $json['option'][$key[$z]]['datetime'] + $x;
                            $jobs->create($key[$z].' '.$json['option'][$key[$z]]['parameter']);
                            $json['option'][$key[$z]]['datetime'] = $nextrun;
                        }
                    }
                }
                
                $helper->savejson_exsists($json, $cpath);
            }
        }
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
        $json = $helper->file_to_json('data/serv/'.$dir[$i].'.json', true);

        $json['online'] = filtersh($json['online']);
        $json_all['cfgs'][$s] = $servdata->show_name().'.cfg';

        $max++;
        $z++;
        if($json['online'] == 'Yes') {
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
$json_all = json_encode($json_all, true);
if(file_put_contents($file, $json_all));





$tpl_crontab->repl('re', $re);
?>