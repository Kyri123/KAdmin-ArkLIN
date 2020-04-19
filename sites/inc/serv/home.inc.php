<?php

$page_tpl = new Template('home.htm', 'tpl/serv/sites/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Startseite</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->repl('cfg' ,$serv->show_name());
$page_tpl->repl('SESSION_USERNAME' ,$user->name());

$cheatfile = $serv->get_save_dir(true)."/AllowedCheaterSteamIDs.txt";

//add_admin
if(isset($_POST["addadmin"])) {
    $id = $_POST["id"];
    $api = $steamapi->getsteamprofile_class($id);
    $content = file_get_contents($cheatfile)."\n$id";
    if(file_put_contents($cheatfile, $content)) {
        $resp = meld('success', "Admin <b>$api->personaname</b> wurde hinzugefügt!", 'Erfolgreich!', null);
    }
    else {
        $resp = meld('danger', 'Fehler beim Schreiben der Datei!', 'Fehler!', null);
    }
}

//remove Admin
if(isset($url[4]) && isset($url[5]) && $url[4] == 'rm') {
    $id = $url[5];
    $api = $steamapi->getsteamprofile_class($id);
    $content = file_get_contents($cheatfile);
    if(substr_count($content, $id) > 0) {
        $content = str_replace($id, null, $content);
        if(file_put_contents($cheatfile, $content)) {
            $resp = meld('success', "Admin <b>$api->personaname</b> wurde entfernt!", 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Fehler beim Schreiben der Datei!', 'Fehler!', null);
        }
    }
}




$serv->cfg_read('arkserverroot');
$savedir = $serv->get_save_dir();
$player_json = $helper->file_to_json('data/saves/player_'.$serv->show_name().'.json', false);
$tribe_json = $helper->file_to_json('data/saves/tribes_'.$serv->show_name().'.json', false);
if(!is_array($player_json)) $player_json = array();
if(!is_array($tribe_json)) $tribe_json = array();
$bool_install = filter_var($serv->check_install(), FILTER_VALIDATE_BOOLEAN);
//Liste für Admin
if($bool_install) {
    if(!file_exists($cheatfile)) file_put_contents($cheatfile, "");
    $jhelper = new player_json_helper();
    $userlist_admin = null;
    $player_json = $helper->file_to_json('data/saves/player_'.$serv->show_name().'.json', false);
    if(!is_array($player_json)) $player_json = array();

    $file = file($cheatfile);

    for ($i=0;$i<count($file);$i++) {
        $find = array("\n", "\r", " ");
        $file[$i] = str_replace($find, null, $file[$i]);
        if(is_numeric($file[$i])) {
            $list_tpl = new Template('list_user_admin.htm', 'tpl/serv/sites/list/');
            $list_tpl->load();

            $found = false;
            for($p=0;$p<count($player_json);$p++) {
                $pl = $jhelper->player($player_json, $p);
                $id = $pl->SteamId;
                if($id === $file[$i]) {
                    $found = true;
                    break;
                }
            }

            if($found) {
                $api = $steamapi->getsteamprofile_class($id);
                $list_tpl->repl("igname", $pl->CharacterName);
            }
            else {
                $api = $steamapi->getsteamprofile_class($file[$i]);
                $list_tpl->repl("igname", "Ingamename Unbekannt (Savegame exsistiert nicht)");
            }

            $list_tpl->repl("stid", $api->steamid);
            $list_tpl->repl("url", $api->profileurl);
            $list_tpl->repl("cfg", $serv->show_name());
            $list_tpl->repl("rndb", rndbit(25));
            $list_tpl->repl("stname", $api->personaname);
            $list_tpl->repl("img", $api->avatarmedium);

            $adminlist_admin .= $list_tpl->loadin();
        }
    }

    if(is_array($player_json)) {
        for($i=0;$i<count($player_json);$i++) {
            $pl = $jhelper->player($player_json, $i);
            $id = $pl->SteamId;
            $name = $pl->SteamName;
            $ig_name = $pl->CharacterName;
            $not = true;
            for($p=0;$p<count($file);$p++) {
                if(intval($file[$p]) == $id) {
                    $not = false;
                    break;
                }
            }

            if($not) $userlist_admin .= "<option value='$id'>$name - $ig_name</option>";
        }
    }
}


if(!$ifcadmin) $resp .= meld_full('info', "Funktion Administrator wurde Deaktiviert da die Synchronisation aktiv ist. Gehe für Änderungen zum Masterserver.", 'Administratoren: Sync Mode', null);

$page_tpl->replif("installed", $bool_install);
$page_tpl->repl("userlist_admin", $userlist_admin);
$page_tpl->repl("adminlist_admin", $adminlist_admin);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>