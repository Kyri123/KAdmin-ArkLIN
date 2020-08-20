<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::mods::pagename}';
$page_tpl = new Template('saves.htm', 'app/template/sub/serv/');
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::mods::pagename}</li>';
$jhelper = new player_json_helper();

$resp = null;
$c_pl = $c_t = $w_t = 0;

// erstelle Zip download
if (isset($_POST["zip"])) {
    if(!file_exists("app/downloads")) mkdir("app/downloads");
    $zipfile = "app/downloads/savegames.tar";
    
    $save = isset($_POST["save"]);
    $tribe = isset($_POST["tribe"]);
    $map = isset($_POST["map"]);

    if($tribe || $save || $map) {
        if(file_exists($zipfile)) unlink($zipfile);
        if(file_exists($zipfile.".gz")) unlink($zipfile.".gz");
        // Erstelle tar.gz
        if ($tar = new PharData($zipfile)) {
            $dir = scandir($serv->dir_save());
            $path = $serv->dir_save();
            $file_count = 0;
            foreach($dir as $file) {
                $file_path = "$path/$file";
                // Prüfe ob Datei in die tar.gz darf
                if(
                    (strpos($file, 'tribe') !== false && $tribe) ||
                    (strpos($file, 'ark') !== false && strpos($file, "_0") === false && strpos($file, "_2") === false && strpos($file, "_1") === false && strpos($file, "arktribe") === false && strpos($file, "arkprofile") === false && $map) ||
                    (strpos($file, 'profile') !== false && $save)
                ) {
                    if($tar->addFile($file_path)) $file_count++;
                } 
            }
            //beende zip erstellung
            if($tar->compress(Phar::GZ)) {
                // Melde Download bereit
                $alert->code = 110;
                $alert->r("url", "/$zipfile");
                $resp = $alert->re(); //download startet
                header("Location: /".$zipfile.".gz");
                if(file_exists($zipfile)) unlink($zipfile);
            }
            else {
                // Melde Schreibe/Lese Fehler
                $resp = $alert->rd(1); 
            }
        }
        else {
            // Melde Schreib/Lese Fehler
            $resp = $alert->rd(1);
        }
    }
    else {
        // Melde Input Fehller (Fehlende Werte)
        $resp = $alert->rd(2);
    }
}

