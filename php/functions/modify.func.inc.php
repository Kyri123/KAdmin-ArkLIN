<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

/**
 * Filtern shell informationen aus einem String & modifiziert ihn (für Logdateien gedacht)
 *
 * @param  mixed $str
 * @return void
 */

function filtersh($str) {
    $search  = array(
        "\e[0;39m",
        "\e[1;32m",
        "\e[1;31m",
        " \n",
        "   ",
        "  ] 	",
        " 0 \/ ",
        "  \e[1;32m ",
        "  \e[1;31m ",
        " \e[0;39m ] 	",
        "[ \e[1;31m ",
        "\e8\e[J",
        "\e7",
        "[ \e[0;33m WARN \e[0;39m ] 	",
        "\n"
    );
    $replace = array(
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        ' ',
        ' ',
        ' ',
        '[WARN]',
        null
    );
    $str = str_replace($search, $replace, $str);
    return $str;
}

function sh_crontab($str) {
    $search  = array(
        "\e[0;39m",
        "\e[1;32m",
        "\e[1;31m",
        " \n",
        "   ",
        "  ] 	",
        " 0 \/ ",
        "  \e[1;32m ",
        "  \e[1;31m ",
        " \e[0;39m ] 	",
        "[ \e[1;31m ",
        "\e8\e[J",
        "\e7",
        "[ \e[0;33m WARN \e[0;39m ] 	"
    );
    $replace = array(
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        ' ',
        ' ',
        ' ',
        '[WARN]'
    );
    $str = str_replace($search, $replace, $str);
    return $str;
}

