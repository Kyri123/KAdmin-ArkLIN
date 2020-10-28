<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
// Wenn Modsupport deaktiviert ist leitet direkt zu ServerCenter Startseite des Servers
if (!$user->perm("$perm/mods/show") || !$serv->mod_support()) {
    header(!$serv->mod_support() ? "Location: /404" : "Location: /401");
    exit;
}

$pagename = '{::lang::php::sc::page::saves::pagename}';
$resp = null;
$urls = '/servercenter/'.$url[2].'/mods/';
$page_tpl = new Template('mods.htm', __ADIR__.'/app/template/sub/serv/');
$urltop = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::saves::urltop}</li>';

// Mods Hinzufügen
if (isset($_POST['addmod']) && $user->perm("$perm/mods/add")) {
    $urler = $_POST['url'];
    foreach($urler as $k => $urle) {
        // Prüfe ob wert eine ID oder eine URL ist
        $int = is_numeric($urle);
        if ((strpos($urle, 'steamcommunity.com/sharedfiles/filedetails') || $int === true) && $urle != "") {
            if (strpos($urle, 'id=') || $int === true) {
                $modid = $urle;
                // Wenn es eine URL ist filtere ID aus der URL
                if (!$int) {
                    $urle = parse_url($urle);
                    $query = $urle['query'];
                    if (strpos($query, '&')) {
                        $exp = explode('&', $query);
                        for ($i=0;$i<count($exp);$i++) {
                            $expm = explode('=', $exp[$i]);
                            if ($expm[0] == 'id') {
                                $modid = $expm[1];
                                break;
                            }
                        }
                    }
                    else {
                        $expm = explode('=', $query);
                        $modid = $expm[1];
                    }
                }
    
                // Hole Informationen von der SteamAPI & Prüfe ob es ein Gültiger Inhalt ist
                if ($steamapi->check_mod($modid)) {
                    $mod_cfg = $serv->cfg_read('ark_GameModIds');
                    $mods = explode(',', $mod_cfg);
                    if (count($mods) > 1 || $mods[0] > 0) {
                        // Schau ob diese Mod bereits exsistiert
                        $exsists = false;
                        for ($i=0;$i<count($mods);$i++) {
                            if ($mods[$i] == $modid) {
                                $exsists = true;
                                break;
                            }
                        }
                        if ($exsists === false) {
                            // Installiere Mod wenn dies in der Konfig gewünscht ist
                            if ($ckonfig['install_mod'] == 1) {
                                $jobs->set($serv->name());
                                $jobs->arkmanager('installmod ' . $modid);
                            }
                            $i = count($mods)+1;
                            $mods[$i] = $modid;
                            $save_data = implode(',', $mods);
                            // Speicher Mods
                            $serv->cfg_write('ark_GameModIds', $save_data);
                            $serv->cfg_save();
                        }
                        else {
                            // Melde: Mod Exsistiert
                            $resp = $alert->rd(5);
                        }
                    }
                    else {
                        // Speicher Mod
                        $serv->cfg_write('ark_GameModIds', $modid);
                        $serv->cfg_save();
                    }
                } else {
                    // Melde kein Gültiger Inhalt
                    $resp = $alert->rd(20);
                }
            } else {
                // Melde: Keine Gültige URL bzw ID
                $resp = $alert->rd(19);
            }
        } else {
            // Melde: Workshop URL falsch
            $resp = $alert->rd(18);
        }
    }
}
elseif(isset($_POST['addmod'])) {
    $resp = $alert->rd(99);
}

if ($ifcadmin) {
    $resp_cluster .= $alert->rd(302, 3);
}
$page_tpl->load();
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('urls' ,$urls);
$panel = $page_tpl->load_var();


