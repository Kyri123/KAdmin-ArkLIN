<?php
$resp = null;
$urls = 'http://dev.aa.chiraya.de/serverpage/'.$url[2].'/mods/';

if(isset($_POST['addmod'])) {
    #https://steamcommunity.com/sharedfiles/filedetails/?id=719928795
    $urle = $_POST['url'];
    if(strpos($urle, 'steamcommunity.com/sharedfiles/filedetails') !== false) {
        if(strpos($urle, 'id=') !== false) {
            $urle = parse_url($urle);
            $query = $urle['query'];
            if (strpos($query, '&') !== false) {
                $exp = explode('&', $query);
                for($i=0;$i<count($exp);$i++) {
                    if(strpos($exp[$i], 'id') !== false) {
                        $expm = explode('=', $exp[$i]);
                        $modid = $expm[1];
                    }
                }
            }
            else {
                $expm = explode('=', $query);
                $modid = $expm[1];
            }

            $json = $steamapi->getmod($modid);
            if($json->response->publishedfiledetails[0]->consumer_app_id == 346110) {
                $mod_cfg = $serv->cfg_read('ark_GameModIds');
                $mods = explode(',', $mod_cfg);
                $exsists = false;
                for($i=0;$i<count($mods);$i++) {
                    if($mods[$i] == $modid) {
                        $exsists = true;
                        break;
                    }
                }
                if($exsists === false) {
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

if(isset($url[4]) && isset($url[5]) && ($url[4] == 'remove' OR $url[4] == 'bot' OR $url[4] == 'top')) {
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
                $mods[$i] = 'removed';
                break;
            }
        }
    }
    // builder
    for($i=0;$i<count($mods);$i++) {
        if($mods[$i] == 'removed') {
            unset($mods[$i]);
            break;
        }
    }
    $mod_builder = implode(',', $mods);
    // saver
    $serv->cfg_write('ark_GameModIds', $mod_builder);
    $serv->cfg_save();
}


$page_tpl = new Template('mods.htm', 'tpl/serv/sites/');
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Modifikationen</li>';
$page_tpl->load();
$page_tpl->repl('cfg' ,$url[2]);
$page_tpl->repl('urls' ,$urls);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>