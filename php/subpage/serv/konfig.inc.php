<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$session_user->perm("$perm/konfig/show")) {
    header("Location: /401");
    exit;
}
$resp = null;

$pagename   = '{::lang::php::sc::page::konfig::pagename}';
$page_tpl   = new Template('konfig.htm', __ADIR__.'/app/template/sub/serv/');
$urltop     = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfgRead('ark_SessionName').'</a></li>';
$urltop     .= '<li class="breadcrumb-item">{::lang::php::sc::page::konfig::urltop}</li>';

$page_tpl->load();

// arkmanager.cfg Speichern (Normaler Modus)
$resp = $ark_flag = $eventlist = null;
if (isset($_POST['savecfg']) && (($serv->stateCode() == 1 && $session_user->show_mode("konfig")) || !$session_user->show_mode("konfig")) && $session_user->perm("$perm/konfig/arkmanager")) {
    $value  = $_POST['value'];
    $key    = $_POST['key'];
    $flag   = $_POST['flag'];
    $cfg    = null;

    // entfernte gamemod && mapmod wenn ModSupport Deaktiviert
    $remove[]   = null;
    if(!$serv->modSupport()) {
        $remove[] = "ark_GameModIds";
        $remove[] = "serverMapModId";
    }

    for ($i=0;$i<count($key);$i++) {
        $write          = !($value[$i] == "none" && !in_array($value[$i], $remove));
        if($write) $cfg .= $key[$i].'="'.$value[$i]."\"\n";
    }

    // Füge Flaggen hinzu die Ausgewählt sind
    if(is_array($flag)) {
        for ($i=0;$i<count($flag);$i++) {
            $cfg    .= "arkflag_".$flag[$i].'="True"'."\n";
        }
    }
    else {
        $cfg        .= $flag;
    }

    $cfg    = ini_save_rdy($cfg);
    $cfg    = str_replace("Array", null, $cfg);
    $path   = __ADIR__.'/remote/arkmanager/instances/'.$url[2].'.cfg';
    $resp   .= $alert->rd($KUTIL->filePutContents($path, $cfg) ? 102 : 1);
}
else {
    // Melde Fehlschlag
    if(isset($_POST['savecfg'])) $resp .= $alert->rd(7);
    if(isset($_POST['savecfg']) && !$session_user->perm("$perm/konfig/arkmanager")) $resp .= $alert->rd(7);
}

// GameUserSettings/Game/Engine.ini Speichern (Normaler Modus)
if (isset($_POST['savenormal']) && (($serv->stateCode() == 1 && $session_user->show_mode("konfig")) || !$session_user->show_mode("konfig"))) {

    $INI_ARRAY      = $_POST["ini"];
    $CUSTOM         = $_POST["custom"];
    $TYPE           = $_POST["type"];

    if(
        ($TYPE == "GameUserSettings.ini"    && $session_user->perm("$perm/konfig/gus"))     ||
        ($TYPE == "Game.ini"                && $session_user->perm("$perm/konfig/game"))    ||
        ($TYPE == "Engine.ini"              && $session_user->perm("$perm/konfig/engine"))
    ) {
        $INI_STRING = null;
        $FIRST = false;
        foreach ($INI_ARRAY as $key => $item){
            $INI_STRING .= !$FIRST ? "[$key]\n" : "\n[$key]\n" ;
            $FIRST = true;
            foreach ($item as $KEY => $ITEM){
                if(is_array($ITEM)) {
                    foreach ($ITEM as $KEY2 => $ITEM2){
                        if(!is_array($ITEM2)) {
                            $INI_STRING .= $KEY."[$KEY2]=$ITEM2\n";
                        }
                    }
                }
                else {
                    $INI_STRING         .= "$KEY=".(is_bool($ITEM) ? ($ITEM ? "True" : "False") : $ITEM)."\n";
                }
            }
        }
        $INI_STRING .= $CUSTOM;

        $path       = $serv->dirKonfig()."/$TYPE";
        $text       = ini_save_rdy($INI_STRING);

        // Wenn Datei geschreiben wurde
        $resp       .= $alert->rd($KUTIL->filePutContents($path, $text) ? 102 : 1);
    }
    else {
        $resp .= $alert->rd(99);
    }
}
else {
    // Melde Fehlschlag
    if(isset($_POST['savenormal'])) $resp .= $alert->rd(7);
}

