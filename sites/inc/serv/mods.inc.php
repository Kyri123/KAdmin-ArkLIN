<?php
$resp = null;
$urls = 'http://dev.aa.chiraya.de/serverpage/'.$url[2].'/mods/';

if(isset($_POST['addmod'])) {
    $urle = $_POST['url'];
    $int = false;
    if(is_numeric($urle)) $int = true;
    if(strpos($urle, 'steamcommunity.com/sharedfiles/filedetails') || $int === true) {
        if(strpos($urle, 'id=') || $int === true) {
            $modid = $urle;
            if(!$int) {
                $urle = parse_url($urle);
                $query = $urle['query'];
                if (strpos($query, '&')) {
                    $exp = explode('&', $query);
                    for($i=0;$i<count($exp);$i++) {
                        $expm = explode('=', $exp[$i]);
                        if($expm[0] == 'id') {
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

            $steamapi->modid = $modid;
            if($steamapi->check_mod()) {
                $mod_cfg = $serv->cfg_read('ark_GameModIds');
                $mods = explode(',', $mod_cfg);
                if(count($mods) > 1 || $mods[0] > 0) {
                    $exsists = false;
                    for($i=0;$i<count($mods);$i++) {
                        if($mods[$i] == $modid) {
                            $exsists = true;
                            break;
                        }
                    }
                    if($exsists === false) {
                        if($ckonfig['install_mod'] == 1) {
                            $jobs->set($serv->show_name());
                            $jobs->create('installmod ' . $modid);
                        }
                        $i = count($mods)+1;
                        $mods[$i] = $modid;
                        $save_data = implode(',', $mods);
                        $serv->cfg_write('ark_GameModIds', $save_data);
                        $serv->cfg_save();
                        header('Location: '.$urls);
                        exit;
                    }
                    else {
                        $resp = meld('danger', 'Mod Exsistiert bereits', 'Fehler!', null);
                    }
                }
                else {
                    $serv->cfg_write('ark_GameModIds', $modid);
                    $serv->cfg_save();
                }
            }
            else {
                $resp = meld('danger', 'ID ist kein Gültiger Workshop inhalt', 'Fehler!', null);
            }
        }
        else {
            $resp = meld('danger', 'keine ID im Link', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'kein Gültiger Workshop link', 'Fehler!', null);
    }
}

if(isset($url[4]) && isset($url[5]) && $url[4] == 'removelocal') {
    $path = $serv->get_dir()."/ShooterGame/Content/Mods/".$url[5];

    $resp = meld('danger', 'Verzeichnis nicht Entfernt', 'Fehler!', null);

    if(file_exists($path)) {
        $jobs = new jobs();
        $jobs->set($serv->show_name());
        $jobs->create("uninstallmod ".$url[5]);
        $mod = $steamapi->getmod_class($url[5]);
        $resp = meld('success', 'Verzeichnis der mod <b>'.$mod->title.'</b> wird Entfernt (dauert höhsten eine Minute)', 'Erfolgreich!', null);
    }
}

if(isset($url[4]) && isset($url[5]) && ($url[4] == 'remove' || $url[4] == 'bot' || $url[4] == 'top')) {
    $action = $url[4];
    $modid = $url[5];
    // change order
    $mod_cfg = $serv->cfg_read('ark_GameModIds');
    $mods = explode(',', $mod_cfg);
    // replacer
    for($i=0;$i<count($mods);$i++) {
        if($mods[$i] == $modid) {
            if($action == 'bot') {
                $iafter = $i+1;
                $modid_after = $mods[$iafter];
                $mods[$iafter] = $modid;
                $mods[$i] = $modid_after;
                break;
            }
            if($action == 'top') {
                $ibefore = $i-1;
                $modid_before = $mods[$ibefore];
                $mods[$ibefore] = $modid;
                $mods[$i] = $modid_before;
                break;
            }
            if($action == 'remove') {
                $id = $mods[$i];
                $mods[$i] = 'removed';
                break;
            }
        }
    }
    // builder
    for($i=0;$i<count($mods);$i++) {
        if($mods[$i] == 'removed') {
            if($ckonfig['uninstall_mod'] == 1) {
                $jobs->set($serv->show_name());
                $jobs->create('uninstallmod ' . $id);
            }
            unset($mods[$i]);
            $resp = meld('success', 'Mod <b>'.$steamapi->getmod_class($id)->title.'</b> Entfernt', 'Erfolgreich!', null);
            break;
        }
    }
    $mod_builder = implode(',', $mods);
    // saver
    $serv->cfg_write('ark_GameModIds', $mod_builder);
    $serv->cfg_save();
}

if(!$ifcadmin) $resp .= meld_full('info', "Funktion Mods wurde Deaktiviert da die Synchronisation aktiv ist. Gehe für Änderungen zum Masterserver.", 'Mods: Sync Mode', null);
$page_tpl = new Template('mods.htm', 'tpl/serv/sites/');
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Modifikationen</li>';
$page_tpl->load();
$page_tpl->repl('cfg' ,$url[2]);
$page_tpl->repl('urls' ,$urls);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>