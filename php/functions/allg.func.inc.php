<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/
function setico($target, $ico = null) {
    if(is_dir($target)) {
        $ico = '<i class="nav-icon fas fa-folder-open" aria-hidden="true"></i>';
    }
    else {
        $type = pathinfo($target)['extension'];
        if($type == "sh" || $type == "ini") {
            $ico = '<i class="nav-icon fa fa-file-code-o" aria-hidden="true"></i>';
        }
        elseif($type == "txt" || $type == "log") {
            $ico = '<i class="nav-icon fa fa-file-text-o" aria-hidden="true"></i>';
        }
        elseif($type == "ark" || $type == "bak") {
            $ico = '<i class="nav-icon fa fa-map-o" aria-hidden="true"></i>';
        }
        elseif($type == "arkprofile" || $type == "profilebak") {
            $ico = '<i class="nav-icon fa fa-user" aria-hidden="true"></i>';
        }
        elseif($type == "tribebak" || $type == "arktributetribe" || $type == "arktribe") {
            $ico = '<i class="nav-icon fa fa-users" aria-hidden="true"></i>';
        }
        elseif($type == "pnt") {
            $ico = '<i class="nav-icon fa fa-file-image-o" aria-hidden="true"></i>';
        }
        else {
            $ico = '<i class="nav-icon fa fa-file-o" aria-hidden="true"></i>';
        }
    }
    return $ico;
}

function convertstate($serverstate) {
    if ($serverstate == 0) {
        $serv_state = "{::lang::php::function_allg::state_off}";
        $serv_color = "danger";
    }
    elseif ($serverstate == 1) {
        $serv_state = "{::lang::php::function_allg::state_start}";
        $serv_color = "info";
    }
    elseif ($serverstate == 2) {
        $serv_state = "{::lang::php::function_allg::state_on}";
        $serv_color = "success";
    }
    elseif ($serverstate == 3) {
        $serv_state = "{::lang::php::function_allg::state_notinstalled}";
        $serv_color = "warning";
    }
    return array("color" => $serv_color,"str" => $serv_state);
}

function mem_perc(){

    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;
    $memory_usage = round($memory_usage, 2);
    return $memory_usage;
}

function mem_array(){
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage[0] = $mem[1];
    $memory_usage[1] = $mem[2];
    return $memory_usage;
}

function cpu_perc(){

    $load = sys_getloadavg();
    return $load[0];

}

#user allgmeine daten
function getuserdata($data, $id) {
    global $mycon;
    $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$id.'\'';
    if ($mycon->query($query)->numRows() > 0) {
        $row = $mycon->query($query)->fetchArray();
        return $row[$data];
    } else {
        return '{::lang::php::function_allg::acc_notfound}';
    }
}

function rndbit($l) {
    return bin2hex(random_bytes($l));
}


function del_dir( $dir )
{
    if ( is_dir( $dir ) )
    {
        $dir_handle = opendir( $dir );
        if ( $dir_handle )
        {
            while( $file = readdir( $dir_handle ) )
            {
                if ($file != "." && $file != "..")
           {
               if ( ! is_dir( $dir."/".$file ) )
               {
                   unlink( $dir."/".$file );
               }
               else
               {
                   unlink($dir.'/'.$file);
               }

           }
      }
            closedir( $dir_handle );
        }
        rmdir( $dir );
        return true;
    }
    return false;
}

function bitrechner( $size, $sourceUnit = 'bit', $targetUnit = 'MB' ) {
    $units = array(
        'bit' => 0,
        'B' => 1,
        'KB' => 2,
        'MB' => 3,
        'GB' => 4
    );

    if ( $units[$sourceUnit] <= $units[$targetUnit] ) {
        for ( $i = $units[$sourceUnit]; $size >= 1024; $i++ ) {
            if ( $i === 0 ) {
                $size /= 8;
            } else {
                $size /= 1024;
            }
        }
    } else {
        for ( $i = $units[$sourceUnit]; $i > $units[$targetUnit]; $i-- ) {
            if ( $i === 1 ) {
                $size *= 8;
            } else {
                $size *= 1024;
            }
        }
    }
    return round( $size, 2 ) . ' ' . array_keys($units)[$i];
}

function meld($type, $txt = '{ALERT_TXT}', $title = '{ALERT_TITLE}', $setcicon = null, $rounded = "rounded-0") {
    $rnd = bin2hex(random_bytes(50));
    $icon = "fas fa-question";
    if ($type == "info") $icon = "fas fa-info";
    if ($type == "danger") $icon = "fas fa-exclamation-triangle";
    if ($type == "warning") $icon = "fas fa-exclamation-circle";
    if ($type == "success") $icon = "fas fa-check";
    if ($setcicon != null) $icon = $setcicon;
    return '<div class="card card-'.$type.' '.$rounded.'" id="'.$rnd.'">
              <div class="card-header '.$rounded.'">
                <h3 class="card-title"><i class="icon '.$icon.'"></i> '.$title.'</h3>

                <div class="card-tools">
                      <button type="button" class="btn btn-tool" onclick="remove(\''.$rnd.'\')">
                            <i class="fas fa-times"></i>
                      </button>
                </div>
              </div>
              <div class="card-body '.$rounded.'">
                    '.$txt.'
              </div>
            </div>';
}
function meld_full($type, $txt = '{ALERT_TXT}', $title = '{ALERT_TITLE}', $setcicon = null, $rounded = "rounded-0") {
    $icon = "fas fa-question";
    if ($type == "info") $icon = "fas fa-info";
    if ($type == "danger") $icon = "fas fa-exclamation-triangle";
    if ($type == "warning") $icon = "fas fa-exclamation-circle";
    if ($type == "success") $icon = "fas fa-check";
    if ($setcicon != null) $icon = $setcicon;
    return '<div class="alert alert-'.$type.' alert-dismissible '.$rounded.'">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                  <h5><i class="icon '.$icon.'"></i> '.$title.'</h5>
                  '.$txt.'
                </div>';
}


