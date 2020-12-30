<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Vars
$tpl_dir        = __ADIR__.'/app/template/core/home/';
$setsidebar     = false;
$pagename       = "{::lang::php::home::pagename}";
$urltop         = "<li class=\"breadcrumb-item\">$pagename</li>";

//tpl
$tpl            = new Template('tpl.htm', $tpl_dir);
$tpl->load();

//lasten


// Server
$all            = $helper->fileToJson(__ADIR__."/app/json/serverinfo/all.json");
$a_cfg          = $all["cfgs"];
$count_server   = count($a_cfg);

$serv_list      = null;
foreach ($a_cfg as $key => $value) {
    $value      = str_replace(".cfg", null, $value);
    $serv       = new server($value);

    $list       = new Template('server_list.htm', $tpl_dir);
    $list->load();

    $state_code = $serv->stateCode();
    $converted  = convertstate($state_code);
    $data       = $serv->status();

    $name       = $serv->cfgRead("ark_SessionName");
    $cfg        = $serv->name();

    $vs         = $data->version != null ? " - V.".$data->version : null;

    $map_file   = __ADIR__."/app/dist/img/igmap/".$serv->cfgRead("serverMap").".jpg";
    $map_path   = !file_exists($map_file)? "$ROOT/app/dist/img/igmap/ark.png" : "$ROOT/app/dist/img/igmap/".$serv->cfgRead("serverMap").".jpg";

    $l          = strlen($name);
    $lmax       = 25;
    if ($l > $lmax) $name   = substr($name, 0 , $lmax) . " ...";

    $list->r("img", $map_path);
    $list->r("name", $name);
    $list->r("cfg", $cfg);
    $list->r("color", $converted["color"]);
    $list->r("state_str", $converted["str"]);
    $list->r("aplayer", $data->aplayers);
    $list->r("mplayer", $serv->cfgRead("ark_MaxPlayers"));
    $list->r("version", $vs);
    $serv_list .= $list->load_var();
}



// Changelogs
$json   = $helper->remoteFileToJson($webserver['changelog'], 'changelog.json', 3600);

if (isset($json['file'])) {
    echo 'error error';
} else {
    $c      = 0;
    $list   = null;
    $now    = false;
    for ($i=count($json)-1;$i>-1;$i--) {
        $listtpl = new Template('changelog_list.htm', $tpl_dir);
        $listtpl->load();
        if ($version == $json[$i]['version']) $now = true;
        if ($now) {
            $color      = 'success';
            $colortxt   = '{::lang::php::home::old}';
        }
        if (!$now) {
            $color      = 'danger';
            $colortxt   = '{::lang::php::home::new}';
        }
        if ($version == $json[$i]['version']) {
            $color      = 'primary';
            $colortxt   = '{::lang::php::home::curr}';
        }
        if ($json[$i]['datestring'] == "--.--.----") {
            $color      = 'warning';
            $colortxt   = '{::lang::php::home::newWIP}';
        }

        $listtpl->r('lastupdate', converttime($json[$i]['updated'], false, true));
        $listtpl->r('git', $json[$i]['git']);
        $listtpl->r('download', $json[$i]['download']);
        $listtpl->rif ('ifgit', $json[$i]['git'] != " " && $json[$i]['git'] != null);
        $listtpl->rif ('ifdownload', $json[$i]['download'] != " " && $json[$i]['download'] != null);
        $listtpl->r('state_css', $color);
        $listtpl->r('state', $colortxt);
        $listtpl->r('date', $json[$i]['datestring']);
        $listtpl->r('version', $json[$i]['version']);
        $list .= $listtpl->load_var();
        if ($c == 15) break;
        $c++;
    }
}

$query = "SELECT * FROM ArkAdmin_statistiken WHERE server='server' ORDER BY `time` DESC";
$mycon->query($query);

$cpu_lable  = $cpu_data = $ram_lable = $ram_data = $mem_lable = $mem_data = array();
$last       = 0;
$first      = null;
$show_date  = false;
if($mycon->numRows() > 0) {
    $arr = $mycon->fetchAll();
    foreach ($arr as $item) {
        $string         = trim(utf8_encode($item["serverinfo_json"]));
        $string         = str_replace("\n", null, $string);

        // wandel Informationen in Array
        $infos          = json_decode($string, true);

        if($first == null) $first = $item["time"];
        if(($first - $item["time"]) > 86400) break;

        $date           = date("d.m - H:i", $item["time"]);
        $cpu_lable[]    = $ram_lable[] = $mem_lable[] = "'$date'";
        $cpu_data[]     = round($infos["cpu"], 2);
        $ram_data[]     = round($infos["ram"], 2);
        $mem_data[]     = round($infos["mem"], 2);

        $show_date      = false;
    }
}

// lade in TPL
$tpl->r('listchangelogs', $list);
$tpl->r('version', $version);
$tpl->r('serv_list', $serv_list);

$tpl->r('lable_ram', implode(",", $ram_lable));
$tpl->r('lable_cpu', implode(",", $cpu_lable));
$tpl->r('lable_mem', implode(",", $mem_lable));

$tpl->r('data_ram', implode(",", $ram_data));
$tpl->r('data_cpu', implode(",", $cpu_data));
$tpl->r('data_mem', implode(",", $mem_data));

$content = $tpl->load_var();
$pageicon = "<i class=\"fas fa-tachometer-alt\" aria-hidden=\"true\"></i>";