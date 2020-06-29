<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::konfig::pagename}';
$page_tpl = new Template('konfig.htm', 'app/template/serv/page/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::konfig::urltop}</li>';

// arkmanager.cfg Speichern
$resp = null;
if (isset($_POST['savecfg'])) {
    $value = $_POST['value'];
    $key = $_POST['key'];
    $flag = $_POST['flag'];
    $cfg = null;

    for ($i=0;$i<count($key);$i++) {
        $write = ($value[$i] == "none") ? false : true;
        if($write) $cfg .= $key[$i].'="'.$value[$i]."\"\n";
    }

    if(is_array($flag)) {
        for ($i=0;$i<count($flag);$i++) {
            $cfg .= "arkflag_".$flag[$i].'="True"'."\n";
        }
    }

    $cfg .= $flag;
    $cfg = ini_save_rdy($cfg);
    $path = 'remote/arkmanager/instances/'.$url[2].'.cfg';
    if (file_put_contents($path, $cfg)) {
        $resp = $alert->rd(102);
        header("Refresh:0"); exit;
    } else {
        $resp = $alert->rd(1);
    } 
}

// arkmanager.cfg (Expert) Speichern
$resp = null;
if (isset($_POST['savecfg_expert'])) {
    $txtarea = $_POST['txtarea'];
    $cfg = ini_save_rdy($txtarea);
    $path = 'remote/arkmanager/instances/'.$url[2].'.cfg';
    if (file_put_contents($path, $cfg)) {
        $resp = $alert->rd(102);
    } else {
        $resp = $alert->rd(1);
    } 
}

// Game,GUS,Engine.ini Speichern
if (isset($_POST['save'])) {
    $type = $_POST["type"];
    $text = $_POST["text"];
    $path = $serv->dir_konfig().$type;
    if (file_exists($path)) {
        $text = ini_save_rdy($text);
        if (file_put_contents($path, $text)) {
            $resp = $alert->rd(102);
        } else {
            $resp = $alert->rd(1);
        }
    } else {
        $resp = $alert->rd(1);
    }
}

$page_tpl->r('cfg' ,$url[2]);

$default = "{::lang::php::sc::page::konfig::ini_notfound}";

$gus = ($serv->ini_load('GameUserSettings.ini', true)) ? $gus = $serv->ini_get_str() : $default;
$game = ($serv->ini_load('Game.ini', true)) ? $serv->ini_get_str() : $default;
$engine = ($serv->ini_load('Engine.ini', true)) ? $serv->ini_get_str() : $default;

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
    $remove = array(
        "arkopt_ActiveEvent"
    );


    $serv->cfg_get();
    $ini = parse_ini_file('remote/arkmanager/instances/'.$url[2].'.cfg', false);
    foreach($ini as $key => $val) {
        if ($key) {
            if (in_array($key, $remove)) {
                null;
            }
            elseif (strpos($key, 'arkflag_') !== false) {
                array_push($flags, $key);
            }
            else {
                $formtype = (!in_array($key, $no_del)) ? 
                // wenn nicht im array
                '<div class="input-group mb-0">
                    <input type="hidden" name="key[]" readonly value="'.$key.'">
                    <input type="text" name="value[]" class="form-control form-control-sm"  value="'.$val.'">
                    <div class="input-group-append">
                    <span onclick="remove(\''.md5($key).'\')" style="cursor:pointer" class="input-group-btn btn-danger pr-2 pl-2 pt-1" id="basic-addon2"><i class="fa fa-times" aria-hidden="true"></i></span>
                    </div>
                </div>' : 
                //sonst
                '<input type="hidden" name="key[]" readonly value="'.$key.'">
                <input type="text" name="value[]" class="form-control form-control-sm"  value="'.$val.'">';

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

//flags
$flags_json = $helper->file_to_json("app/json/panel/flags.json", true);
foreach($flags_json as $k => $v) {
    $sel = (in_array("arkflag_$v", $flags)) ? 'selected="true"' : null;
    $ark_flag .= "<option value=\"$v\" $sel>$v</option>";
}

if ($ifckonfig) $resp .= $alert->rd(301, 3);
$page_tpl->r('ark_opt', $ark_opt);
$page_tpl->r('ark_flag', $ark_flag);
$page_tpl->r('form', $form);
$page_tpl->r('strcfg', $strcfg);
$page_tpl->r('gus', $gus);
$page_tpl->r('game', $game);
$page_tpl->r('engine', $engine);
$page_tpl->r('eventlist', $eventlist);
$page_tpl->r('amcfg', file_get_contents('remote/arkmanager/instances/'.$url[2].'.cfg'));
$page_tpl->rif('expert', $user->expert());
$page_tpl->session();
$panel = $page_tpl->load_var();
?>