function alog($str) {
    global $helper;
    $steamapi_mods = (file_exists("app/json/steamapi/mods.json")) ? $helper->file_to_json("app/json/steamapi/mods.json", true) : array();

    $search  = array(
        '', // 9
        '[K', // 10
        "\033[0;39m\e[68G[", // 11
        "\033[0;39m ", // 12
        '[0;39m[68G[   [1;32mOK[0;39m   ]', // 21
        '\033[0;39m\e[68G[ \033[1;31mFAILED\033[0;39m ]', // 22
        'WARNING [0;39m ] 	', // 23
        'ERROR [0;39m ] 	', // 24
        "[ \e[0;33m WARN ]", // 25
        '\033[0;39m ', // 28
        ' ]', // 29
    );

    $replace = array(
        null, // 9
        null, // 10
        null, // 11
        null, // 12
        '<b class="text-success">[OK]</b>', // 21
        '<b class="text-danger">[FAIL]</b>', // 22
        '<b class="text-warning">WARNING </b> ', // 23
        '<b class="text-danger">ERROR </b> ', // 24
        '<b class="text-warning">[WARN] </b> ', // 25
        null, // 28
        null, // 29
    );
    $str = str_replace($search, $replace, $str);

    /* TODO Lang
    $search  = array(
        'The server is starting', // 1
        'for instance ', // 2
        'Running command ', // 3
        'ARK world file ', // 4
        'ARK profile files', // 5
        'ARK tribe files ', // 6
        'ARK tribute tribe files ', // 7
        'Compressing Backup ', // 8
        '', // 9
        '[K', // 10
        "\033[0;39m\e[68G[", // 11
        "\033[0;39m ", // 12
        'Your server is already up to date! The most recent version is', // 13
        'Retries exhausted', // 14
        'The server is now running, and should be up within 10 minutes', // 15
        'Saved arks directory is ', // 16
        'Copying ', // 17
        'Copying files to ', // 18
        'Created Backup: ', // 19
        ' Yes ', // 20
        '[0;39m[68G[   [1;32mOK[0;39m   ]', // 21
        '\033[0;39m\e[68G[ \033[1;31mFAILED\033[0;39m ]', // 22
        'WARNING [0;39m ] 	', // 23
        'ERROR [0;39m ] 	', // 24
        "[ \e[0;33m WARN ]", // 25
        " No ", // 26
        "Installing ARK server ...", // 27
        '\033[0;39m ', // 28
        ' ]', // 29
        "Checking for update;", // 30
        "Checking for updates before starting", // 31
        "The server is already stopped", // 32
        "All mods are up to date", // 33
        "Querying Steam database for latest version...", // 34
        "Current version", // 35
        "Available version", // 36
        "Your server is up to date!", // 37
        "The server has been stopped", // 38
        "Stopping server; reason: shutdown", // 39
        "World Saved" // 40
    );

    $replace = array(
        'Der Server startet...', // 1
        ' aus für die Instanz ', // 2
        'Führe Aktion ', // 3
        'ARK Welt / Karte ... ', // 4
        'Charaktere (Profile) ... ', // 5
        'Stämme ... ', // 6
        'Stämme > Charaktere ... ', // 7
        'Komprimiere Backup ... ', // 8
        null, // 9
        null, // 10
        null, // 11
        null, // 12
        'Der Server ist auf der aktuellen Version:', // 13
        'Neuer versuch wird gestartet', // 14
        'Der Server läuft und sollte in ca. <b>10 Minuten <span class="text-success">Online</span></b> sein.', // 15
        '<b class="text-gray-800">Speicherverzeichnis ist: </b>', // 16
        '<b class="text-gray-800">Kopiere: </b>', // 17
        '<b class="text-gray-800">Kopiere Dateien nach: </b>', // 18
        '<b class="text-gray-800">Erstelle Backup: </b>', // 19
        '<b class="text-success"> Ja</b>', // 20
        '<b class="text-success">[OK]</b>', // 21
        '<b class="text-danger">[Fehlgeschlagen]</b>', // 22
        '<b class="text-warning">WARNUNG</b> ', // 23
        '<b class="text-danger">ERROR</b> ', // 24
        '<b class="text-warning">WARNUNG:</b> ', // 25
        "<b class=\"text-danger\"> Nein</b>", // 26
        "Installiere Server...   ", // 27
        null, // 28
        null, // 29
        "Prüfe auf Updates", // 30
        "Prüfe auf Updates bevor der Server startet.", // 31
        "Der Server ist schon gestoppt!", // 32
        "Alle Mods sind auf dem neusten Stand!", // 33
        "Hole Daten von Steam...", // 34
        "Aktuelle Version", // 35
        "Verfügbare Version", // 36
        "Dein Server ist auf dem neusten Stand!", // 37
        "Der Server wurde Angehalten", // 38
        "Stoppe Server; Grund: <b>Shutdown</b>", // 39
        "Server Gespeichert" // 40
    );
    $str = str_replace($search, $replace, $str);*/

    if (strpos($str, '] Updating mod') !== false) {
        $time = explode('[', $str);
        $time = $time[0];
        $modid = explode(']', $str);
        $modid = trim($modid[1]);
        $modid = str_replace('Updating mod ', null, $modid);
        $modid = str_replace(" ", null, $modid);
        $json = $steamapi_mods[$modid];
        $str = $time.' <b class="text-gray-800">Updating Mod: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $steamapi_mods[$modid]["title"].'</a></b>';
    }
    if (strpos($str, '] Mod') !== false) {
        $time = explode('[', $str);
        $time = $time[0];
        $modid = explode(']', $str);
        $modid = trim($modid[1]);
        $modid = str_replace('Updating mod ', null, $modid);
        $modid = str_replace(" ", null, $modid);
        $modid = str_replace("Mod", null, $modid);
        $modid = str_replace("updated", null, $modid);
        $str = $time.' <b class="text-success">Update done: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $steamapi_mods[$modid]["title"].'</a></b>';
    }
    if (strpos($str, 'Updating mod') !== false) {
        $modid = str_replace('Updating mod ', null, $str);
        $modid = str_replace(" ", null, $modid);
        $modid = trim($modid);
        $str = '<b class="text-gray-800">Updating Mod: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $steamapi_mods[$modid]["title"].'</a></b>';
    }
    if (strpos($str, 'not fully downloaded') !== false) {
        $modid = str_replace(' not fully downloaded - retrying', null, $str);
        $modid = str_replace("Mod ", null, $modid);
        $modid = trim($modid);
        $json = $steamapi_mods[$modid];
        $str = '<b class="text-gray-800">Not fully downloaded: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $steamapi_mods[$modid]["title"] .'</a> (retry)</b>';
    }
    if (strpos($str, ' installed') !== false) {
        $modid = str_replace('Mod ', null, $str);
        $modid = str_replace(" installed", null, $modid);
        $modid = trim($modid);
        $json = $steamapi_mods[$modid];
        $str = '<b class="text-gray-800">Installed: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $steamapi_mods[$modid]["title"] .'</a></b>';
    }
    if (strpos($str, 'Downloading mod ') !== false) {
        $modid = str_replace('Downloading mod ', null, $str);
        $modid = str_replace(" ", null, $modid);
        $modid = explode('...', $modid);
        $done = $modid[1];
        $modid = trim($modid[0]);
        $json = $steamapi_mods[$modid];
        $str = '<b class="text-gray-800">Downloading: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $steamapi_mods[$modid]["title"].'</a></b>';

        if (strpos($done, 'downloaded') !== false) {
            $str .= ' | <span class="text-success">Done!</span>';
        }
    }
    /*
    if (strpos($str, 'Performing ARK update') !== false) {
        //$modid = str_replace('Performing ARK update ', null, $str);
        //$modid = str_replace(" ", null, $modid);
        $txt = explode('...', $str);
        $txt = explode('to ', $txt[1]);
        $txt[1] = str_replace(" complete", null, $txt[1]);
        $txt[1] = str_replace(" ", null, $txt[1]);
        $str = '<b class="text-gray-800">Installing new Ark-update</b>';

        if ($txt[1] > 0) {
            $str .= ' | <span class="text-success">Done! New build: <b>'.$txt[1].'</b></span>';
        }
    }*/
    return $str;
}