// arkmanager.cfg (Expert) Speichern
if (isset($_POST['savecfg_expert']) && (($serv->stateCode() == 1 && $session_user->show_mode("konfig")) || !$session_user->show_mode("konfig")) && $session_user->perm("$perm/konfig/arkmanager")) {
    $txtarea    = $_POST['txtarea'];
    $cfg        = ini_save_rdy($txtarea);
    $path       = __ADIR__.'/remote/arkmanager/instances/'.$url[2].'.cfg';
    $resp .= $alert->rd($KUTIL->filePutContents($path, $cfg) ? 102 : 1);
}
else {
    // Melde Fehlschlag
    if(isset($_POST['savecfg_expert'])) $resp .= $alert->rd(7);
}

// Game,GUS,Engine.ini Speichern (Expertenmodus)
if (isset($_POST['save']) && (($serv->stateCode() == 1 && $session_user->show_mode("konfig")) || !$session_user->show_mode("konfig"))) {
    $TYPE = $_POST["type"];
    $text = $_POST["text"];
    $path = $serv->dirKonfig()."/$TYPE";

    if(
        ($TYPE == "GameUserSettings.ini"    && $session_user->perm("$perm/konfig/gus")) ||
        ($TYPE == "Game.ini"                && $session_user->perm("$perm/konfig/game")) ||
        ($TYPE == "Engine.ini"              && $session_user->perm("$perm/konfig/engine"))
    ) {
        if (@file_exists($path)) {
            $text   = ini_save_rdy($text);
            $resp   .= $alert->rd($KUTIL->filePutContents($path, $text) ? 102 : 1);
        }
    }
    else {
        $resp       .= $alert->rd(99);
    }
}
else {
    // Melde Fehlschlag
    if(isset($_POST['save'])) $resp .= $alert->rd(7);
}

$page_tpl->r('cfg' ,$url[2]);

$default        = "{::lang::php::sc::page::konfig::ini_notfound}";
$default_table  = "<tr colspan='2'><td>{::lang::php::sc::page::konfig::ini_notfound}</td></tr>";

$gus            = ($serv->iniLoad('GameUserSettings.ini', true))    ? $gus = $serv->iniGetString()  : $default;
$game           = ($serv->iniLoad('Game.ini', true))                ? $serv->iniGetString()         : $default;
$engine         = ($serv->iniLoad('Engine.ini', true))              ? $serv->iniGetString()         : $default;

$gus_bool       = $serv->iniLoad('GameUserSettings.ini', true);
$game_bool      = $serv->iniLoad('Game.ini', true);
$engine_bool    = $serv->iniLoad('Engine.ini', true);

// Prüfe ob Inis exsistieren
$show = (
    @file_exists($serv->dirSavegames(true)."/Config/LinuxServer/GameUserSettings.ini") &&
    @file_exists($serv->dirSavegames(true)."/Config/LinuxServer/Game.ini") &&
    @file_exists($serv->dirSavegames(true)."/Config/LinuxServer/Engine.ini")
);

$gus_nexp       = $serv->iniLoad('GameUserSettings.ini', true)    ? json_decode(json_encode($serv->iniGetArray()), true) : $default_table;
$game_nexp      = $serv->iniLoad('Game.ini', true)                ? json_decode(json_encode($serv->iniGetArray()), true) : $default_table;
$engine_nexp    = $serv->iniLoad('Engine.ini', true)              ? json_decode(json_encode($serv->iniGetArray()), true) : $default_table;

$strcfg         = $serv->cfgGetString();

