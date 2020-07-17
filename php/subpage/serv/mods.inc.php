<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::saves::pagename}';
$resp = null;
$urls = '/servercenter/'.$url[2].'/mods/';
$page_tpl = new Template('mods.htm', 'app/template/sub/serv/');
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::saves::urltop}</li>';

if(!$serv->mod_support()) {
    header('Location: /servercenter/'.$url[2].'/home'); exit;
}

if (isset($_POST['addmod'])) {
    $urler = $_POST['url'];
    foreach($urler as $k => $urle) {
        $int = is_numeric($urle);
        if ((strpos($urle, 'steamcommunity.com/sharedfiles/filedetails') || $int === true) && $urle != "") {
            if (strpos($urle, 'id=') || $int === true) {
                $modid = $urle;
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
    
                $steamapi->modid = $modid;
                if ($steamapi->check_mod()) {
                    $mod_cfg = $serv->cfg_read('ark_GameModIds');
                    $mods = explode(',', $mod_cfg);
                    if (count($mods) > 1 || $mods[0] > 0) {
                        $exsists = false;
                        for ($i=0;$i<count($mods);$i++) {
                            if ($mods[$i] == $modid) {
                                $exsists = true;
                                break;
                            }
                        }
                        if ($exsists === false) {
                            if ($ckonfig['install_mod'] == 1) {
                                $jobs->set($serv->name());
                                $jobs->arkmanager('installmod ' . $modid);
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
                            $resp = $alert->rd(5);
                        }
                    }
                    else {
                        $serv->cfg_write('ark_GameModIds', $modid);
                        $serv->cfg_save();
                    }
                } else {
                    $resp = $alert->rd(20);
                }
            } else {
                $resp = $alert->rd(19);
            }
        } else {
            $resp = $alert->rd(18);
        }
    }
}

if (isset($url[4]) && isset($url[5]) && $url[4] == 'removelocal') {
    $path = $serv->dir_main()."/ShooterGame/Content/Mods/".$url[5];
    $resp = $alert->rd(1);

    if (file_exists($path)) {
        $jobs = new jobs();
        $jobs->set($serv->name());
        $jobs->arkmanager("uninstallmod ".$url[5]);
        $mod = $steamapi->getmod_class($url[5]);

        $alert->overwrite_text = "{::lang::php::sc::page::mods::mod_removed_dir}";
        $alert->r("name", $mod->title);
        $resp = $alert->rd(100);
    }
}

if (isset($url[4]) && isset($url[5]) && ($url[4] == 'remove' || $url[4] == 'bot' || $url[4] == 'top')) {
    $action = $url[4];
    $modid = $url[5];
    // change order
    $mod_cfg = $serv->cfg_read('ark_GameModIds');
    $mods = explode(',', $mod_cfg);
    // replacer
    for ($i=0;$i<count($mods);$i++) {
        if ($mods[$i] == $modid) {
            if ($action == 'bot') {
                $iafter = $i+1;
                $modid_after = $mods[$iafter];
                $mods[$iafter] = $modid;
                $mods[$i] = $modid_after;
                break;
            }
            if ($action == 'top') {
                $ibefore = $i-1;
                $modid_before = $mods[$ibefore];
                $mods[$ibefore] = $modid;
                $mods[$i] = $modid_before;
                break;
            }
            if ($action == 'remove') {
                $id = $mods[$i];
                $mods[$i] = 'removed';
                break;
            }
        }
    }
    // builder
    for ($i=0;$i<count($mods);$i++) {
        if ($mods[$i] == 'removed') {
            if ($ckonfig['uninstall_mod'] == 1) {
                $jobs->set($serv->name());
                $jobs->arkmanager('uninstallmod ' . $id);
            }
            unset($mods[$i]);
            $alert->overwrite_text = "{::lang::php::sc::page::mods::mod_removed}";
            $alert->r("name", $steamapi->getmod_class($id)->title);
            $resp = $alert->rd(100);
            break;
        }
    }
    $mod_builder = implode(',', $mods);
    // saver
    $serv->cfg_write('ark_GameModIds', $mod_builder);
    $serv->cfg_save();
}

if ($ifcadmin) {
    $resp_cluster .= $alert->rd(302, 3);
}
$page_tpl->load();
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('urls' ,$urls);
$page_tpl->session();
$panel = $page_tpl->load_var();


?>