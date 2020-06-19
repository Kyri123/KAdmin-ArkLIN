<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::home::pagename}';
$page_tpl = new Template('home.htm', 'app/template/serv/page/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::home::urltop}</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->r('cfg' ,$serv->name());
$page_tpl->r('SESSION_USERNAME' ,$user->name());

$cheatfile = $serv->dir_save(true)."/AllowedCheaterSteamIDs.txt";

$playerjs = $helper->file_to_json('app/json/steamapi/profile_savegames_'.$serv->name().'.json', true)["response"]["players"];
$count = (is_countable($playerjs)) ? count($playerjs): false;

//add_admin
if (isset($_POST["addadmin"])) {
    $id = $_POST["id"];
    for ($ix=0;$ix<$count;$ix++) if($id == $playerjs[$ix]["steamid"]) {$i = $ix; break;};
    $content = file_get_contents($cheatfile)."\n$id";
    if (file_put_contents($cheatfile, $content)) {
        $alert->code = 100;
        $alert->r("name", $playerjs[$i]["personaname"]);
        $alert->overwrite_text = "{::lang::php::sc::page::home::add_admin}";
        $resp = $alert->re();
    } else {
        $alert->code = 1;
        $resp = $alert->re();
    }
}

//remove Admin
if (isset($url[4]) && isset($url[5]) && $url[4] == 'rm') {
    $id = $url[5];
    for ($ix=0;$ix<$count;$ix++) if($id == $playerjs[$ix]["steamid"]) {$i = $ix; break;};
    $content = file_get_contents($cheatfile);
    if (substr_count($content, $id) > 0) {
        $content = str_replace($id, null, $content);
        if (file_put_contents($cheatfile, $content)) {
            $alert->code = 101;
            $alert->r("name", $playerjs[$i]["personaname"]);
            $alert->overwrite_text = "{::lang::php::sc::page::home::remove_admin}";
            $resp = $alert->re();
        } else {
            $alert->code = 1;
            $resp = $alert->re();
        }
    }
}




$serv->cfg_read('arkserverroot');
$savedir = $serv->dir_save();
$player_json = $helper->file_to_json('app/json/saves/player_'.$serv->name().'.json', false);
$tribe_json = $helper->file_to_json('app/json/saves/tribes_'.$serv->name().'.json', false);
if (!is_array($player_json)) $player_json = array();
if (!is_array($tribe_json)) $tribe_json = array();
$bool_install = filter_var($serv->isinstalled(), FILTER_VALIDATE_BOOLEAN);
//Liste für Admin
if ($bool_install) {
    if (!file_exists($cheatfile)) file_put_contents($cheatfile, "");
    $jhelper = new player_json_helper();
    $userlist_admin = null;
    $player_json = $helper->file_to_json('app/json/saves/player_'.$serv->name().'.json', false);
    if (!is_array($player_json)) $player_json = array();

    $file = file($cheatfile);

    for ($i=0;$i<count($file);$i++) {
        $find = array("\n", "\r", " ");
        $file[$i] = str_replace($find, null, $file[$i]);
        if (is_numeric($file[$i])) {
            $list_tpl = new Template('list_user_admin.htm', 'app/template/serv/page/list/');
            $list_tpl->load();

            $found = false;
            for ($p=0;$p<count($player_json);$p++) {
                $pl = $jhelper->player($player_json, $p);
                $id = $pl->SteamId;
                if ($id === $file[$i]) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                for ($ix=0;$ix<$count;$ix++) if($id == $playerjs[$ix]["steamid"]) {$ix = $ix; break;};
                $list_tpl->r("igname", $pl->CharacterName);
            } else {
                $list_tpl->r("igname", "{::lang::php::sc::page::home::unknown_name}");
            }

            $list_tpl->r("stid", $playerjs[$ix]["steamid"]);
            $list_tpl->r("url", $playerjs[$ix]["profileurl"]);
            $list_tpl->r("cfg", $serv->name());
            $list_tpl->r("rndb", rndbit(25));
            $list_tpl->r("stname", $playerjs[$ix]["personaname"]);
            $list_tpl->r("img", $playerjs[$ix]["avatarmedium"]);

            $adminlist_admin .= $list_tpl->load_var();
        }
    }

    if (is_array($player_json)) {
        for ($i=0;$i<count($player_json);$i++) {
            $pl = $jhelper->player($player_json, $i);
            $id = $pl->SteamId;
            $name = $pl->SteamName;
            $ig_name = $pl->CharacterName;
            $not = true;
            for ($p=0;$p<count($file);$p++) {
                if (intval($file[$p]) == $id) {
                    $not = false;
                    break;
                }
            }

            if ($not) $userlist_admin .= "<option value='$id'>$name - $ig_name</option>";
        }
    }
} 

if ($ifcadmin) $resp .= $alert->rd(300, 3);

$page_tpl->rif ("installed", $bool_install);
$page_tpl->r("userlist_admin", $userlist_admin);
$page_tpl->r("adminlist_admin", $adminlist_admin);
$page_tpl->session();
$panel = $page_tpl->load_var();

?>