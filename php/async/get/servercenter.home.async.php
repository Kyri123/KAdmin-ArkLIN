<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg = $_GET['cfg'];
$case = $_GET['case'];

switch ($case) {
    // CASE: Whitelist list
    case "loadwhite":
        //erstelle SteamAPI von Savegames

        $serv = new server($cfg);
        $whitelistfile = $serv->dir_main()."/ShooterGame/Binaries/Linux/PlayersJoinNoCheckList.txt";
        $file = file($whitelistfile);
        $arr = [];

        if (is_array($file)) {
            for ($i = 0; $i < count($file); $i++) {
                $find = array("\n", "\r", " ");
                $file[$i] = str_replace($find, null, $file[$i]);
                if($file[$i] != "0" && $file[$i] != "" && $file[$i] != null) $arr[] = $file[$i];
            }
        }
        
        $steamapi->getsteamprofile_list("whitelist_".$serv->name(), $arr, 0);
        $file = $helper->file_to_json('app/json/steamapi/profile_whitelist_'.$serv->name().'.json', true)["response"]["players"];

        for ($i=0;$i<count($file);$i++) {
            $list_tpl = new Template('whitelist.htm', 'app/template/lists/serv/jquery/');
            $list_tpl->load();

            $list_tpl->r("sid", $file[$i]["steamid"]);
            $list_tpl->r("url", $file[$i]["profileurl"]);
            $list_tpl->r("cfg", $serv->name());
            $list_tpl->r("rndb", rndbit(25));
            $list_tpl->r("name", $file[$i]["personaname"]);
            $list_tpl->r("img", $file[$i]["avatarmedium"]);

            $adminlist_admin .= $list_tpl->load_var();
        }
        echo $adminlist_admin;
    break;
    
    default:
        echo "Case not found";
    break;
}
$mycon->close();
?>