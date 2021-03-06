<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

/**
 * Setzte Icon von Dateien
 *
 * @param  mixed $target
 * @param  mixed $ico
 * @return string
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

/**
 * Wandelt serverstatus in String (ML)
 *
 * @param  mixed $serverstate
 * @return array - ["color"] / ["str"]
 */

function convertstate($serverstate) {
    $state = [];

    $state[0]["serv_state"] = "{::lang::php::function_allg::state_off}";
    $state[0]["serv_color"] = "danger";

    $state[1]["serv_state"] = "{::lang::php::function_allg::state_start}";
    $state[1]["serv_color"] = "info";

    $state[2]["serv_state"] = "{::lang::php::function_allg::state_on}";
    $state[2]["serv_color"] = "success";

    $state[3]["serv_state"] = "{::lang::php::function_allg::state_notinstalled}";
    $state[3]["serv_color"] = "warning";

    return array("color" => $state[$serverstate]["serv_color"],"str" => $state[$serverstate]["serv_state"]);
}

/**
 * Zufällige String reihenfolge
 *
 * @param  int $l
 * @return bool
 */

function rndbit($l) {
    return bin2hex(random_bytes($l));
}

/**
 * Löscht das verzeichnis Rekursiv
 *
 * @param  mixed $dir
 * @return bool
 */

function del_dir($dir) {
    if(is_dir($dir))
    {
        $dir_handle = opendir($dir);
        if($dir_handle) {
            while($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (!is_dir($dir."/".$file))
                    {
                        unlink( $dir."/".$file );
                    } else {
                        del_dir($dir.'/'.$file);
                    }
                }
            }
            closedir($dir_handle);
        }
        rmdir($dir);
        return true;
    }
    return false;
}

/**
 * Rechnet bit in gewünschten Format um
 *
 * @param  mixed $size
 * @param  mixed $sourceUnit Quelle bit | B | KB | MK | GB
 * @param  mixed $targetUnit Ausgabe bit | B | KB | MK | GB
 * @return string
 */

