<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$session_user->perm("$perm/saves/show")) {
    header("Location: /401");
    exit;
}

$pagename = '{::lang::php::sc::page::mods::pagename}';
$page_tpl = new Template('saves.htm', __ADIR__.'/app/template/sub/serv/');
$urltop = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::mods::pagename}</li>';
$jhelper = new player_json_helper();

$resp = null;
$c_pl = $c_t = $w_t = 0;

// erstelle Zip download
if (isset($_POST["zip"]) && $session_user->perm("$perm/saves/download")) {
    if(!file_exists(__ADIR__."/app/downloads")) mkdir(__ADIR__."/app/downloads");
    $zipfile = __ADIR__."/app/downloads/savegames.tar";
    
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
                $resp .= $alert->re(); //download startet
                header("Location: ".str_replace(__ADIR__, null, $zipfile).".gz");
                if(file_exists($zipfile)) unlink($zipfile);
            }
            else {
                // Melde Schreibe/Lese Fehler
                $resp .= $alert->rd(1);
            }
        }
        else {
            // Melde Schreib/Lese Fehler
            $resp .= $alert->rd(1);
        }
    }
    else {
        // Melde Input Fehller (Fehlende Werte)
        $resp .= $alert->rd(2);
    }
}
elseif(isset($_POST["zip"])) {
    $resp .= $alert->rd(99);
}