$form           = null;
$ark_opt        = null;
$flags          = array();
$i              = 0;


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
$eventlist      .= "<option value=\"none\">{::lang::php::sc::page::konfig::noevent}</option>";
$curr           = $serv->cfgRead("arkopt_ActiveEvent");
foreach($events as $k) {
    $eventlist  .="<option value=\"$k\" ".(($curr == $k) ? "selected=\"true\"" : null).">$k</option>";
}
$form           .= '
    <tr>
        <td class="p-2">arkopt_ActiveEvent</td>
        <td class="p-2">
            <input type="hidden" name="key[]" readonly value="arkopt_ActiveEvent">
            <select type="text" name="value[]" class="form-control form-control-sm">'.$eventlist.'</select>
        </td>
    </tr>';

// Wenn Installiert dann gebe Inis aus
if ($serv->isInstalled()) {
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
    $remove[]       = "arkopt_ActiveEvent";

    if(!$serv->modSupport()) {
        $remove[]   = "ark_GameModIds";
        $remove[]   = "serverMapModId";
    }

    $serv->cfgGetArray();
    $ini = parse_ini_file(__ADIR__.'/remote/arkmanager/instances/'.$url[2].'.cfg', false);
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
                        <option value="" '.(!$session_user->perm("$perm/konfig/arkmanager") && $val != "" ? "disabled" : null).'>{::lang::allg::default::select}</option>';

                    $mapjson = $helper->fileToJson(__ADIR__."/app/json/panel/maps.json");
                    foreach ($mapjson as $map => $infos) {
                        if(($infos["mod"] == 1 && $serv->modSupport()) || $infos["mod"] == 0)
                            $add .= '<option id="'.$map.'" value="'.$map.'" data-mod="'.$infos["mod"].'" data-modid="'.$infos["modid"].'" '.($map == $val ? "selected" : null).' '.($map == $val ? "" : (!$session_user->perm("$perm/konfig/arkmanager") ? "disabled" : "")).'>
                                '.($infos["mod"] == 1 ? "[MOD] " : null).$infos["name"].'
                            </option>';
                    }

                    $add .= '</select></div>';
                }

                // Totalmod Select
                if($key == "ark_TotalConversionMod") {
                    $add .= '<div class="input-group-append"><select class="form-control form-control-sm" onchange="settmod()" id="tmodsel">
                        <option value="" '.(!$session_user->perm("$perm/konfig/arkmanager") && $val != "" ? "disabled" : null).'>{::lang::allg::default::select}</option>';

                    $tmodjson = $helper->fileToJson(__ADIR__."/app/json/panel/tmods.json");
                    foreach ($tmodjson as $tmod => $infos) {
                        if(($infos["offi"] == 0 && $serv->modSupport()) || $infos["offi"] == 1)
                            $add .= '<option value="'.$infos["modid"].'" '.($infos["modid"] == $val ? "selected" : null).' '.($infos["modid"] == $val ? "" : (!$session_user->perm("$perm/konfig/arkmanager") ? "disabled" : "")).'>
                                '.($infos["offi"] == 0 ? "[MOD] " : null).$tmod.'
                            </option>';
                    }

                    $add .= '</select></div>';
                }

                $formtype = (!in_array($key, $no_del)) ?
                // wenn nicht im array
                '<div class="input-group mb-0">
                    <input type="hidden" name="key[]" readonly value="'.$key.'">
                    <input type="text" name="value[]" class="form-control form-control-sm" value="'.$val.'" id="input_'.$key.'" '.(!$session_user->perm("$perm/konfig/arkmanager") ? "readonly" : null).'>
                    <div class="input-group-append">
                    <span '.(!$session_user->perm("$perm/konfig/arkmanager") ? null : "'onclick=\"remove(\''.md5($key).'\')\"").' style="cursor:pointer" class="input-group-btn btn-danger pr-2 pl-2 pt-1 '.(!$session_user->perm("$perm/konfig/arkmanager") ? "disabled" : null).'" id="basic-addon2"><i class="fa fa-times" aria-hidden="true"></i></span>
                    </div>
                </div>' :
                //sonst
                '<div class="input-group mb-0"><input type="hidden" name="key[]" readonly value="'.$key.'">
                <input type="text" name="value[]" class="form-control form-control-sm" value="'.$val.'" id="input_'.$key.'" '.(!$session_user->perm("$perm/konfig/arkmanager") ? "readonly" : null).'>'.$add.'</div>';

                $form .= '
                    <tr class="'.(($serv->clusterIn() && in_array($key, $hide_cluster)) ? "d-none" : null).'" id="'.md5($key).'">
                        <td class="p-2">'.$key.'</td>
                        <td class="p-2">
                        '.$formtype.'
                        </td>
                    </tr>';
            }
        };
    }
}

