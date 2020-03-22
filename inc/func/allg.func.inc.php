<?php


#user allgmeine daten
function getuserdata($data, $id) {
    global $mycon;
    $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$id.'\'';
    if($mycon->query($query)->numRows() > 0) {
        $row = $mycon->query($query)->fetchArray();
        return $row[$data];
    }
    else {
        return 'Account nicht gefunden!';
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
        if( $dir_handle )
        {
            while( $file = readdir( $dir_handle ) )
            {
                if($file != "." && $file != "..")
           {
               if( ! is_dir( $dir."/".$file ) )
               {
                   unlink( $dir."/".$file );
               }
               else
               {
                   delete_directory($dir.'/'.$file);
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

    if( $units[$sourceUnit] <= $units[$targetUnit] ) {
        for( $i = $units[$sourceUnit]; $size >= 1024; $i++ ) {
            if( $i === 0 ) {
                $size /= 8;
            } else {
                $size /= 1024;
            }
        }
    } else {
        for( $i = $units[$sourceUnit]; $i > $units[$targetUnit]; $i-- ) {
            if( $i === 1 ) {
                $size *= 8;
            } else {
                $size *= 1024;
            }
        }
    }
    return round( $size, 2 ) . ' ' . array_keys($units)[$i];
}

function meld($type, $txt = '{ALERT_TXT}', $title = '{ALERT_TITLE}', $icon = null) {
    $rnd = bin2hex(random_bytes(50));
    return '<div class="card card-'.$type.'" id="'.$rnd.'">
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
                    '.$txt.'
              </div>
              <!-- /.card-body -->
            </div>';
}

# Time
function converttime($stamp) {
    return date("d.m.Y H:i", $stamp); ;
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

#
function write_ini_file($array, $file)
{
    $res = array();
    foreach($array as $key => $val)
    {
        if(is_array($val))
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
        if(!$canWrite) usleep(round(rand(0, 100)*1000));
    } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

    if ($canWrite)
    {            fwrite($fp, $dataToSave);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

}
#
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

#

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

    $str = str_replace('ERROR [0;39m ] 	', '<b class="text-danger">ERROR</b> ', $str);
    $str = str_replace('WARNING [0;39m ] 	', '<b class="text-warning">WARNUNG</b> ', $str);
    $str = str_replace('Retries exhausted', 'Neuer versuch wird gestartet', $str);
    $str = str_replace('Your server is already up to date! The most recent version is', 'Der Server ist auf der aktuellen Version:', $str);
    $str = str_replace('\033[0;39m ', null, $str);
    $str = str_replace('\033[0;39m\e[68G[', null, $str);
    $str = str_replace('\033[1;31mFAILED]', '<b class="text-danger">[Fehlgeschlagen]</b>', $str);
    $str = str_replace('[0;39m[68G[   [1;32mOK[0;39m   ]', '<b class="text-success">[OK]</b>', $str);
    $str = str_replace('', null, $str);
    $str = str_replace('[K', null, $str);
    $str = str_replace('Yes', '<b class="text-success"> Ja</b>', $str);
    $str = str_replace('No', '<b class="danger"> Nein</b>', $str);
    $str = str_replace('Created Backup: ', '<b class="text-gray-800">Erstelle Backup: </b>', $str);
    $str = str_replace('Copying files to ', '<b class="text-gray-800">Kopiere Dateien nach: </b>', $str);
    $str = str_replace('Copying ', '<b class="text-gray-800">Kopiere: </b>', $str);
    $str = str_replace('Saved arks directory is ', '<b class="text-gray-800">Speicherverzeichnis ist: </b>', $str);
    $str = str_replace('Compressing Backup ', 'Komprimiere Backup ... ', $str);
    $str = str_replace('ARK tribute tribe files ', 'Stämme > Charaktere ... ', $str);
    $str = str_replace('ARK tribe files ', 'Stämme ... ', $str);
    $str = str_replace('ARK profile files', 'Charaktere (Profile) ... ', $str);
    $str = str_replace('ARK world file ', 'ARK Welt / Karte ... ', $str);
    $str = str_replace('Running command ', 'Führe Aktion ', $str);
    $str = str_replace('for instance ', ' aus für die Instanz ', $str);
    $str = str_replace('The server is starting', 'Der Server startet...', $str);
    $str = str_replace('The server is now running, and should be up within 10 minutes', 'Der Server läuft und sollte in ca. <b>10 Minuten <span class="text-success">Online</span></b> sein.', $str);
    if(strpos($str, '] Updating mod') !== false) {
        $time = explode('[', $str);
        $time = $time[0];
        $modid = explode(']', $str);
        $modid = $modid[1];
        $modid = str_replace('Updating mod ', null, $modid);
        $modid = str_replace(" ", null, $modid);
        $json = $steamapi->getmod($modid);
        $str = $time.' <b class="text-gray-800">Update Mod: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';
    }
    if(strpos($str, '] Mod') !== false) {
        $time = explode('[', $str);
        $time = $time[0];
        $modid = explode(']', $str);
        $modid = $modid[1];
        $modid = str_replace('Updating mod ', null, $modid);
        $modid = str_replace(" ", null, $modid);
        $modid = str_replace("Mod", null, $modid);
        $modid = str_replace("updated", null, $modid);
        $json = $steamapi->getmod($modid);
        $str = $time.' <b class="text-success">Update Erfolgreich: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';
    }
    if(strpos($str, 'Updating mod') !== false) {
        $modid = str_replace('Updating mod ', null, $str);
        $modid = str_replace(" ", null, $modid);
        $json = $steamapi->getmod($modid);
        $str = '<b class="text-gray-800">Update Mod: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';
    }
    if(strpos($str, 'not fully downloaded') !== false) {
        $modid = str_replace(' not fully downloaded - retrying', null, $str);
        $modid = str_replace("Mod ", null, $modid);
        $mod = $steamapi->getmod_class($modid);
        $str = '<b class="text-gray-800">Wurde nicht richtig Runtergeladen: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $mod->title .'</a> (neuer Versuch)</b>';
    }
    if(strpos($str, 'Downloading mod ') !== false) {
        $modid = str_replace('Downloading mod ', null, $str);
        $modid = str_replace(" ", null, $modid);
        $modid = explode('...', $modid);
        $done = $modid[1];
        $modid = $modid[0];
        $json = $steamapi->getmod($modid);
        $str = '<b class="text-gray-800">Läd Runter: </b><a href="https://steamcommunity.com/sharedfiles/filedetails/?id='.$modid.'" target="_blank">' . $json->response->publishedfiledetails[0]->title.'</a></b>';

        if(strpos($done, 'downloaded') !== false) {
            $str .= ' | <span class="text-success">Fertig!</span>';
        }
    }
    if(strpos($str, 'Performing ARK update') !== false) {
        //$modid = str_replace('Performing ARK update ', null, $str);
        //$modid = str_replace(" ", null, $modid);
        $txt = explode('...', $str);
        $txt = explode('to ', $txt[1]);
        $txt[1] = str_replace(" complete", null, $txt[1]);
        $txt[1] = str_replace(" ", null, $txt[1]);
        $str = '<b class="text-gray-800">Installiere neues Ark Update</b>';

        if($txt[1] > 0) {
            $str .= ' | <span class="text-success">Fertig! Neuer Build: <b>'.$txt[1].'</b></span>';
        }
    }
    return $str;
}

function ini_save_rdy($str) {
    $str = str_replace("\r", null, $str);
    $str = str_replace("\t", null, $str);
    return $str;
}



?>