function bitrechner( $size, $sourceUnit = 'bit', $targetUnit = 'MB' ) {
    $units = array(
        'bit' => 0,
        'B' => 1,
        'KB' => 2,
        'MB' => 3,
        'GB' => 4,
        'TB' => 5
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

/**
 * Schaut ob im Array ein String gefunden wird
 *
 * @param  string|int $haystack
 * @param  array $array
 * @return bool
 */

function strpos_arr($haystack, array $array)
{
    $bool = false;
    foreach($array as $str) {
        if(!is_array($str)) {
            if(strpos($haystack, $str) === false) {
                $bool = false;
            } else {
                $bool = true;
                break;
            }
        }
    }
    return $bool;
}

/**
 * Wandelt timestamp in String um
 *
 * @param  mixed $stamp
 * @param  mixed $withsec
 * @param  mixed $onlydate
 * @return string
 */

function converttime($stamp, $withsec = false, $onlydate = false) {
    if ($withsec) return date("d.m.Y H:i:s", $stamp);
    if ($onlydate) return date("d.m.Y", $stamp);
    return date("d.m.Y H:i", $stamp);
}

/**
 * Wandelt ein Verzeichnis Rekursiv in ein array
 *
 * @param  mixed $dir
 * @return array
 */

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

/**
 * Fileter \r \t
 *
 * @param string $str
 * @return string
 */

function ini_save_rdy(string $str) {
    $str = str_replace("\r", null, $str);
    $str = str_replace("\t", null, $str);
    return $str;
}

/**
 * Differnz von wann eine Datei erstellt wurde
 *
 * @param  mixed $file
 * @param  mixed $diff
 * @return bool
 */

function timediff(String $file, Int $diff) {
    if($file == "" || $file == null || !file_exists($file)) return -1;
    $filetime = filemtime($file);
    $differnz = time()-$filetime;
    return ($differnz > $diff);
}

/**
 * Berechnung von %
 *
 * @param  mixed $curr
 * @param  mixed $max
 * @return int
 */

function perc($curr, $max) {
    return ($curr / $max * 100);
}

/**
 * Erfasse Client IP
 *
 * @return string
 */
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Konvertiere Default zu Ini compati
 *
 * @param array $ARR
 * @return array
 */
function convert_ini(array $ARR) {
    $RETURN = array();
    foreach ($ARR as $key => $item) {
        foreach ($item as $KEY => $ITEM) {
            if(isset($ITEM["default"])) {
                $RETURN[$key][$KEY] = $ITEM["default"];
            } else {
                foreach ($ITEM as $KEY2 => $ITEM2) {
                    $RETURN[$key][$KEY][] = $ITEM2["default"];
                }
            }
        }
    }
    return $RETURN;
}

/**
 * Konvertiere Ini zur Form
 *
 * @param string $INI
 * @param array $ARR
 * @param array $DEFAULT
 * @return array
 */
function create_ini_form(array $ARR, string $INI, array $DEFAULT, string $CFG) {

    $RETURN = $REST = null;

    $INARR = $INI == "Game" ? [
        "PlayerBaseStatMultipliers",
        "PerLevelStatsMultiplier_Player",
        "PerLevelStatsMultiplier_DinoWild",
        "PerLevelStatsMultiplier_DinoTamed",
        "PerLevelStatsMultiplier_DinoTamed_Add",
        "PerLevelStatsMultiplier_DinoTamed_Affinity"
    ] : [];

    $former_use = [
        "ServerSettings",
        "SessionSettings",
        "/Script/Engine.GameSession",
        "MessageOfTheDay",
        "/Game/PrimalEarth/CoreBlueprints/TestGameMode.TestGameMode_C",
        "/script/shootergame.shootergamemode",
        "/script/onlinesubsystemutils.ipnetdriver",
        "/script/engine.player"
    ];

    foreach ($ARR as $key => $item) {
        global $langfrom;

        if(isset($_SESSION["id"])) {
            $user = new userclass($_SESSION["id"]);
        } else {
            return ["form" => "", "rest" => ""];
        }
        $ITEMS = null;

        if(in_array($key, $former_use)) {
            $tpl_sec = new Template("section.htm", __ADIR__."/app/template/lists/serv/konfig/");
            $tpl_sec->load();
        }
        else {
            $REST .= "\n[$key]\n";
        }

        foreach ($item as $KEY => $ITEM) {
            if(!is_array($ITEM)) {
                if(in_array($key, $former_use) && ($INI != "Game" || isset($DEFAULT[$key][$KEY]))) {
                    $tpl_i1 = new Template("item.htm", __ADIR__."/app/template/lists/serv/konfig/");
                    $tpl_i1->load();

                    $TYPE = isset($DEFAULT[$key][$KEY]) ? $DEFAULT[$key][$KEY]["type"] : "string";

                    $tpl_i1->rif("float", $TYPE == "float");
                    $tpl_i1->rif("string", $TYPE == "string");
                    $tpl_i1->rif("bool", $TYPE == "bool");
                    $tpl_i1->rif("int", $TYPE == "int");

                    if($TYPE == "int") $ITEM = round($ITEM,0);

                    $tpl_i1->rif("ro", !$user->perm("server/$CFG/konfig/$INI"));
                    $tpl_i1->rif("showbtn", in_array("{::lang::$INI::$KEY}", $langfrom));
                    $tpl_i1->r("ini", $INI);
                    $tpl_i1->r("name", "ini[$key][$KEY]");
                    $tpl_i1->r("opt", $KEY);
                    $tpl_i1->r("opt2", $KEY);
                    $tpl_i1->r("value", $ITEM);
                    $tpl_i1->r("True", ($TYPE == "bool" && $ITEM == "True") ? "selected" : "".(!$user->perm("server/$CFG/konfig/$INI") ? " disabled" : ""));
                    $tpl_i1->r("False", ($TYPE == "bool" && $ITEM == "False") ? "selected" : "".(!$user->perm("server/$CFG/konfig/$INI") ? " disabled" : ""));
                    $tpl_i1->r("max", $TYPE == "float" ? round((($DEFAULT[$key][$KEY]["default"] < 1 ? 1 : $DEFAULT[$key][$KEY]["default"]) * 10), 0) : ($TYPE == "int" ? round((($DEFAULT[$key][$KEY]["default"] < 5 ? 5 : $DEFAULT[$key][$KEY]["default"]) * 10), 0) :"1"));

                    $ITEMS .= $tpl_i1->load_var();
                }
                else {
                    if(is_array($ITEM)) {
                        foreach ($ITEM as $IL) {
                            $REST .= "$KEY=$IL\n";
                        }
                    }
                    else {
                        $REST .= "$KEY=$ITEM\n";
                    }
                }
            } elseif(in_array($KEY, $INARR)) {
                foreach ($ITEM as $KEY2 => $ITEM2) {
                    if(in_array($key, $former_use)) {
                        $tpl_i1 = new Template("item.htm", __ADIR__."/app/template/lists/serv/konfig/");
                        $tpl_i1->load();

                        $TYPE = isset($DEFAULT[$key][$KEY][$KEY2]) ? $DEFAULT[$key][$KEY][$KEY2]["type"] : "string";

                        $tpl_i1->rif("float", $TYPE == "float");
                        $tpl_i1->rif("string", $TYPE == "string");
                        $tpl_i1->rif("bool", $TYPE == "bool");
                        $tpl_i1->rif("int", $TYPE == "int");

                        if($TYPE == "int") $ITEM2 = round($ITEM2,0);

                        $tpl_i1->rif("showbtn", in_array("{::lang::$INI::$KEY"."$KEY2}", $langfrom));
                        $tpl_i1->rif("ro", !$user->perm("server/$CFG/konfig/$INI"));
                        $tpl_i1->r("ini", $INI);
                        $tpl_i1->r("name", "ini[$key][$KEY][$KEY2]");
                        $tpl_i1->r("opt", $KEY."[$KEY2]");
                        $tpl_i1->r("opt2", $KEY.$KEY2);
                        if(is_array($ITEM2)) $tpl_i1->r("value", isset($ITEM2[$KEY2]) ? $ITEM2[$KEY2] : $DEFAULT[$key][$KEY][$KEY2]["default"]);
                        if(!is_array($ITEM2)) $tpl_i1->r("value", $ITEM2);
                        $tpl_i1->r("True", ($TYPE == "bool" && $ITEM == "True") ? "selected" : "".(!$user->perm("server/$CFG/konfig/$INI") ? " disabled" : ""));
                        $tpl_i1->r("False", ($TYPE == "bool" && $ITEM == "False") ? "selected" : "".(!$user->perm("server/$CFG/konfig/$INI") ? " disabled" : ""));
                        $tpl_i1->r("max", $TYPE == "float" ? round((($DEFAULT[$key][$KEY][$KEY2]["default"] < 1 ? 1 : $DEFAULT[$key][$KEY][$KEY2]["default"]) * 10), 0) : ($TYPE == "int" ? round((($DEFAULT[$key][$KEY][$KEY2]["default"] < 5 ? 5 : $DEFAULT[$key][$KEY][$KEY2]["default"]) * 10), 0) :"1"));

                        $ITEMS .= $tpl_i1->load_var();
                    }
                    else {
                        if(is_array($ITEM)) {
                            foreach ($ITEM as $IL) {
                                $REST .= "$KEY=$IL\n";
                            }
                        }
                        else {
                            $REST .= "$KEY=$ITEM\n";
                        }
                    }
                }
            }
            else {
                if(is_array($ITEM)) {
                    foreach ($ITEM as $IL) {
                        $REST .= "$KEY=$IL\n";
                    }
                }
                else {
                    $REST .= "$KEY=$ITEM\n";
                }
            }
        }

        if(in_array($key, $former_use)) {
            $tpl_sec->rif("hidden", /*$key == "/Script/ShooterGame.ShooterGameUserSettings"*/false);
            $tpl_sec->r("name", $key);
            $tpl_sec->r("items", $ITEMS);

            $RETURN .= $tpl_sec->load_var();
        }
    }

    return ["form" => $RETURN, "rest" => $REST];
}


// create form
/**
 * Erstelle Rekutsive die Form für Rechte
 *
 * @param array $arr        Array der Permissions
 * @param array $D_ARRAY    Array der Permissions
 * @param string $k         Wird nur intern benutzt
 * @param string $keys      Wird nur intern benutzt
 * @return string
 */
function creatform(array $arr, array $D_ARRAY = null, string $k = null, array $keys = []) {
    global $all;

    // Import ROOT_TPL & page
    global $ROOT_TPL, $page;

    $string     = null;

    foreach ($arr as $KEY => $ITEM) {
        $übergabe = $keys;
        $übergabe[] = $KEY;

        // Erstelle Header und gehe Tiefer ins Array
        if(is_array($ITEM)) {
            $TPL_FORM   = new Template("form_head.htm", __ADIR__."/app/template/lists/$page/");
            $TPL_FORM->load();

            $TPL_FORM->r("key", "{::lang::php::userpanel::permissions::$KEY}");
            $TPL_FORM->r("color", count($übergabe) > 1 ? (count($übergabe) > 2 ? "secondary" : "dark") : "dark");

            $string     .= $TPL_FORM->load_var();
            $rnd = rndbit(30);

            if(count($übergabe) == 2) $string .= '<a class="btn btn-info btn-sm" href="#" onclick="$(\'#'.$rnd.'\').toggle()" style="width: 100%">Rechte verwalten</a><div id="'.$rnd.'" style="display:none">';
            $string .= creatform($ITEM, $D_ARRAY, $KEY, $übergabe);
            if(count($übergabe) == 2) $string .= '</div>';
        }
        // Erstelle Forminput
        else {
            $TPL_FORM   = new Template("form_item.htm", __ADIR__."/app/template/lists/$page/");
            $TPL_FORM->load();

            $formname = null;
            foreach ($übergabe as $NAME) {
                $formname .= "[$NAME]";
            }

            $TPL_FORM->r("form_name", $formname);
            $TPL_FORM->r("key", "{::lang::php::userpanel::permissions::$KEY}");
            $TPL_FORM->r("id", "input_".rndbit(5)."_".($k == null ? "": $k."_")."$KEY");
            $TPL_FORM->rif("active", boolval($ITEM));

            $string     .= $TPL_FORM->load_var();
        }
    }
    if(isset($all["cfgs_only_name"]) && is_array($all["cfgs_only_name"])) {
        foreach ($all["cfgs_only_name"] as $SERVER) {
            $server = new server($SERVER);
            $string = str_replace("{::lang::php::userpanel::permissions::$SERVER}", $server->cfgRead("ark_SessionName"), $string);
        }
    }

    return $string;
}

/**
 * @param $file
 * @return array
 */
function extend_parse_ini($file){
    $arr = array();
    $handle = fopen($file, "r");
    $superkey = "unload";
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = str_replace(["\r", "\n"], null, $line);
            $line = preg_replace(
                '/
                            ^
                            [\pZ\p{Cc}\x{feff}]+
                            |
                            [\pZ\p{Cc}\x{feff}]+$
                           /ux','', $line);
            if(
                strpos($line,"[") !== false &&
                strpos($line,"]") !== false &&
                strpos($line,"=") === false &&
                !is_numeric(preg_replace("#(.*?)\[(.*?)\]#si", "$2", $line))
            ) {
                $superkey = preg_replace("#(.*?)\[(.*?)\]#si", "$2", $line);
            }
            else {
                $parsed = parse_ini_string($line,null, INI_SCANNER_RAW);
                if(empty($parsed)) continue;
                $key = key($parsed);
                if(isset($arr[$superkey][$key])) {
                    if(!is_array($arr[$superkey][$key])) {
                        $tmp = $arr[$superkey][$key];
                        $arr[$superkey][$key] = array($tmp);
                    }
                    $arr[$superkey][$key][] = $parsed[$key];
                } else {
                    $arr[$superkey][$key] = $parsed[$key];
                }
            }
        }
        fclose($handle);
        return $arr;
    }
    return false;
}