<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$ipath = __ADIR__.'/remote/arkmanager/instances/';
$dir = scandir($ipath);
for ($i=0;$i<count($dir);$i++) {
    $ifile = $ipath.$dir[$i];
    // wenn es ein Verzeichnis ist skippe
    if(is_dir($ifile)) continue;

    $ifile_info = pathinfo($ipath.$dir[$i]);
    $checkit = false;

    if ($ifile_info['extension'] == "cfg" && strpos($ifile_info['filename'], "example") !== true) {
        //erstelle Server Klasse
        $serv = new server($ifile_info['filename']);

        // Erstelle logdateien
        $log = __ADIR__.'/app/json/saves/chat_'.$serv->name().'.log';
        if (!file_exists($log)) file_put_contents($log, " ");
        $pl = __ADIR__.'/app/json/saves/pl_'.$serv->name().'.players';
        if (!file_exists($pl)) file_put_contents($pl, " ");

        if ($serv->isinstalled() == "TRUE") {

            // lade Spielstände & Informationen
            $path = $serv->dir_save();
            $servname = $serv->name();
            $container = null;
            $container = new Container();
            $container->LoadDirectory($path);
            $container->LinkPlayersAndTribes();

            // lösche inhalt von vars
            $json_user = $json_tribe = null;

            // holen Stamm Informationen
            $z = 0;
            foreach($container->Tribes as $tribe)
            {
                $json_tribe[$z]['Id'] = $tribe->Id;
                $json_tribe[$z]['Name'] = $tribe->Name;
                $json_tribe[$z]['OwnerId'] = 0;
                $json_tribe[$z]['FileCreated'] = $tribe->FileCreated;
                $json_tribe[$z]['FileUpdated'] = $tribe->FileUpdated;
                $json_tribe[$z]['Members'] = $tribe->Members;
                $z++;
            }

            // holen Spieler Informationen
            $z = 0;
            foreach($container->Players as $Players)
            {
                $json_user[$z]['Id'] = $Players->Id;
                $json_user[$z]['SteamId'] = $Players->SteamId;
                $json_user[$z]['SteamId'] = str_replace(".arkprofile", null, $json_user[$z]['SteamId']);
                $json_user[$z]['SteamId'] = intval($json_user[$z]['SteamId']);
                $json_user[$z]['SteamName'] = null;
                $json_user[$z]['CharacterName'] = $Players->CharacterName;
                $json_user[$z]['Level'] = $Players->Level;
                $json_user[$z]['ExperiencePoints'] = $Players->ExperiencePoints;
                $json_user[$z]['TotalEngramPoints'] = $Players->TotalEngramPoints;
                $json_user[$z]['FirstSpawned'] = $Players->FirstSpawned;
                $json_user[$z]['FileCreated'] = $Players->FileCreated;
                $json_user[$z]['FileUpdated'] = $Players->FileUpdated;
                $json_user[$z]['TribeId'] = $Players->TribeId;
                $json_user[$z]['TribeName'] = $Players->TribeName;
                $z++;
            }

            // Debug
            // var_dump($json_user); echo "<hr>"; var_dump($json_tribe); echo "<hr>";

            // Lese Stämme um diese in die Datenbank einzutragen
            if(is_array($json_tribe) && is_countable($json_tribe)) {
                foreach($json_tribe as $k => $v) {
                    $query = null;
                    $mycon->query("SELECT * FROM `ArkAdmin_tribe` WHERE `Id`='". $v["Id"] ."' AND  `server`='$servname'");
                    if($mycon->numRows() > 0) {
                        $row = $mycon->fetchArray();
                        $query = "UPDATE `ArkAdmin_tribe` SET 
                        `Id` = '".$v["Id"]."', 
                        `tribeName` = '".$v["Name"]."', 
                        `OwnerId` = '".$v["OwnerId"]."', 
                        `FileCreated` = '".$v["FileCreated"]."', 
                        `FileUpdated` = '".$v["FileUpdated"]."', 
                        `Members` = '".json_encode($v["Members"])."'
                    WHERE total_id = '". $row['total_id'] ."';";
                    }
                    else {
                        $query = "INSERT INTO `ArkAdmin_tribe` VALUES
                    (
                        null, 
                        '$servname', 
                        '".$v["Id"]."', 
                        '".$v["Name"]."', 
                        '".$v["OwnerId"]."', 
                        '".$v["FileCreated"]."', 
                        '".$v["FileUpdated"]."', 
                        '".json_encode($v["Members"])."'
                    );";
                    }
                    if($query !=  null) $mycon->query($query);
                }
            }

            // Lese Spieler um diese in die Datenbank einzutragen
            if(is_array($json_user) && is_countable($json_user)) {
                foreach($json_user as $k => $v) {
                    $query = null;
                    $mycon->query("SELECT * FROM `ArkAdmin_players` WHERE `id`='". $v["Id"] ."' AND  `server`='$servname'");
                    if($mycon->numRows() > 0) {
                        $row = $mycon->fetchArray();
                        $query = "UPDATE `ArkAdmin_players` SET 
                        `id` = '".$v["Id"]."', 
                        `SteamId` = '".$v["SteamId"]."', 
                        `SteamName` = '".$v["SteamName"]."', 
                        `CharacterName` = '".$v["CharacterName"]."', 
                        `Level` = '".$v["Level"]."', 
                        `ExperiencePoints` = '".$v["ExperiencePoints"]."', 
                        `TotalEngramPoints` = '".$v["TotalEngramPoints"]."', 
                        `FirstSpawned` = '".$v["FirstSpawned"]."', 
                        `FileCreated` = '".$v["FileCreated"]."', 
                        `FileUpdated` = '".$v["FileUpdated"]."', 
                        `TribeId` = '".$v["TribeId"]."',
                        `TribeName` = '".$v["TribeName"]."'
                    WHERE total_id = '". $row['total_id'] ."';";
                    }
                    else {
                        $query = "INSERT INTO `ArkAdmin_players` VALUES
                    (
                        null, 
                        '$servname', 
                        '".$v["Id"]."', 
                        '".$v["SteamId"]."', 
                        '".$v["SteamName"]."', 
                        '".$v["CharacterName"]."', 
                        '".$v["Level"]."', 
                        '".$v["ExperiencePoints"]."', 
                        '".$v["TotalEngramPoints"]."', 
                        '".$v["FirstSpawned"]."', 
                        '".$v["FileCreated"]."', 
                        '".$v["FileUpdated"]."', 
                        '".$v["TribeId"]."', 
                        '".$v["TribeName"]."'
                    );";
                    }
                    if($query !=  null) $mycon->query($query);
                }
            }
        }
        // lösche inhalt von vars
        $container = $json_user = $json_tribe = null;
    }
}