/**
 * Wandelt eine Array rekursiv in ein HTML editor
 *
 * @param  array $array
 * @param  string $str
 * @param  array $fullarray - Bitte null lassen
 * @return void
 */
function perm_to_htm(array $array, $keys = null) {
    $fullarray = $array;
    $str = null;
    if(isset($fullarray["all"]["is_admin"]) && $fullarray["all"]["is_admin"] == 1) {
        // Erstelle Head
        $tpl = new Template("edit.htm", "app/template/lists/userpanel/");
        $tpl->load();
        $tpl->rif("is_array", true);
        $tpl->r("key", "{::lang::php::userpanel::permissions::all}");
        $tpl->r("keys", "[all]");
        $str .= $tpl->load_var();

        $tpl = null;

        // Erstelle Input
        $tpl = new Template("edit.htm", "app/template/lists/userpanel/");
        $tpl->load();
        $tpl->rif("is_array", false);
        $tpl->r("key", "{::lang::php::userpanel::permissions::is_admin}");
        $tpl->r("keys", "[all][is_admin]");
        $tpl->rif("active", true);
        $str .= $tpl->load_var();

        $tpl = null;
    }
    else {
        foreach ($array as $key => $item) {
            $tpl = new Template("edit.htm", "app/template/lists/userpanel/");
            $tpl->load();
            $tpl->rif("is_array", is_array($item));
            if(is_array($item)) {
                if($keys == "[server]") {
                    if(file_exists("remote/arkmanager/instances/$key.cfg")) {
                        if(isset($item["is_server_admin"]) && $item["is_server_admin"] == 1) {
                            $serv = new server($key);

                            // Erstelle Head
                            $tpl = new Template("edit.htm", "app/template/lists/userpanel/");
                            $tpl->load();
                            $tpl->rif("is_array", true);
                            $tpl->r("key", $serv->cfg_read("ark_SessionName"));
                            $tpl->r("keys", "[server][$key]");
                            $str .= $tpl->load_var();

                            $tpl = null;

                            // Erstelle Input
                            $tpl = new Template("edit.htm", "app/template/lists/userpanel/");
                            $tpl->load();
                            $tpl->rif("is_array", false);
                            $tpl->r("key", "{::lang::php::userpanel::permissions::is_server_admin}");
                            $tpl->r("keys", "[server][$key][is_server_admin]");
                            $tpl->rif("active", true);
                            $str .= $tpl->load_var();

                            $tpl = null;
                        }
                        else {
                            // Erstelle Head
                            $serv = new server($key);
                            $tpl->r("key", $serv->cfg_read("ark_SessionName"));
                            $keyw = $keys."[$key]";
                            $tpl->r("keys", $keyw);
                            $str .= $tpl->load_var();
                            $str .= perm_to_htm($item, $keyw);
                        }
                    }
                }
                else {
                    // Erstelle Head
                    $keyw = $keys."[$key]";
                    $tpl->r("key", "{::lang::php::userpanel::permissions::$key}");
                    $tpl->r("keys", $keyw);
                    $str .= $tpl->load_var();
                    $str .= perm_to_htm($item, $keyw);
                }
            }
            else {
                // Erstelle Input
                $keyw = $keys."[$key]";
                $tpl->r("key", "{::lang::php::userpanel::permissions::$key}");
                $tpl->r("keys", $keyw);
                $tpl->rif("active", boolval($item));
                $str .= $tpl->load_var();
            }
        }
    }
    return $str;
}

/**
 * Modifiziert die Shell anfrage um sie zu sichern
 *
 * @param  mixed $command
 * @return void
 */
function saveshell($command) {
    $forbitten = array(";", "?", "|", "OR", "AND", "passwd", "reboot", "shutdown", "service", "apt", "sudo");
    return str_replace($forbitten, null, $command);
}

?>