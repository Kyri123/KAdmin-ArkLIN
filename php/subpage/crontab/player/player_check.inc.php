<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$ipath = 'remote/arkmanager/instances/';
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
        $log = 'app/json/saves/chat_'.$serv->name().'.log';
        if (!file_exists($log)) file_put_contents($log, " ");
        $pl = 'app/json/saves/pl_'.$serv->name().'.players';
        if (!file_exists($pl)) file_put_contents($pl, " ");

        if ($serv->isinstalled() == "TRUE") {

            // lade Spielstände & Informationen
            $path = $serv->dir_save();
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
                $json_tribe[$z]['OwnerId'] = $container->Tribes[0]->Owner->SteamId;
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

            // Speicher Informationen / encode
            if ($json_user_enc = json_encode($json_user, JSON_INVALID_UTF8_SUBSTITUTE)) file_put_contents('app/json/saves/tribes_'.$serv->name().'.json', $json_user_tribe);
            if ($json_user_tribe = json_encode($json_tribe, JSON_INVALID_UTF8_SUBSTITUTE)) file_put_contents('app/json/saves/player_'.$serv->name().'.json', $json_user_enc);
        }
        // lösche inhalt von vars
        $container = $json_user = $json_tribe = null;
    }
}
?>