<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/


$file = 'app/json/serverinfo/all.json';
$cfg_json = $helper->file_to_json($file);

$sid_array = $modid_array = array();
$player_array = $steamapi_user;
$mod_array = $steamapi_mods;

foreach ($cfg_json["cfgs"] as $v) {
    $name = str_replace(".cfg", null, $v);
    $serv = new server($name);

    $cheatfile = $serv->dir_save(true)."/AllowedCheaterSteamIDs.txt";
    $whitelistfile = $serv->dir_main()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";

    $exp = explode(",", $serv->cfg_read("ark_GameModIds"));
    foreach ($exp as $item) if(!in_array($item, $modid_array)) $modid_array[] = $item;

    // lese Adminliste
    $file = file($cheatfile);
    if (is_array($file)) {
        for ($i = 0; $i < count($file); $i++) {
            $file[$i] = trim($file[$i]);
            if(
                $file[$i] != "0" &&
                $file[$i] != "" &&
                $file[$i] != null &&
                !in_array($file[$i], $sid_array)
            ) $sid_array[] = $file[$i];
        }
    }

    // lese Whitelist
    $file = file($whitelistfile);
    if (is_array($file)) {
        for ($i = 0; $i < count($file); $i++) {
            $file[$i] = trim($file[$i]);
            if(
                $file[$i] != "0" &&
                $file[$i] != "" &&
                $file[$i] != null &&
                !in_array($file[$i], $sid_array)
            ) $sid_array[] = $file[$i];
        }
    }
}

$query = "SELECT `SteamId` FROM ArkAdmin_players";
$arr = $mycon->query($query)->fetchAll();

foreach ($arr as $v) {
    if(!in_array($v["SteamId"], $sid_array)) $sid_array[] = $v["SteamId"];
}

$json = $steamapi->getsteamprofile_list("allg", $sid_array, 360)->response->players;
$i = 0;
foreach ($json as $key => $item) {
    $sid = intval($item->steamid);
    foreach ($item as $k => $v) {
        $player_array[$sid][$k] = $v;
    }
    $i++;
}

$json = $steamapi->getmod_list("allg", $modid_array, 360)->response->publishedfiledetails;
$i = 0;
foreach ($json as $key => $item) {
    $sid = intval($item->publishedfileid);
    foreach ($item as $k => $v) {
        $mod_array[$sid][$k] = $v;
    }
    $i++;
}

$path = "app/json/steamapi";

$helper->savejson_create($player_array, "$path/user.json");
$helper->savejson_create($mod_array, "$path/mods.json");