# Time
function converttime($stamp, $withsec = false, $onlydate = false) {
    if ($withsec) return date("d.m.Y H:i:s", $stamp);
    if ($onlydate) return date("d.m.Y", $stamp);
    return date("d.m.Y H:i", $stamp);
}

# Alerts
function alert($type, $text = '{ALERT_TXT}', $css = null, $title = '{ALERT_TITLE}') {
    $rnd = bin2hex(random_bytes(50));
    return '<div class="card card-'.$type.' '.$css.'" id="'.$rnd.'">
              <div class="card-header">
                <h3 class="card-title">'.$title.'</h3>

                <div class="card-tools">
                      <button type="button" class="btn btn-tool" onclick="remove(\''.$rnd.'\')">
                            <i class="fas fa-times"></i>
                      </button>
                </div>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                    '.$text.'
              </div>
              <!-- /.card-body -->
            </div>';
}

function write_ini_file($array, $file)
{
    $res = array();
    foreach($array as $key => $val)
    {
        if (is_array($val))
        {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = $skey."=".(is_numeric($sval) ? $sval : '"'.$sval.'"');
        }
        else $res[] = $key."=".(is_numeric($val) ? $val : '"'.$val.'"');
    }
    safefilerewrite($file, implode("\n", $res));
}

function safefilerewrite($fileName, $dataToSave)
{    if ($fp = fopen($fileName, 'w'))
{
    $startTime = microtime(TRUE);
    do
    {
        $canWrite = flock($fp, LOCK_EX);
        if (!$canWrite) usleep(round(rand(0, 100)*1000));
    } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

    if ($canWrite)
    {            fwrite($fp, $dataToSave);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

}

function dirToArray($dir) { 

    $result = array();

    $cdir = scandir($dir);
    foreach ($cdir as $key => $value)
    {
        if (!in_array($value,array(".","..")))
        {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
            {
                $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
            }
            else
            {
                $result[] = $value;
            }
        }
    }

    return $result;
}

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
    global $steamapi;
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

    /*$search  = array(
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
        $modid = $modid[1];
        $modid = str_replace('Updating mod ', null, $modid);
        $modid = str_replace(" ", null, $modid);
        $json = $steamapi->getmod($modid);
        $str = $time.' <b class="text-gray-800">Updating Mod: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';
    }
    if (strpos($str, '] Mod') !== false) {
        $time = explode('[', $str);
        $time = $time[0];
        $modid = explode(']', $str);
        $modid = $modid[1];
        $modid = str_replace('Updating mod ', null, $modid);
        $modid = str_replace(" ", null, $modid);
        $modid = str_replace("Mod", null, $modid);
        $modid = str_replace("updated", null, $modid);
        $json = $steamapi->getmod($modid);
        $str = $time.' <b class="text-success">Update done: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';
    }
    if (strpos($str, 'Updating mod') !== false) {
        $modid = str_replace('Updating mod ', null, $str);
        $modid = str_replace(" ", null, $modid);
        $json = $steamapi->getmod($modid);
        $str = '<b class="text-gray-800">Updating Mod: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';
    }
    if (strpos($str, 'not fully downloaded') !== false) {
        $modid = str_replace(' not fully downloaded - retrying', null, $str);
        $modid = str_replace("Mod ", null, $modid);
        $mod = $steamapi->getmod_class($modid);
        $str = '<b class="text-gray-800">not fully downloaded: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $mod->title .'</a> (neuer Versuch)</b>';
    }
    if (strpos($str, 'Downloading mod ') !== false) {
        $modid = str_replace('Downloading mod ', null, $str);
        $modid = str_replace(" ", null, $modid);
        $modid = explode('...', $modid);
        $done = $modid[1];
        $modid = $modid[0];
        $json = $steamapi->getmod($modid);
        $str = '<b class="text-gray-800">Downloading: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';

        if (strpos($done, 'downloaded') !== false) {
            $str .= ' | <span class="text-success">Done!</span>';
        }
    }
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
    }
    return $str;
}

function ini_save_rdy($str) {
    $str = str_replace("\r", null, $str);
    $str = str_replace("\t", null, $str);
    return $str;
}

function get_lang_list() {
    $re = null;
    $dir = "app/lang";
    $dir = dirToArray("app/lang");
    foreach($dir as $k => $v) {
        $ftpl = new Template("lang.htm", "app/template/default/");
        $ftpl->load();

        $xml = new xml_helper("app/lang/$k/info.xml");
        $arr = $xml->array();

        $ftpl->r("lang_icon", $arr["icon_path"]);
        $ftpl->r("lang_short", $k);
        $ftpl->r("lang_name", $arr["lang_name"]);
        $ftpl->r("author", $arr["author"]);
        $ftpl->rif("noimg", ($arr["icon_path"] == "null") ? false : true);

        $re .= $ftpl->load_var();
    }
    return $re;
}

?>