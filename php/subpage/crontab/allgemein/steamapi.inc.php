<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

$file           = __ADIR__.'/app/json/serverinfo/all.json';
$cfg_json       = $helper->fileToJson($file);
$sid_array      = $modid_array = array();
$player_array   = $steamapi_user;
$mod_array      = $steamapi_mods;

foreach ($cfg_json["cfgs"] as $v) {
    $name           = str_replace(".cfg", null, $v);
    $serv           = new server($name);
    if($serv !== false) {
        $cheatfile      = $serv->dirSavegames(true)."/AllowedCheaterSteamIDs.txt";
        $whitelistfile  = $serv->dirMain()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";
        $exp            = explode(",", $serv->cfgRead("ark_GameModIds"));

        if(is_array($exp)) {
            foreach ($exp as $item) if(!in_array($item, $modid_array) && $item != "") $modid_array[] = $item;
        } else {
            if(is_numeric($serv->cfgRead("ark_GameModIds"))) $modid_array[] = $serv->cfgRead("ark_GameModIds");
        }

        // lese Adminliste
        $files          = [];
        $files[]        = @file($KUTIL->path($cheatfile)["/path"]);
        $files[]        = @file($KUTIL->path($whitelistfile)["/path"]);

        foreach ($files as $key => $item) {
            if (is_array($item)) {
                for ($i = 0; $i < count($item); $i++) {
                    $item[$i] = trim($item[$i]);
                    if(
                        $item[$i] != "0" &&
                        $item[$i] != "" &&
                        $item[$i] != null &&
                        !in_array($item[$i], $sid_array)
                    ) $sid_array[] = $item[$i];
                }
            }
        }
    }
}

$arr        = $mycon->query("SELECT `SteamId` FROM ArkAdmin_players")->fetchAll();
foreach ($arr as $v) if(!in_array($v["SteamId"], $sid_array)) $sid_array[] = $v["SteamId"];

$json       = $steamapi->getsteamprofile_list("allg", $sid_array, 360)->response->players;
$i          = 0;
foreach ($json as $key => $item) {
    if(isset($item->steamid)) {
        $sid    = intval($item->steamid);
        foreach ($item as $k => $v) $player_array[$sid][$k] = $v;
        $i++;
    }
}

$json       = $steamapi->getmod_list("allg", $modid_array, 360)->response->publishedfiledetails;

$i          = 0;
foreach ($json as $key => $item) {
    if(isset($item->publishedfileid)) {
        $sid = intval($item->publishedfileid);
        foreach ($item as $k => $v) $mod_array[$sid][$k] = $v;
        $i++;
    }
}

$path       = __ADIR__."/app/json/steamapi";
$helper->saveFile($player_array, "$path/user.json");
$helper->saveFile($mod_array, "$path/mods.json");