// Entferne Savegame
if (isset($_POST["remove"]) && $session_user->perm("$perm/saves/remove")) {

    // Setzte Vars
    $file_name = $_POST["file"];
    $savedir = $serv->dir_save();

    // Wenn Spieler
    if (strpos($file_name, 'profile') !== false) {
        $filename_1 = $savedir.'/'.$file_name;
        $filetname = str_replace('.arkprofile', null, $file_name);
        $filename_2 = str_replace('.arkprofile', '.profilebak', $file_name);
        $filename_2 = $savedir.'/'.$filename_2;
        $del[0] = 0;
        $del[1] = 0;

        if (file_exists($filename_1)) {
            if (unlink($filename_1));
        }
        if (file_exists($filename_2)) {
            if (unlink($filename_2));
        }

        $path = __ADIR__.'/app/json/saves/player_'.$serv->name().'.json';
        $json = $helper->fileToJson($path);

        for ($i=0;$i<count($json);$i++) {
            $pl = $jhelper->player($json, $i);
            if ($filetname == $pl->SteamId) {
                unset($json[$i]);
                break;
            }
        }
        $json = array_values($json);
        if (file_put_contents($path, $helper->jsonToString($json))) {
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }


    // Wenn Stamm
    elseif (strpos($file_name, 'tribe') !== false) {
        $filename_1 = $savedir.'/'.$file_name;
        $filetname = str_replace('.arktribe', null, $file_name);
        $filename_2 = str_replace('.arktribe', '.tribebak', $file_name);
        $filename_2 = $savedir.'/'.$filename_2;
        $del[0] = 0;
        $del[1] = 0;

        print_r($file);

        if (file_exists($filename_1)) {
            if (unlink($filename_1));
        }
        if (file_exists($filename_2)) {
            if (unlink($filename_2));
        }
        print_r($del);

        $path = __ADIR__.'/app/json/saves/tribes_'.$serv->name().'.json';
        $json = $helper->fileToJson($path);
        for ($i=0;$i<count($json);$i++) {
            $pl = $jhelper->tribe($json, $i);
            if ($filetname == $pl->Id) {
                unset($json[$i]); break;
            }
        }
        $json = array_values($json);
        if (file_put_contents($path, $helper->jsonToString($json))) {
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }


    // Wenn Welt
    elseif (strpos($file_name, '.ark') !== false) {
        $filename = $savedir.'/'.$file_name;
        $filetname = str_replace('.ark', null, $file_name);
        if (unlink($filename)) {
            $arr = dirToArray($serv->dir_save());
            for ($i=0;$i<count($arr);$i++) {
                if (strpos($arr[$i], $filetname) !== false) {
                    if (file_exists($savedir.'/'.$arr[$i])) unlink($savedir.'/'.$arr[$i]);
                }
            }
            header('Location: /servercenter/'.$serv->name().'/saves/');
            exit;
        }
    }
}
elseif(isset($_POST["remove"])) {
    $resp .= $alert->rd(99);
}

// Entferne Savegame
if (isset($_POST["removeall"]) && $session_user->perm("$perm/saves/remove")) {
    $savedir = $serv->dir_save();
    if(del_dir($savedir)) {
        mkdir($savedir);
        $resp .= $alert->rd(101);
    }
    else {
        $resp .= $alert->rd(1);
    }
}
elseif(isset($_POST["removeall"])) {
    $resp .= $alert->rd(99);
}

$urls = '/servercenter/'.$url[2].'/mods/';

$serv->cfg_read('arkserverroot');
$savedir = $serv->dir_save();

// Listen
$player = null;
$tribe_json = $helper->fileToJson(__ADIR__.'/app/json/saves/tribes_'.$serv->name().'.json', false);
$player_json = $helper->fileToJson(__ADIR__.'/app/json/saves/player_'.$serv->name().'.json', false);
$playerjson = $helper->fileToJson(__ADIR__.'/app/json/steamapi/profile_savegames_'.$serv->name().'.json', true);
$playerjs = isset($playerjson["response"]["players"]) ? $playerjson["response"]["players"] : [];
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
        $list_tpl = new Template('saves.htm', __ADIR__.'/app/template/lists/serv/savegames/');
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
            $IG_name = $row["CharacterName"] == "" ? $steamapi_user[$row["SteamId"]]["personaname"] : $row["CharacterName"];
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
        $list_tpl->r('EP', $xp);
        $list_tpl->r('SpielerID', $SpielerID);
        $list_tpl->r('TEP', $TotalEngramPoints);
        $list_tpl->r('TID', $TribeId);
        $list_tpl->r('file', $SteamId.'.arkprofile');
        $list_tpl->r('cfg', $serv->name());


        $list_tpl->rif ('empty', false);

        $file = $savedir.'/'.$SteamId.'.arkprofile';
        $list_tpl->r('durl', str_replace(__ADIR__, null, $file));
    
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
        $list_tpl = new Template('tribes.htm', __ADIR__.'/app/template/lists/serv/savegames/');
        $list_tpl->load();

        // Hole Daten von MySQL
        $query = "SELECT * FROM ArkAdmin_tribe WHERE `server`='".$serv->name()."' AND `Id`='".$tribe_save[$i]."'";
        $query = $mycon->query($query);

        if($query->numRows() > 0) {
            $rows = $query->fetchArray();

            $tplayer = json_decode($rows["Members"], true);

            $playerlist = null;
            $ct=0;

            if(is_countable($tplayer)) {
                foreach ($tplayer as $item) {

                    $query = "SELECT * FROM ArkAdmin_players WHERE `server`='" . $serv->name() . "' AND `CharacterName`='" . $item . "'";
                    $query = $mycon->query($query);

                    if ($query->numRows() > 0) {
                        $row = $query->fetchArray();
                        $row["SteamId"] = intval($row["SteamId"]);

                        $playerlist_tpl = new Template('tribes_user.htm', __ADIR__.'/app/template/lists/serv/savegames/');
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
            $list_tpl->r('durl', str_replace(__ADIR__, null, $file));
            $list_tpl->r('file', $rows["Id"].'.arktribe');
            $list_tpl->r('cfg', $serv->name());

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
                $list_tpl = new Template('world.htm', __ADIR__.'/app/template/lists/serv/savegames/');
                $list_tpl->load();
                $time = filemtime($file);

                $name = str_replace('.ark', null, $dirarr[$i]);
                $date_array = date_parse($name);

                $list_tpl->r('name', $name);
                $list_tpl->r('update', converttime($time));
                $list_tpl->r('durl', str_replace(__ADIR__, null, $file));
                $list_tpl->r('rnd', rndbit(10));
                $list_tpl->r('file', $dirarr[$i]);
                $list_tpl->r('cfg', $serv->name());

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
$panel = $page_tpl->load_var();

$player = null;

