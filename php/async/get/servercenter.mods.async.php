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
$ifslave = ($serv->cluster_type() == 0 && $serv->cluster_in());
$ifcmods = ($serv->cluster_mods() && $ifslave && $serv->cluster_in());
$dir_installed = $serv->dir_main()."/ShooterGame/Content/Mods";

switch ($case) {
    // CASE: Aktive Mods
    case "mods_active":

        $resp = null;
        $site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $mods = explode(',', $serv->cfg_read('ark_GameModIds'));
        $y = 1;

        $total_count = (is_countable($mods)) ? count($mods) : 0;
        $imgb = -1;
        if ($total_count > 0 && $serv->cfg_read('ark_GameModIds') != "") {
            for ($i=0;$i<count($mods);$i++) {
                $tpl = new Template('mods.htm', 'app/template/lists/serv/jquery/');
                $tpl->load();
                $curr_id = $mods[$i];

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

                if(isset($steamapi_mods[$mods[$i]])) {
                    $tpl->r('img', $steamapi_mods[$mods[$i]]["preview_url"]);
                    $modname = $steamapi_mods[$mods[$i]]["title"];
                    $l = strlen($modname); $lmax = 14;
                    if ($l > $lmax) {
                        $modname = substr($modname, 0 , $lmax) . "...";
                    }
                    $tpl->r('title_full', $steamapi_mods[$mods[$i]]["title"]);
                    $tpl->r('title', $modname);
                    $tpl->r('lastupdate', date('d.m.Y - H:i', $steamapi_mods[$mods[$i]]["time_updated"]));

                    // Todo Funktioniert noch nicht
                    $tpl->rif ('ifupdate', file_exists("$dir_installed/$curr_id.mod") ? filemtime("$dir_installed/$curr_id.mod") > $steamapi_mods[$mods[$i]]["time_updated"] : false);
                }
                else {
                    $tpl->r('title_full', "{::lang::allg::default::notinapimod}");
                    $tpl->r('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
                    $tpl->r('title', "{::lang::allg::default::notinapimod}");
                    $tpl->r('lastupdate', date('d.m.Y - H:i', time()));
                    $tpl->rif ('ifupdate', false);
                }

                $opt = null;
                for ($z=0;$z<count($mods);$z++) {
                    if($i != $z) $opt .= "<option value='$z'>$z</option>";
                }

                while (true) {
                    $rand = rand($head_img["min"], $head_img["max"]);
                    if($rand != $imgb) {
                        $imgb = $rand;
                        break;
                    }
                }

                $tpl->r('update', date('d.m.Y - H:i', (isset($steamapi_mods[$mods[$i]]["time_updated"]) ? $steamapi_mods[$mods[$i]]["time_updated"] : 0)));
                $tpl->r('pos', $i);
                $tpl->r('poslist', $opt);
                $tpl->r('modid', $mods[$i]);
                $tpl->r('cfg', $cfg);
                $tpl->r('img_head', $head_img["img"][$imgb]);
                $tpl->rif ('hide', false);
                $tpl->rif ("ifcmods", $ifcmods);
                $resp .= $tpl->load_var();
                $tpl = null;
            }
        }

        // Wenn kein Mod Gefunden wurde
        if($resp == null) {
            $tpl = new Template('content.htm', 'app/template/universally/default/');
            $tpl->load();
            $tpl->r('content', '<ul class="list-group ml-2 mr-2" style="border:0; width: 100%">
                                    <div class="list-group-item bg-warning">
                                        <div class="row p-0">
                                            <div class="col-12">
                                                <i class="text-black-50 fas fa-exclamation-triangle -align-left position-absolute" style="font-size: 45px;color: rgba(0,0,0,.5)!important;" height="50" width="50"></i>
                                                <div style="margin-left: 60px;">{::lang::php::async::get::servercenter::mods::no_mods_found}<br><span style="font-size: 11px;">{::lang::servercenter::mods::nomodfound}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </ul>');
            $resp = $tpl->load_var();
            $tpl = null;
        }

        echo $resp;
        break;

    // CASE: Installierte Mods
    //
    case "mods_installed":

        $api = new steamapi();

        $imgb = -1;
        $resp = null;
        $site = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $dir = $serv->dir_main()."/ShooterGame/Content/Mods";

        $mods = explode(',', $serv->cfg_read("ark_GameModIds"));

        //List Local mods
        $array = scandir($dir);
        $mod_arr = [];
        
        foreach($array as $key => $value) {
            if(is_dir("$dir/$value")) {
                $info = pathinfo("$dir/$value");
                if(is_numeric($info['basename'])) $mod_arr[] = $info['basename'];
            }
        }

        $mods_arr = json_decode(json_encode($steamapi->getmod_list($cfg."_installed", $mod_arr, 0, true)), true)["response"]["publishedfiledetails"];
         
        foreach($mods_arr as $key => $value) {
            if(
                isset($value["publishedfileid"]) &&
                isset($value["file_url"]) &&
                isset($value["preview_url"]) &&
                isset($value["time_updated"]) &&
                isset($value["title"]) 
            ) {
                $tpl = new Template('mods_local.htm', 'app/template/lists/serv/jquery/');
                $tpl->load();
                $path = $serv->dir_main()."/ShooterGame/Content/Mods/".$value["publishedfileid"];
                $installed = (file_exists($path) && in_array($value["publishedfileid"], $mods));

                // new
                while (true) {
                    $rand = rand($head_img["min"], $head_img["max"]);
                    if($rand != $imgb) {
                        $imgb = $rand;
                        break;
                    }
                }

                $tpl->r('img', $value["preview_url"]);
                $modname = $value["title"];
                $l = strlen($modname); $lmax = 25;
                if ($l > $lmax) {
                    $modname = substr($modname, 0 , $lmax) . "...";
                }

                $tpl->r('title_full', $value["title"]);
                $tpl->r('title', $modname);
                $tpl->r('lastupdate', date('d.m.Y - H:i', $value["time_updated"]));
                $tpl->r('update', date('d.m.Y - H:i', $value["time_updated"]));
                $tpl->r('modid', $value["publishedfileid"]);
                $tpl->r('cfg', $cfg);
                $tpl->r('img_head', $head_img["img"][$imgb]);
                $tpl->r('color', $installed ? "text-success" : "text-danger");
                $tpl->rif ('hide', true);
                $tpl->rif ("ifcmods", $ifcmods);

                // old
                if($value["publishedfileid"] != 111111111) $resp .= $tpl->load_var();
                $tpl = null;
            }
        }

        // Wenn kein Mod Gefunden wurde
        if($resp == null) {
            $tpl = new Template('content.htm', 'app/template/universally/default/');
            $tpl->load();
            $tpl->r('content', '<ul class="list-group ml-2 mr-2" style="border:0; width: 100%">
                                    <div class="list-group-item bg-warning">
                                        <div class="row p-0">
                                            <div class="col-12">
                                                <i class="text-black-50 fas fa-exclamation-triangle -align-left position-absolute" style="font-size: 45px;color: rgba(0,0,0,.5)!important;" height="50" width="50"></i>
                                                <div style="margin-left: 60px;">{::lang::php::async::get::servercenter::mods::no_mods_found}<br><span style="font-size: 11px;">{::lang::servercenter::mods::nomodfound}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </ul>');
            $resp = $tpl->load_var();
            $tpl = null;
        }

        echo $resp;
        break;
    default:
        echo "Case not found";
        break;
}
$mycon->close();
