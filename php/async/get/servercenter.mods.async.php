<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg = $_GET['cfg'];
$case = $_GET['case'];
$serv = new server($cfg);
$serv->cluster_load();
$ifslave = false; if ($serv->cluster_type() == 0 && $serv->cluster_in()) $ifslave = true;
$ifcmods = false; if ($serv->cluster_mods() && $ifslave && $serv->cluster_in()) $ifcmods = true;

switch ($case) {
    // CASE: Aktive Mods
    case "mods_active":
        $api = new steamapi();

        $resp = null;
        $site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $mods = explode(',', $serv->cfg_read("ark_GameModIds"));
        $y = 1;

        $total_count = count($mods);
        if ($total_count > 1 || $mods[0] > 0) {
            for ($i=0;$i<count($mods);$i++) {
                $api->modid = $mods[$i];
                if ($api->check_mod()) {
                    $mod = $api->getmod_class($mods[$i]);

                    $tpl = new Template('list_mods.htm', 'app/template/serv/page/list/');
                    $tpl->load();

                    $y = $i + 1;
                    $btns = null;

                    if ($i == 0 && $total_count > 1) {
                        $tpl->rif ('ifup', false);
                        $tpl->rif ('ifdown', true);
                    } elseif ($i == 0) {
                        $tpl->rif ('ifup', false);
                        $tpl->rif ('ifdown', false);
                    } elseif ($y != count($mods)) {
                        $tpl->rif ('ifup', true);
                        $tpl->rif ('ifdown', true);
                    } else {
                        $tpl->rif ('ifup', true);
                        $tpl->rif ('ifdown', false);
                    }
                    $tpl->r('modid', $mod->publishedfileid);
                    $tpl->rif ('empty', true);
                    $tpl->r('img', $mod->preview_url);
                    $tpl->r('cfg', $cfg);
                    $tpl->r('title', $mod->title);
                    $tpl->r('lastupdate', date('d.m.Y - H:i', $mod->time_updated));
                    $tpl->rif ("ifcmods", $ifcmods);
                    $resp .= $tpl->load_var();
                    $tpl = null;
                }
            }
        }
        // Wenn kein Mod gefunden wurde
        else {
            $tpl = new Template('list_mods.htm', 'app/template/serv/page/list/');
            $tpl->load();
            $tpl->r('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
            $tpl->r('title', "{::lang::php::async::get::servercenter::mods::no_mods_found}");
            $tpl->rif ('empty', false);
            $resp = $tpl->load_var();
            $tpl = null;
        }

        echo $resp;
        break;

    // CASE: Installierte Mods
    //
    case "mods_installed":

        $api = new steamapi();

        $resp = null;
        $site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $mods = explode(',', $serv->cfg_read("ark_GameModIds"));
        $y = 1;


        $total_count = count($mods);



        //List Local mods
        $array = dirToArray($serv->dir_main()."/ShooterGame/Content/Mods");
        $exp = explode(",", $serv->cfg_read("ark_GameModIds"));

        foreach($array as $key => $value) {
            $api->modid = $key;
            if ($api->check_mod()) {
                $mod = $api->getmod_class($key);

                $tpl = new Template('list_mods_local.htm', 'app/template/serv/page/list/');
                $tpl->load();
                $y = $i+1;
                $btns= null;
                $installed = false;
                if (in_array($key, $exp)) $installed = true;

                $tpl->r('modid', $mod->publishedfileid);
                $tpl->r('steamurl', $mod->file_url);
                $tpl->rif ('active', $installed);
                $tpl->r('img', $mod->preview_url);
                $tpl->r('cfg', $cfg);
                $tpl->r('rnd', rndbit(25));
                $tpl->r('title', $mod->title);
                $tpl->r('lastupdate', date('d.m.Y - H:i', $mod->time_updated));
                $tpl->rif ("ifcmods", false); //ggf false durch $ifcmods ersetzten so wird diese funktion auch verwaltet
                $resp .= $tpl->load_var();
                $tpl = null;
            }
        }
        // Wenn kein Mod gefunden wurde
        if ($resp == null) {
            $tpl = new Template('list_mods.htm', 'app/template/serv/page/list/');
            $tpl->load();
            $tpl->r('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
            $tpl->r('title', "{::lang::php::async::get::servercenter::mods::no_mods_found}");
            $tpl->rif ('empty', false);
            $resp = $tpl->load_var();
            $tpl = null;
        }

        echo $resp;
        break;
    default:
        echo "Case not found";
        break;
}
?>