// Entferne Savegame
if (isset($url[4]) && $url[4] == 'remove' && isset($url[5])) {

    // Setzte Vars
    $file_name = $url[5];
    $savedir = $serv->dir_save();

    // Wenn Spieler
    if (strpos($file_name, 'profile') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.arkprofile', null, $file_name);
        $file['name2'] = str_replace('.arkprofile', '.profilebak', $file_name);
        $file['name2'] = $savedir.'/'.$file['name2'];
        $del[0] = 0;
        $del[1] = 0;

        if (file_exists($file['name1'])) {
            if (unlink($file['name1']));
        }
        if (file_exists($file['name2'])) {
            if (unlink($file['name2']));
        }

        $path = 'app/json/saves/player_'.$serv->name().'.json';
        $json = $helper->file_to_json($path);

        for ($i=0;$i<count($json);$i++) {
            $pl = $jhelper->player($json, $i);
            if ($file['tname'] == $pl->SteamId) {
                unset($json[$i]);
                break;
            }
        }
        $json = array_values($json);
        if (file_put_contents($path, $helper->json_to_str($json))) {
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }


    // Wenn Stamm
    elseif (strpos($file_name, 'tribe') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.arktribe', null, $file_name);
        $file['name2'] = str_replace('.arktribe', '.tribebak', $file_name);
        $file['name2'] = $savedir.'/'.$file['name2'];
        $del[0] = 0;
        $del[1] = 0;

        print_r($file);

        if (file_exists($file['name1'])) {
            if (unlink($file['name1']));
        }
        if (file_exists($file['name2'])) {
            if (unlink($file['name2']));
        }
        print_r($del);

        $path = 'app/json/saves/tribes_'.$serv->name().'.json';
        $json = $helper->file_to_json($path);
        for ($i=0;$i<count($json);$i++) {
            $pl = $jhelper->tribe($json, $i);
            if ($file['tname'] == $pl->Id) {
                unset($json[$i]); break;
            }
        }
        $json = array_values($json);
        if (file_put_contents($path, $helper->json_to_str($json))) {
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }


    // Wenn Welt
    elseif (strpos($file_name, '.ark') !== false) {
        $file['name1'] = $savedir.'/'.$file_name;
        $file['tname'] = str_replace('.ark', null, $file_name);
        if (unlink($file['name1'])) {
            $arr = dirToArray($serv->dir_save());
            for ($i=0;$i<count($arr);$i++) {
                if (strpos($arr[$i], $file['tname']) !== false) {
                    if (file_exists($savedir.'/'.$arr[$i])) unlink($savedir.'/'.$arr[$i]);
                }
            }
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }
}


$urls = '/servercenter/'.$url[2].'/mods/';

$serv->cfg_read('arkserverroot');
$savedir = $serv->dir_save();

// Listen
$player = null;
$tribe_json = $helper->file_to_json('app/json/saves/tribes_'.$serv->name().'.json', false);
$player_json = $helper->file_to_json('app/json/saves/player_'.$serv->name().'.json', false);
$playerjs = $helper->file_to_json('app/json/steamapi/profile_savegames_'.$serv->name().'.json', true)["response"]["players"];
$jhelper = new player_json_helper();

// Spieler liste
$dir_arr = scandir($serv->dir_save());
$player_save = $tribe_save = array();

foreach ($dir_arr as $file) {
    if($file != "." && $file != ".") {
        $file_path = $serv->dir_save()."/$file";
        $file_info = pathinfo($file_path);
        $ext = $file_info["extension"];
        $filename = $file_info["filename"];

        if($ext == "arkprofile") $player_save[] = $filename;
        if($ext == "arktribe") $tribe_save[] = $filename;
    }
}


$count = (is_countable($player_save)) ? count($player_save) : false;
if($count !== false) {
    for ($i=0;$i<$count;$i++) {
        $list_tpl = new Template('saves.htm', 'app/template/lists/serv/savegames/');
        $list_tpl->load();

        // Hole
        $query = "SELECT * FROM ArkAdmin_players WHERE `server`='".$serv->name()."' AND `SteamId`='".$player_save[$i]."'";
        $query = $mycon->query($query);

        if($query->numRows() > 0) {
            $row = $query->fetchArray();
            $row["SteamId"] = intval($row["SteamId"]);

            $img = $steamapi_user[$row["SteamId"]]["avatar"];
            $SteamId = $row["SteamId"];
            $surl = $steamapi_user[$row["SteamId"]]["profileurl"];
            $steamname = $steamapi_user[$row["SteamId"]]["personaname"];
            $IG_level = $row["Level"];
            $xp = $row["ExperiencePoints"];
            $SpielerID = $row["id"];
            $FileUpdated = $row["FileUpdated"];
            $TribeId = $row["TribeId"];
            $TotalEngramPoints = $row["TotalEngramPoints"];
            $TribeName = $row["TribeName"];
            $IG_name = $row["CharacterName"];
        }
        else {
            $img = "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/";
            $surl = $steamname = $IG_name = "#unknown";
            $xp = $SpielerID = $TotalEngramPoints = $SteamId = 0;
            $FileUpdated = time();
            $TribeId = 7;
            $TribeName = null;
        }

        $list_tpl->r('tribe', (($TribeName != null) ? $TribeName : '{::lang::php::sc::notribe}'));
        $list_tpl->r('IG:name', $IG_name);
        $list_tpl->r('IG:Level', $IG_level);
        $list_tpl->r('update', converttime($FileUpdated));
        $list_tpl->r('rnd', rndbit(10));
        $list_tpl->r('url', $surl);
        $list_tpl->r('img', $img);
        $list_tpl->r('steamname', $steamname);
        $list_tpl->r('rm_url', '/servercenter/' . $serv->name() . '/saves/remove/' . $SteamId . '.arkprofile');
        $list_tpl->r('EP', $xp);
        $list_tpl->r('SpielerID', $SpielerID);
        $list_tpl->r('TEP', $TotalEngramPoints);
        $list_tpl->r('TID', $TribeId);
        $list_tpl->rif ('empty', false);

        $file = $savedir.'/'.$SteamId.'.arkprofile';
        $list_tpl->r('durl', "/".$file);
    
        if(file_exists($savedir.'/'.$SteamId.'.arkprofile')) {
            $player .= $list_tpl->load_var();
            $c_pl++;
        }
    }
}
$tribe = null; $c_t = 0;

// Stämme Liste
if(is_countable($tribe_save)) {
    for ($i = 0; $i < count($tribe_save); $i++) {
        $list_tpl = new Template('tribes.htm', 'app/template/lists/serv/savegames/');
        $list_tpl->load();

        // Hole Daten von MySQL
        $query = "SELECT * FROM ArkAdmin_tribe WHERE `server`='".$serv->name()."' AND `Id`='".$tribe_save[$i]."'";
        $query = $mycon->query($query);

        if($query->numRows() > 0) {
            $rows = $query->fetchArray();

            $tplayer = json_decode($rows["Members"], true);

            $playerlist = null;
            $ct=0;

            $member = $tribe_json[$i]->Members;
            if(is_countable($tplayer)) {
                foreach ($tplayer as $item) {

                    $query = "SELECT * FROM ArkAdmin_players WHERE `server`='" . $serv->name() . "' AND `CharacterName`='" . $item . "'";
                    $query = $mycon->query($query);

                    if ($query->numRows() > 0) {
                        $row = $query->fetchArray();
                        $row["SteamId"] = intval($row["SteamId"]);

                        $playerlist_tpl = new Template('tribes_user.htm', 'app/template/lists/serv/savegames/');
                        $playerlist_tpl->load();

                        $playerlist_tpl->r('IG:name', $row["CharacterName"]);
                        $playerlist_tpl->r('lastupdate', converttime($row["FileUpdated"]));
                        $playerlist_tpl->r('url', $steamapi_user[$row["SteamId"]]["profileurl"]);
                        $playerlist_tpl->r('img', $steamapi_user[$row["SteamId"]]["avatar"]);
                        $playerlist_tpl->r('steamname', $steamapi_user[$row["SteamId"]]["personaname"]);
                        $rank = '<b>{::lang::php::sc::page::mods::member}</b>';

                        $playerlist .= $playerlist_tpl->load_var();
                        $ct++;
                    }

                }
            }

            $list_tpl->r('rnd', rndbit(10));
            $list_tpl->r('name', $rows["tribeName"]);
            $list_tpl->r('update', converttime($rows["FileUpdated"]));
            $list_tpl->r('pl', $playerlist);
            $list_tpl->r('count', $ct);
            $list_tpl->r('id', $rows["Id"]);
            $file = $savedir.'/'.$rows["Id"].'.arktribe';
            $list_tpl->r('durl', "/".$file);

            $list_tpl->r('rm_url', '/servercenter/'.$serv->name().'/saves/remove/'.$rows["Id"].'.arktribe');

            $tribe .= $list_tpl->load_var();
            $list_tpl = null;
            $c_t++;
        }
    }
}


$world = null; $w_t = 0;
$dirarr = dirToArray($savedir);
// World  Liste
if(is_countable($dirarr)) {
    for ($i=0;$i<count($dirarr);$i++) {
        if (strpos($dirarr[$i], '.ark')) {
            $file = $savedir.'/'.$dirarr[$i];
            if (file_exists($file)) {
                $list_tpl = new Template('world.htm', 'app/template/lists/serv/savegames/');
                $list_tpl->load();
                $time = filemtime($file);

                $name = str_replace('.ark', null, $dirarr[$i]);
                $date_array = date_parse($name);

                $list_tpl->r('name', $name);
                $list_tpl->r('update', converttime($time));
                $list_tpl->r('durl', "/".$file);
                $list_tpl->r('rnd', rndbit(10));

                $list_tpl->r('rm_url', '/servercenter/'.$serv->name().'/saves/remove/'.$dirarr[$i]);

                if (!strpos($name, 'profile') && $date_array["year"] == null) {
                    $world .= $list_tpl->load_var();
                    $w_t++;
                }
            }
        }
    }
}




$page_tpl->load();
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('urls' ,$urls);
$page_tpl->r('player', $player);
$page_tpl->r('tribe', $tribe);
$page_tpl->r('world', $world);
$page_tpl->r('cp', $c_pl);
$page_tpl->r('ct', $c_t);
$page_tpl->r('cw', $w_t);
$page_tpl->r('resp', $resp);
$page_tpl->session();
$panel = $page_tpl->load_var();

$player = null;

?>