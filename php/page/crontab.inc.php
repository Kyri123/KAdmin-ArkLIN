<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

if (!file_exists("app/check/")) mkdir("app/check/"); file_put_contents("app/check/webhelper", time());

$tpl_crontab = new Template('crontab.htm', 'app/template/system/');
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
    if (strpos($str, 'Yes') !== false) {
        return 'Yes';
    } else {
        return 'No';
    }
}

//Für Serverstatus
if ($job == "status") {
    if (file_put_contents("app/data/checked", "checked"));
    $mainfile = null;
    $dir = dirToArray('remote/arkmanager/instances/');
    for ($i=0;$i<count($dir);$i++) {
        $server[$i]['cfg'] = $dir[$i];
        $re .= 'Read... ' . $dir[$i] . '<br />';
        if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
            $c_serv = new server($dir[$i]);
            
            $shell_path = 'sh/serv/check_server_ID_'.$c_serv->name().'.sh';
            // Shell Command
            $shell_command = 'echo "" > '.$root_dir.'/sh/serv/check_server_ID_'.$dir[$i].'.sh ;arkmanager status @'.$c_serv->name().' > '.$root_dir.'/sh/resp/'.$c_serv->name().'/status.log ;exit';

            // Erstelle Dateien & Verzeichnis...
            if (!file_exists('sh/resp/'.$c_serv->name())) mkdir('sh/resp/'.$c_serv->name());
            if (!file_exists('remote/serv/server_ID_'.$c_serv->name().'_logs')) mkdir('remote/serv/server_ID_'.$c_serv->name().'_logs');
            if (!file_exists('sh/resp/'.$c_serv->name().'/last.log')) file_put_contents('sh/resp/'.$c_serv->name().'/last.log', null);
            if (!file_exists('app/json/serverinfo/'.$c_serv->name().'.json')) file_put_contents('app/json/serverinfo/'.$c_serv->name().'.json', '{}');
            if (!file_exists('sh/serv/jobs_ID_'.$c_serv->name().'.sh')) file_put_contents('sh/serv/jobs_ID_'.$c_serv->name().'.sh', null);
            if (!file_exists('sh/serv/sub_jobs_ID_'.$c_serv->name().'.sh')) file_put_contents('sh/serv/sub_jobs_ID_'.$c_serv->name().'.sh', null);
            if (!file_exists($c_serv->cfg_read("logdir"))) mkdir($c_serv->cfg_read("logdir"));
            if (!file_exists($c_serv->cfg_read("arkserverroot"))) mkdir($c_serv->cfg_read("arkserverroot"));
            if (!file_exists($c_serv->cfg_read("arkbackupdir"))) mkdir($c_serv->cfg_read("arkbackupdir"));

            if (!file_exists($shell_path)) file_put_contents($shell_path, null);
            $file_time = filemtime($shell_path);
            $diff = $time - $file_time;
            if ($diff > $timediff['shell']) {
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
elseif ($job == "player") {
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
                if ($serv_state == 'TRUE') {
                    $server['next'] = 'FALSE';
                }
                elseif ($serv_state== 'FALSE') {
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
                $rcon->send_command('getchat');
                $resp = $rcon->get_response();
                $exp = explode("\n", $resp);
                $string = null;
                for ($u=0;$u<count($exp);$u++) {
                    $string .= "\n".time()."(-/-)".$exp[$u];
                }
                $file = $file.$string;

                if (strpos($resp, 'But no response') !== false) {null;}
                elseif (strpos($resp, 'Connection refused') !== false) {null;}
                else {
                    file_put_contents($log, $file);
                }


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
}

//jobs
elseif ($job == "jobs") {
    $dir = dirToArray('remote/arkmanager/instances/');
    for ($i=0;$i<count($dir);$i++) {
        if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
            $serv = new server($dir[$i]);
            $jobs = new jobs();
            $jobs->set($serv->name());
            $cpath = "app/json/servercfg/jobs_" . $serv->name() . ".json";
            if (file_exists($cpath)) {
                $json = $helper->file_to_json($cpath, true);
                if (count($json['jobs']) > 0) {
                    foreach($json['jobs'] as $key => $value) {
                        $diff =  time() - $json['jobs'][$key]['datetime'];
                        if ($diff >= 0 && $json['jobs'][$key]['active'] == "true") {
                            if ($diff > $json['jobs'][$key]['intervall']) {
                                $x = $diff / $json['jobs'][$key]['intervall'];
                                $x = floor($x);
                                $x = $x * $json['jobs'][$key]['intervall'];
                            }
                            else {
                                $x = $json['jobs'][$key]['intervall'];
                            }
                            $nextrun = $json['jobs'][$key]['datetime'] + $x;
                            $jobs->arkmanager($json['jobs'][$key]['action'].' '.$json['jobs'][$key]['parameter']);
                            $json['jobs'][$key]['datetime'] = $nextrun;
                        }
                    }
                }
                // Backup & Update
                $key[0] = 'backup'; $key[1] = 'update';
                for ($z=0;$z<count($key);$z++) {
                    if ($json['option'][$key[$z]]['active'] == "true") {
                        $diff =  time() - $json['option'][$key[$z]]['datetime'];
                        if ($diff >= 0) {
                            if ($diff > $json['option'][$key[$z]]['intervall']) {
                                $x = $diff / $json['option'][$key[$z]]['intervall'];
                                $x = floor($x);
                                $x = $x * $json['option'][$key[$z]]['intervall'];
                            }
                            else {
                                $x = $json['option'][$key[$z]]['intervall'];
                            }
                            $nextrun = $json['option'][$key[$z]]['datetime'] + $x;
                            $jobs->arkmanager($key[$z].' '.$json['option'][$key[$z]]['parameter']);
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
$file = 'app/json/serverinfo/all.json';
$json_all = json_decode(file_get_contents($file), true);
$json_all = null;
$on = 0;
$max = 0;
$z = 0;
$s = 0;
$dir = dirToArray('remote/arkmanager/instances/');
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $servdata = new server($dir[$i]);

        $data = parse_ini_file('remote/arkmanager/instances/'.$dir[$i].'.cfg');
        $json = $helper->file_to_json('app/json/serverinfo/'.$dir[$i].'.json', true);

        $json['online'] = filtersh($json['online']);
        $json_all['cfgs'][$s] = $servdata->name().'.cfg';

        $max++;
        $z++;
        if ($json['online'] == 'Yes') {
            $on++;
        }
        $path = 'app/json/saves/chat_'.$dir[$i].'.log';

        $array = file($path);
        $y = sizeof($array);
        $filestring = null;
        for ($z=0;$z<$y;$z++) {
            $string = json_encode($array[$z]);
            if (strpos($string, '(-\/-)') !== false) {
                $exp = explode('(-\/-)', $string);
                $string = $exp[1];
            }
            $string = str_replace(" ", null, $string);
            $string = str_replace("\n", null, $string);
            $string = str_replace('\n', null, $string);
            $string = str_replace('\u0000\u0000', null, $string);
            $string = str_replace("\"", null, $string);
            if ($string != null && $string != "") {
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
if (file_put_contents($file, $json_all));



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


$tpl_crontab->r('re', $re);
?>