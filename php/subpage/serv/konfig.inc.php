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
if (!$user->perm("$perm/konfig/show")) {
    header("Location: /401");
    exit;
}

$pagename = '{::lang::php::sc::page::konfig::pagename}';
$page_tpl = new Template('konfig.htm', 'app/template/sub/serv/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::konfig::urltop}</li>';

// arkmanager.cfg Speichern (Normaler Modus)
$resp = $ark_flag = $eventlist = null;
if (isset($_POST['savecfg']) && (($serv->statecode() == 1 && $user->show_mode("konfig")) || !$user->show_mode("konfig")) && $user->perm("$perm/konfig/arkmanager")) {
    $value = $_POST['value'];
    $key = $_POST['key'];
    $flag = $_POST['flag'];
    $cfg = null;

    // entfernte gamemod && mapmod wenn ModSupport Deaktiviert
    $remove[] = null;
    if(!$serv->mod_support()) {
        $remove[] = "ark_GameModIds";
        $remove[] = "serverMapModId";
    }

    for ($i=0;$i<count($key);$i++) {
        $write = ($value[$i] == "none" && !in_array($value[$i], $remove)) ? false : true;
        if($write) $cfg .= $key[$i].'="'.$value[$i]."\"\n";
    }

    // Füge Flaggen hinzu die Ausgewählt sind
    if(is_array($flag)) {
        for ($i=0;$i<count($flag);$i++) {
            $cfg .= "arkflag_".$flag[$i].'="True"'."\n";
        }
    }

    $cfg .= $flag;
    $cfg = ini_save_rdy($cfg);
    $cfg = str_replace("Array", null, $cfg);
    $path = 'remote/arkmanager/instances/'.$url[2].'.cfg';
    // Prüfe ob Datei beschrieben wurde
    if (file_put_contents($path, $cfg)) {
        // Melde: Erfolg
        $resp = $alert->rd(102);
        //header("Refresh:0"); exit;
    } else {
        // Melde Lese/Schreib Fehler
        $resp = $alert->rd(1);
    } 
}
else {
    // Melde Fehlschlag
    if(isset($_POST['savecfg'])) $resp = $alert->rd(7);
    if(isset($_POST['savecfg']) && !$user->perm("$perm/konfig/arkmanager")) $resp = $alert->rd(7);
}

// GameUserSettings.ini Speichern (Normaler Modus)
$resp = null;
if (isset($_POST['savenormal']) && (($serv->statecode() == 1 && $user->show_mode("konfig")) || !$user->show_mode("konfig"))) {

    $value = $_POST['value'];
    $key = $_POST['key'];
    $skey = $_POST['skey'];
    $type = $_POST["type"];
    
    if(
        ($type == "GameUserSettings.ini" && $user->perm("$perm/konfig/gus")) ||
        ($type == "Game.ini" && $user->perm("$perm/konfig/game")) ||
        ($type == "Engine.ini" && $user->perm("$perm/konfig/engine"))
    ) {
        for ($i=0;$i<count($key);$i++) {
            $cfg[$skey[$i]][$key[$i]] = $value[$i];
        }

        $cfg_done = null;
        foreach ($cfg as $k => $v){
            $cfg_done .= "\n[$k]\n";
            foreach ($v as $ik => $iv){
                $cfg_done .= "$ik=$iv\n";
            }
        }

        $path = $serv->dir_konfig().$type;
        $text = ini_save_rdy($cfg_done);
        // Wenn Datei geschreiben wurde
        if (file_put_contents($path, $text)) {
            // Melde: Erfolg
            $resp = $alert->rd(102);
        } else {
            // Melde: Lese/SchreibFeher
            $resp = $alert->rd(1);
        }
    }
    else {
        $resp = $alert->rd(99);
    }
}
else {
    // Melde Fehlschlag
    if(isset($_POST['savenormal'])) $resp = $alert->rd(7);
    if(isset($_POST['savecfg']) && !$user->perm("$perm/konfig/arkmanager")) $resp = $alert->rd(7);
}

// arkmanager.cfg (Expert) Speichern
if (isset($_POST['savecfg_expert']) && (($serv->statecode() == 1 && $user->show_mode("konfig")) || !$user->show_mode("konfig")) && $user->perm("$perm/konfig/arkmanager")) {
    $txtarea = $_POST['txtarea'];
    $cfg = ini_save_rdy($txtarea);
    $path = 'remote/arkmanager/instances/'.$url[2].'.cfg';
    // Wenn Datei geschreiben wurde
    if (file_put_contents($path, $cfg)) {
        // Melde: Erofolg
        $resp = $alert->rd(102);
    } else {
        // Melde: Lese/Schreib Fehler
        $resp = $alert->rd(1);
    } 
}
else {
    // Melde Fehlschlag
    if(isset($_POST['savecfg_expert'])) $resp = $alert->rd(7);
    if(isset($_POST['savecfg']) && !$user->perm("$perm/konfig/arkmanager")) $resp = $alert->rd(7);
}

// Game,GUS,Engine.ini Speichern (Expertenmodus)
if (isset($_POST['save']) && (($serv->statecode() == 1 && $user->show_mode("konfig")) || !$user->show_mode("konfig"))) {
    $type = $_POST["type"];
    $text = $_POST["text"];
    $path = $serv->dir_konfig().$type;
    
    if(
        ($type == "GameUserSettings.ini" && $user->perm("$perm/konfig/gus")) ||
        ($type == "Game.ini" && $user->perm("$perm/konfig/game")) ||
        ($type == "Engine.ini" && $user->perm("$perm/konfig/engine"))
    ) {
        // Prüfe ob Datei Exsistiert
        if (file_exists($path)) {
            $text = ini_save_rdy($text);
            if (file_put_contents($path, $text)) {
                // Mel.de Erfolg
                $resp = $alert->rd(102);
            } else {
                // Melde: Lese/Schreib Fehler
                $resp = $alert->rd(1);
            }
        } else {
            // Melde: Lese/Schreib Fehler
            $resp = $alert->rd(1);
        }
    }
    else {
        $resp = $alert->rd(99);
    }
}
else {
    // Melde Fehlschlag
    if(isset($_POST['save'])) $resp = $alert->rd(7);
}

$page_tpl->r('cfg' ,$url[2]);

$default = "{::lang::php::sc::page::konfig::ini_notfound}";
$default_table = "<tr colspan='2'><td>{::lang::php::sc::page::konfig::ini_notfound}</td></tr>";

$gus = ($serv->ini_load('GameUserSettings.ini', true)) ? $gus = $serv->ini_get_str() : $default;
$game = ($serv->ini_load('Game.ini', true)) ? $serv->ini_get_str() : $default;
$engine = ($serv->ini_load('Engine.ini', true)) ? $serv->ini_get_str() : $default;

$gus_bool = ($serv->ini_load('GameUserSettings.ini', true));
$game_bool = ($serv->ini_load('Game.ini', true));
$engine_bool = ($serv->ini_load('Engine.ini', true));

// Prüfe ob Inis exsistieren
$show = (
    file_exists($serv->dir_save(true)."/Config/LinuxServer/GameUserSettings.ini") && 
    file_exists($serv->dir_save(true)."/Config/LinuxServer/Game.ini") && 
    file_exists($serv->dir_save(true)."/Config/LinuxServer/Engine.ini")
);

$gus_nexp = ($serv->ini_load('GameUserSettings.ini', true)) ? json_decode(json_encode($serv->ini_get()), true) : $default_table;
$game_nexp = ($serv->ini_load('Game.ini', true)) ? json_decode(json_encode($serv->ini_get()), true) : $default_table;
$engine_nexp = ($serv->ini_load('Engine.ini', true)) ? json_decode(json_encode($serv->ini_get()), true) : $default_table;

$inis = array("gus" => $gus_nexp, "game" => $game_nexp, "engine" => $engine_nexp);

$strcfg = $serv->cfg_get_str();

$form = null;
$ark_opt = null;
$flags = array();
$i = 0;


//event 
$events = array(
    "Easter",
    "Arkeolgy",
    "ExtinctionChronicles",
    "WinterWonderland",
    "vday",
    "Summer",
    "FearEvolved",
    "TurkeyTrial",
    "birthday"
);
$eventlist .= "<option value=\"none\">{::lang::php::sc::page::konfig::noevent}</option>";
$curr = $serv->cfg_read("arkopt_ActiveEvent");
foreach($events as $k) {
    $eventlist .="<option value=\"$k\" ".(($curr == $k) ? "selected=\"true\"" : null).">$k</option>";
}
$form .= '
    <tr>
        <td class="p-2">arkopt_ActiveEvent</td>
        <td class="p-2">
            <input type="hidden" name="key[]" readonly value="arkopt_ActiveEvent">
            <select type="text" name="value[]" class="form-control form-control-sm">'.$eventlist.'</select>
        </td>
    </tr>';

// Wenn Installiert dann gebe Inis aus
if ($serv->isinstalled()) {
    $hide_cluster = array(
        "ark_NoTransferFromFiltering",
        "ark_NoTributeDownloads",
        "ark_PreventDownloadSurvivors",
        "ark_PreventUploadSurvivors",
        "ark_PreventDownloadItems",
        "ark_PreventUploadItems",
        "ark_PreventDownloadDinos",
        "ark_PreventUploadDinos",
        "arkopt_clusterid",
        "arkopt_ClusterDirOverride"
    );
    $no_del = array(
        "arkserverroot",
        "logdir",
        "arkbackupdir",
        "arkserverexec",
        "arkautorestartfile",
        "arkAutoUpdateOnStart",
        "arkBackupPreUpdate",
        "ark_SessionName",
        "serverMap",
        "serverMapModId",
        "ark_TotalConversionMod",
        "ark_RCONEnabled",
        "ark_ServerPassword",
        "ark_ServerAdminPassword",
        "ark_MaxPlayers",
        "ark_GameModIds",
        "aark_RCONEnabled",
        "ark_Port",
        "ark_RCONPort",
        "ark_QueryPort",
        "ark_AltSaveDirectoryName"
    );
    $remove[] = "arkopt_ActiveEvent";

    if(!$serv->mod_support()) {
        $remove[] = "ark_GameModIds";
        $remove[] = "serverMapModId";
    }

    $serv->cfg_get();
    $ini = parse_ini_file('remote/arkmanager/instances/'.$url[2].'.cfg', false);
    if(!isset($ini["ark_GameModIds"])) $ini["ark_GameModIds"] = "";
    if(!isset($ini["serverMapModId"])) $ini["serverMapModId"] = "";
    // Gehe ini Durch um Editor zu erstellen
    foreach($ini as $key => $val) {
        if ($key) {
            if (in_array($key, $remove)) {
                // Entferne aus der Ini
            }
            elseif (strpos($key, 'arkflag_') !== false) {
                // Gebe Flaggen seperat in ein Array um sie später weiter zu verarbeiten
                array_push($flags, $key);
            }
            else {
                $add = null;

                // map Select
                if($key == "serverMap") {
                    $add .= '<div class="input-group-append"><select class="form-control form-control-sm" onchange="setmap()" id="mapsel">
                        <option value="">{::lang::allg::default::select}</option>';

                    $mapjson = $helper->file_to_json("app/json/panel/maps.json");
                    foreach ($mapjson as $map => $infos) {
                        if(($infos["mod"] == 1 && $serv->mod_support()) || $infos["mod"] == 0)
                            $add .= '<option id="'.$map.'" value="'.$map.'" data-mod="'.$infos["mod"].'" data-modid="'.$infos["modid"].'" '.($map == $val ? "selected" : null).'>
                                '.($infos["mod"] == 1 ? "[MOD] " : null).$infos["name"].'
                            </option>';
                    }

                    $add .= '</select></div>';
                }

                // Totalmod Select
                if($key == "ark_TotalConversionMod") {
                    $add .= '<div class="input-group-append"><select class="form-control form-control-sm" onchange="settmod()" id="tmodsel">
                        <option value="">{::lang::allg::default::select}</option>';

                    $tmodjson = $helper->file_to_json("app/json/panel/tmods.json");
                    foreach ($tmodjson as $tmod => $infos) {
                        if(($infos["offi"] == 0 && $serv->mod_support()) || $infos["offi"] == 1)
                            $add .= '<option value="'.$infos["modid"].'" '.($infos["modid"] == $val ? "selected" : null).'>
                                '.($infos["offi"] == 0 ? "[MOD] " : null).$tmod.'
                            </option>';
                    }

                    $add .= '</select></div>';
                }

                $formtype = (!in_array($key, $no_del)) ? 
                // wenn nicht im array
                '<div class="input-group mb-0">
                    <input type="hidden" name="key[]" readonly value="'.$key.'">
                    <input type="text" name="value[]" class="form-control form-control-sm" value="'.$val.'" id="input_'.$key.'">
                    <div class="input-group-append">
                    <span onclick="remove(\''.md5($key).'\')" style="cursor:pointer" class="input-group-btn btn-danger pr-2 pl-2 pt-1" id="basic-addon2"><i class="fa fa-times" aria-hidden="true"></i></span>
                    </div>
                </div>' : 
                //sonst
                '<div class="input-group mb-0"><input type="hidden" name="key[]" readonly value="'.$key.'">
                <input type="text" name="value[]" class="form-control form-control-sm" value="'.$val.'" id="input_'.$key.'">'.$add.'</div>';

                $form .= '
                    <tr class="'.(($serv->cluster_in() && in_array($key, $hide_cluster)) ? "d-none" : null).'" id="'.md5($key).'">
                        <td class="p-2">'.$key.'</td>
                        <td class="p-2">
                        '.$formtype.'
                        </td>
                    </tr>';
            }
        };
    }
}


// Verarbeite Inis um Konfigurationen zu erstellen
foreach($inis as $mk => $mv) {
    $re[$mk] = null;
    if(is_array($mv)) {
        // sections
        if(is_array($mv)) foreach($mv as $sk => $sv) {

            $it = null;
            $tpl_sec = new Template("section.htm", "app/template/lists/serv/konfig/");
            $tpl_sec->load();
            
            // items
            if(is_array($sv)) foreach($sv as $ik => $iv) {

                if(is_array($iv)) {
                    $name = $ik;
                    foreach($iv as $pk => $pv) {
                        $tpl_item = new Template("item.htm", "app/template/lists/serv/konfig/");
                        $tpl_item->load();
        
                        $tpl_item->r("sk", $sk);
                        $tpl_item->r("rnd", md5(rndbit(50)));
                        $tpl_item->r("k", $name."[$pk]");
                        $tpl_item->r("v", $pv);
        
                        $it .= $tpl_item->load_var();
                    }
                }
                else {
                    $tpl_item = new Template("item.htm", "app/template/lists/serv/konfig/");
                    $tpl_item->load();
    
                    $tpl_item->r("sk", $sk);
                    $tpl_item->r("rnd", md5(rndbit(50)));
                    $tpl_item->r("k", strval($ik));
                    $tpl_item->r("v", $iv);
    
                    $it .= $tpl_item->load_var();
                }

            }
            $max = 50;

            $tpl_sec->r("rnd", md5(rndbit(50)));
            $tpl_sec->r("sk", $sk);
            $tpl_sec->r("name", $sk);
            $tpl_sec->r("name_withMax", (strlen($sk) > $max) ? substr($sk,0,$max)."..." : $sk);
            $tpl_sec->r("items", $it);

            $re[$mk] .= $tpl_sec->load_var();

        }
    }
}

// Erstelle Flaggen liste und verarbeite gesetzte Flaggen
$flags_json = $helper->file_to_json("app/json/panel/flags.json", true);
$i = 0;
foreach($flags_json as $k => $v) {
    $sel = (in_array("arkflag_$v", $flags)) ? 'checked="true"' : null;
    if($i == 0) $ark_flag .= '<div class="row">';
    $ark_flag .= '  <div class="icheck-primary mb-3 col-lg-6 col-12">
                        <input type="checkbox" name="flag[]" value="' . $v . '" id="' . md5($v) . '" ' . $sel . '>
                        <label for="' . md5($v) . '">
                            ' . $v . '
                        </label>
                    </div>';
    if($i == 1) {
        $ark_flag .= '</div>';
        $i = 0;
    }
    else {
        $i++;
    }
}

if ($ifckonfig) $resp_cluster .= $alert->rd(301, 3);
$page_tpl->r('ark_opt', $ark_opt);
$page_tpl->r('ark_flag', $ark_flag);
$page_tpl->r('form', $form);
$page_tpl->r('form_GUS', $re["gus"]);
$page_tpl->r('form_GAME', $re["game"]);
$page_tpl->r('form_ENGINE', $re["engine"]);
$page_tpl->r('strcfg', $strcfg);
$page_tpl->r('gus', $gus);
$page_tpl->r('game', $game);
$page_tpl->r('engine', $engine);
$page_tpl->r('eventlist', $eventlist);
$page_tpl->r('amcfg', file_get_contents('remote/arkmanager/instances/'.$url[2].'.cfg'));
$page_tpl->rif('expert', $user->expert());
$page_tpl->rif('show', $show);
$panel = $page_tpl->load_var();