// Erstelle Flaggen liste und verarbeite gesetzte Flaggen
$flags_json = $helper->fileToJson(__ADIR__."/app/json/panel/flags.json", true);
$i = 0;
foreach($flags_json as $k => $v) {
    $sel        = (in_array("arkflag_$v", $flags)) ? 'checked="true"' : null;
    if($i == 0) $ark_flag .= '<div class="row">';
    $ark_flag   .= '  <div class="icheck-primary mb-3 col-lg-6 col-12">
                        <input type="checkbox" name="flag[]" value="' . $v . '" id="' . md5($v) . '" ' . $sel . ' '.(!$session_user->perm("$perm/konfig/arkmanager") ? "disabled" : null).'>
                        <label for="' . md5($v) . '">
                            ' . $v . '
                        </label>
                    </div>';
    if($i == 1) {
        $ark_flag   .= '</div>';
        $i          = 0;
    }
    else {
        $i++;
    }
}

// neuer Editor
$CFGs = array(
    "GameUserSettings",
    "Game",
    "Engine"
);

foreach ($CFGs as $CFG) {
    if(@file_exists($serv->dirSavegames(true)."/Config/LinuxServer/$CFG.ini")) {
        $CURR            = $serv->iniLoad("$CFG.ini", true);
        $CURR            = $serv->iniext;
        $RAW_DEFAULT     = $helper->fileToJson(__ADIR__."/app/json/panel/default_$CFG.json");
        $CONV_DEFAULT    = convert_ini($RAW_DEFAULT);
        $FINAL_INI       = array_replace_recursive($CONV_DEFAULT, $CURR);
        $Former_arr      = create_ini_form($FINAL_INI, $CFG, $RAW_DEFAULT, $serv->name());
        $re[$CFG]        = $Former_arr["form"];
        $re["$CFG-rest"] = $Former_arr["rest"];
    }
    else {
        $re[$CFG]        = "";
        $re["$CFG-rest"] = "";
    }
}

if ($ifckonfig) $resp_cluster .= $alert->rd(301, 3);
$page_tpl->r('ark_opt', $ark_opt);
$page_tpl->r('ark_flag', $ark_flag);
$page_tpl->r('form', $form);
$page_tpl->r('form_GUS', $re["GameUserSettings"]);
$page_tpl->r('form_GAME', $re["Game"]);
$page_tpl->r('form_ENGINE', $re["Engine"]);
$page_tpl->r('form_GUS_rest', $re["GameUserSettings-rest"]);
$page_tpl->r('form_GAME_rest', $re["Game-rest"]);
$page_tpl->r('form_ENGINE_rest', $re["Engine-rest"]);
$page_tpl->r('strcfg', $strcfg);
$page_tpl->r('gus', $gus);
$page_tpl->r('game', $game);
$page_tpl->r('engine', $engine);
$page_tpl->r('eventlist', $eventlist);
$page_tpl->r('amcfg', $KUTIL->fileGetContents(__ADIR__.'/remote/arkmanager/instances/'.$url[2].'.cfg'));
$page_tpl->r('WEBURL', $webserver["sendin"]);
$page_tpl->rif('expert', $user->expert());
$page_tpl->rif('show', $show);
$panel = $page_tpl->load_var();