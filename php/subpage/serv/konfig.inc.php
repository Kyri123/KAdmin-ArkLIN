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
    $opt = $_POST['opt'];
    $flag = $_POST['flag'];
    $cfg = null;

    for ($i=0;$i<count($key);$i++) {
        $cfg .= $key[$i].'="'.$value[$i]."\"\n";
    }

    $cfg .= $flag.$opt;
    $cfg = ini_save_rdy($cfg);
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
$ark_flag = null;
$ark_opt = null;
$i = 0;
if ($serv->isinstalled()) {
    $serv->cfg_get();
    $ini = parse_ini_file('remote/arkmanager/instances/'.$url[2].'.cfg', false);
    foreach($ini as $key => $val) {
        if ($key) {
            if (strpos($key, 'arkflag_') !== false) {
                $ark_flag .= $key.'="'.$val.'"
';
            }
            elseif (strpos($key, 'arkopt_') !== false) {
                $ark_opt .= $key.'="'.$val.'"
';
            } else {
                $form .= '
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">'.$key.'</label>
                    <div class="col-sm-9">
                        <input type="hidden" name="key[]" readonly value="'.$key.'">
                        <input type="text" name="value[]" class="form-control"  value="'.$val.'">
                    </div>
                </div>';
            }
        };
    }
}

if ($ifckonfig) $resp .= $alert->rd(301, 3);
$page_tpl->r('ark_opt', $ark_opt);
$page_tpl->r('ark_flag', $ark_flag);
$page_tpl->r('form', $form);
$page_tpl->r('strcfg', $strcfg);
$page_tpl->r('gus', $gus);
$page_tpl->r('game', $game);
$page_tpl->r('engine', $engine);
$page_tpl->session();
$panel = $page_tpl->load_var();
?>