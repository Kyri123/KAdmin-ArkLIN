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
$cfg                            = $_GET['cfg'];
$file                           = $KUTIL->path($_GET['file'])["/path"];
$max                            = $_GET['max'] == "NaN" ? '&#8734;' : $_GET["max"];
$type                           = $_GET['type'];
$filter                         = filter_var($_GET['filter'], FILTER_VALIDATE_BOOLEAN);
$modfilter                      = filter_var($_GET['mods'], FILTER_VALIDATE_BOOLEAN);
$homefilter                     = filter_var($_GET['home'], FILTER_VALIDATE_BOOLEAN);
$hide                           = $type == 'Ja';
$content                        = null;

if (@file_exists($file)) {
    $z          = 1;
    $array      = array();
    $array      = file($file);
    $i          = sizeof($array);

    $content    = '<tr><td class="p-2 text-green">{::lang::allg::logs::time}: <b>'.date ("d.m.Y H:i:s", filemtime($file)).'</b> | {::lang::allg::logs::showmax}: <b>'.$max.'</b> | {::lang::allg::logs::filter}: <b>'.(($filter) ? "{::lang::allg::on}" : "Nein").'</b> | {::lang::allg::logs::hide}: <b>'.(($type == "Ja") ? "{::lang::allg::on}" : "{::lang::allg::off}").'</b></td></tr>';
    while ($i--) {
        $array[$i]  = str_replace("\"", null, $array[$i]);
        $array[$i]  = str_replace("\n", null, $array[$i]);
        $array[$i]  = str_replace("\r", null, $array[$i]);

        $filterthis["filter"] = array(
            "Checking",
            "already up to date",
            "S_API FAIL",
            "Candidates",
            "Setting breakpad",
            "The server is now running",
            "Applying update from staging",
            "Saved ARK",
            "Copying to staging",
            "Saving world",
            "fork: retry:",
            "The server is starting...",
            "/usr/local/bin/arkmanager: Zeile 1189",
            ": start",
            "/usr/local/bin/arkmanager: Zeile 920"
        );
        $filterthis["mods"] = array(
            "] Updating mod",
            "] Mod",
            "Updating mod"
        );
        $filterthis["livechat"] = array(
            "livechat",
            "Dein Stamm killed",
            " - Level",
            "AdminCmd",
            "Souls were destroyed",
            "zerstörte ein(e)",
            "Saved ARK",
            "Log file open",
            "Force respawning Wild Dinos!"
        );

        // Lösche [] für Livechat
        if(strpos($file, "ServerPanel") !== false)
            $array[$i] = preg_replace('#\[(.*?)\]#si', null, $array[$i]);

        // Ändere das Format für Rconlog
        if(strpos($file, "rcon") !== false) {
            $exp = explode('(-/-)', $array[$i]);
            $array[$i] = date('d.m.Y - H:i:s', $exp[0]) . " - ".$exp[1];
        }

        if(
            strpos_arr($array[$i], $filterthis["livechat"]) && 
            $filter && 
            strpos($file, "ServerPanel") !== false
        ) {
            // hidden - wenn Livechat und Filter aktiv
        }
        elseif(strpos_arr($array[$i], $filterthis["filter"]) && $filter && !$homefilter) {
            // hidden
        }
        elseif(strpos_arr($array[$i], $filterthis["mods"]) && !$homefilter) {
            if(!$modfilter) {
                // hidden
            }
            else {
                $content    .= '<tr><td class="p-2"><b class="text-info">'.($z).strpos_arr($array[$i], $filterthis).': </b>'.filtersh(alog($array[$i])).'</td></tr>';
                $z++;
            }
        }
        elseif ($array[$i] != "" && $array[$i] != ' ' && !$modfilter) {
            $laenge         = strlen($array[$i]);
            if ($laenge < 300 && $hide) {
                $content    .= '<tr><td class="p-2"><b class="text-info">'.($z).strpos_arr($array[$i], $filterthis).': </b>'.filtersh(alog($array[$i])).'</td></tr>';
            }
            elseif (!$hide) {
                $content    .= '<tr><td class="p-2"><b class="text-info">'.($z).strpos_arr($array[$i], $filterthis).': </b>'.filtersh(alog($array[$i])).'</td></tr>';
            } else {
                $content    .= '<tr><td class="p-2"><b class="text-info">'.($z).strpos_arr($array[$i], $filterthis).': </b> <b>{::lang::php::async::get::all::getlog::toolarge}</b></td></tr>';
            }
            $z++;
        }
        if ($z == $max) break;
    }


} else {
    $content = '<tr><td>{::lang::php::async::get::all::getlog::no_log_found} '.$file.'</i></td></tr>';
}

$tpl = new Template("content.htm", __ADIR__."/app/template/universally/default/");
$tpl->load();
$tpl->r("content", $content);
$tpl->echo();
$mycon->close();

