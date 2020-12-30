<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg    = $_GET['cfg'];
$case   = $_GET['case'];

switch ($case) {
    // CASE: Whitelist list
    case "loadwhite":
        //erstelle SteamAPI von Savegames

        $serv               = new server($cfg);
        $whitelistfile      = $serv->dirMain()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";
        $file               = file($whitelistfile);
        $arr                = [];
        $adminlist_admin    = null;

        if (is_array($file))
            for ($i = 0; $i < count($file); $i++) {
                $file[$i] = trim($file[$i]);
                if($file[$i] != "0" && $file[$i] != "" && $file[$i] != null) $arr[] = $file[$i];
            }

        if(is_countable($arr) && is_array($arr) && count($arr) > 0) {
            for ($i=0;$i<count($arr);$i++) {
                $list_tpl   = new Template('whitelist.htm', __ADIR__.'/app/template/lists/serv/jquery/');
                $query      = $mycon->query("SELECT * FROM ArkAdmin_players WHERE `server`= ? AND `SteamId`= ? ", $serv->name(), $arr[$i]);

                $list_tpl->load();
                if($query->numRows() > 0) {
                    $row = $query->fetchArray();
                    $list_tpl->r("name", $steamapi_user[$arr[$i]]["personaname"] . " (". $row["CharacterName"] .")");
                }
                else {
                    $list_tpl->r("name", $steamapi_user[$arr[$i]]["personaname"]);
                }

                $list_tpl->r("sid", $steamapi_user[$arr[$i]]["steamid"]);
                $list_tpl->r("url", $steamapi_user[$arr[$i]]["profileurl"]);
                $list_tpl->r("cfg", $serv->name());
                $list_tpl->r("rndb", rndbit(25));
                $list_tpl->r("img", $steamapi_user[$arr[$i]]["avatarmedium"]);
                $list_tpl->rif("hidebtn", false);

                $adminlist_admin .= $list_tpl->load_var();
            }
        }
        else {
            $list_tpl = new Template('whitelist.htm', __ADIR__.'/app/template/lists/serv/jquery/');
            $list_tpl->load();

            $list_tpl->r("sid", 0);
            $list_tpl->r("name", "{::lang::allg::default::noplayer}");
            $list_tpl->r("cfg", $serv->name());
            $list_tpl->r("rndb", rndbit(25));
            $list_tpl->r("img", "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
            $list_tpl->rif("hidebtn", true);

            $adminlist_admin .= $list_tpl->load_var();
        }
        echo $adminlist_admin;
    break;
    
    default:
        echo "Case not found";
        break;
}
$mycon->close();
