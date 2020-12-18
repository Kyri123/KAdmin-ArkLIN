<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

require('../main.inc.php');
$cfg            = $_GET['cfg'];
$case           = $_GET['case'];
$serv           = new server($cfg);
$serv->clusterLoad();
$ifslave        = $serv->clusterRead("type") == 0  && $serv->clusterIn();
$ifcmods        = $serv->clusterRead("mods")       && $ifslave && $serv->clusterIn();
$dir_installed  = $serv->dirMain()."/ShooterGame/Content/Mods";

switch ($case) {
    // CASE: Aktive Mods
    case "mods_active":

        $resp           = null;
        $site           = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $mods           = explode(',', $serv->cfgRead('ark_GameModIds'));
        $y              = 1;

        $total_count    = (is_countable($mods)) ? count($mods) : 0;
        $imgb           = -1;
        if ($total_count > 0 && $serv->cfgRead('ark_GameModIds') != "")
            for ($i=0;$i<count($mods);$i++) {
                $tpl        = new Template('mods.htm', __ADIR__.'/app/template/lists/serv/jquery/');
                $curr_id    = $mods[$i];

                $y          = $i + 1;
                $btns       = null;

                $tpl->load();
                $tpl->rif ('ifup',$i != 0 && $total_count > 1);
                $tpl->rif ('ifdown',$y != $total_count);

                if(isset($steamapi_mods[$mods[$i]])) {
                    $title      = $steamapi_mods[$mods[$i]]["title"];
                    $modname    = strlen($title) > 14 ? substr($title, 0 , 14) . "..." : $title;

                    $tpl->r('img', $steamapi_mods[$mods[$i]]["preview_url"]);
                    $tpl->r('title_full', $steamapi_mods[$mods[$i]]["title"]);
                    $tpl->r('title', $modname);
                    $tpl->r('lastupdate', date('d.m.Y - H:i', $steamapi_mods[$mods[$i]]["time_updated"]));
                    $tpl->rif ('ifupdate', false);
                }
                else {
                    $tpl->r('title_full', "{::lang::allg::default::notinapimod}");
                    $tpl->r('img', "https://steamuserimages-a.akamaihd.net/ugc/885384897182110030/F095539864AC9E94AE5236E04C8CA7C2725BCEFF/");
                    $tpl->r('title', "{::lang::allg::default::notinapimod}");
                    $tpl->r('lastupdate', date('d.m.Y - H:i', time()));
                    $tpl->rif ('ifupdate', false);
                }

                $opt = null;
                for ($z=0;$z<count($mods);$z++)
                    if($i != $z) $opt .= "<option value='$z'>$z</option>";

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

        // Wenn kein Mod Gefunden wurde
        if($resp == null) {
            $tpl = new Template('content.htm', __ADIR__.'/app/template/universally/default/');
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

        $api        = new steamapi();

        $imgb       = -1;
        $resp       = null;
        $site       = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $dir        = $serv->dirMain()."/ShooterGame/Content/Mods";
        $mods       = explode(',', $serv->cfgRead("ark_GameModIds"));

        //List Local mods
        $array      = scandir($dir);
        $mod_arr    = [];
        
        foreach($array as $key => $value)
            if(is_dir("$dir/$value")) {
                $info = pathinfo("$dir/$value");
                if(is_numeric($info['basename'])) $mod_arr[] = $info['basename'];
            }

        $mods_arr   = json_decode(json_encode($steamapi->getmod_list($cfg."_installed", $mod_arr, 0, true)), true)["response"]["publishedfiledetails"];
         
        foreach($mods_arr as $key => $value)
            if(
                isset($value["publishedfileid"])    &&
                isset($value["file_url"])           &&
                isset($value["preview_url"])        &&
                isset($value["time_updated"])       &&
                isset($value["title"]) 
            ) {
                $tpl        = new Template('mods_local.htm', __ADIR__.'/app/template/lists/serv/jquery/');
                $path       = $serv->dirMain()."/ShooterGame/Content/Mods/".$value["publishedfileid"];
                $installed  = (@file_exists($path) && in_array($value["publishedfileid"], $mods));
                $title      = $value["title"];
                $modname    = strlen($title) > 25 ? substr($title, 0 , 25) . "..." : $title;

                // new
                while (true) {
                    $rand = rand($head_img["min"], $head_img["max"]);
                    if($rand != $imgb) {
                        $imgb = $rand;
                        break;
                    }
                }

                $tpl->load();
                $tpl->r('img', $value["preview_url"]);
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

        // Wenn kein Mod Gefunden wurde
        if($resp == null) {
            $tpl = new Template('content.htm', __ADIR__.'/app/template/universally/default/');
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
