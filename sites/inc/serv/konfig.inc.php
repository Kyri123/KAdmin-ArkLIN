<?php
$resp = null;
if(isset($_POST['savecfg'])) {
    $value = $_POST['value'];
    $key = $_POST['key'];
    $opt = $_POST['opt'];
    $flag = $_POST['flag'];
    $cfg = null;

    for($i=0;$i<count($key);$i++) {
        $cfg .= $key[$i].'="'.$value[$i].'"
';
    }

    $cfg .= $flag.$opt;
    $cfg = ini_save_rdy($cfg);
    $path = 'remote/arkmanager/instances/'.$url[2].'.cfg';
    if(file_put_contents($path, $cfg)) $resp = meld('success mb-4', 'Arkmanger.cfg wurde gespeichert!', 'Gespeichert', 'fas fa-check', 'fas fa-exclamation-circle');
}

// Game,GUS,Engine.ini Speichern
if(isset($_POST['save'])) {
    $type = $_POST["type"];
    $text = $_POST["text"];
    $path = $serv->get_konfig_dir().$type;
    if(file_exists($path)) {
        $text = ini_save_rdy($text);
        if(file_put_contents($path, $text)) {
            $resp = meld('success', 'Konfiguration wurde gespeichert', 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Konfiguration wurde nicht gespeichert', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Konfiguration wurde noch nicht vom Server erstellt', 'Fehler!', null);
    }
}


$page_tpl = new Template('konfig.htm', 'tpl/serv/sites/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Konfiguration</li>';


$page_tpl->repl('cfg' ,$url[2]);

$gus = "Wird nicht Gespeichert da die Ini noch nicht Exsistiert! \nBitte starte den Server bevor du weiter machst!";
$game = "Wird nicht Gespeichert da die Ini noch nicht Exsistiert! \nBitte starte den Server bevor du weiter machst!";
$engine = "Wird nicht Gespeichert da die Ini noch nicht Exsistiert! \nBitte starte den Server bevor du weiter machst!";

if($serv->ini_load('GameUserSettings.ini', true)) $gus = $serv->ini_get_str();
if($serv->ini_load('Game.ini', true)) $game = $serv->ini_get_str();
if($serv->ini_load('Engine.ini', true)) $engine = $serv->ini_get_str();
$strcfg = $serv->cfg_get_str();

$form = null;
$ark_flag = null;
$ark_opt = null;
$i = 0;
if($serv->check_install()) {
    $serv->cfg_get();
    $ini = parse_ini_file('remote/arkmanager/instances/'.$url[2].'.cfg', false);
    foreach($ini as $key => $val) {
        if ($key) {
            if(strpos($key, 'arkflag_') !== false) {
                $ark_flag .= $key.'="'.$val.'"
';
            }
            elseif(strpos($key, 'arkopt_') !== false) {
                $ark_opt .= $key.'="'.$val.'"
';
            }
            else {
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








$page_tpl->repl('ark_opt', $ark_opt);
$page_tpl->repl('ark_flag', $ark_flag);
$page_tpl->repl('form', $form);
$page_tpl->repl('strcfg', $strcfg);
$page_tpl->repl('gus', $gus);
$page_tpl->repl('game', $game);
$page_tpl->repl('engine', $engine);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>