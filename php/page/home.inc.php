<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Vars
$tpl_dir = 'app/template/core/home/';
$setsidebar = false;
$pagename = "{::lang::php::home::pagename}";
$urltop = "<li class=\"breadcrumb-item\">$pagename</li>";

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

//lasten


// Server
$all = $helper->file_to_json("app/json/serverinfo/all.json");
$a_cfg = $all["cfgs"];
$count_server = count($a_cfg);

$serv_list = null;
foreach ($a_cfg as $key => $value) {
    $value = str_replace(".cfg", null, $value);
    $serv = new server($value);

    $list = new Template('server_list.htm', $tpl_dir);
    $list->load();

    $state_code = $serv->statecode();
    $converted = convertstate($state_code);
    $data = $serv->status();

    $name = $serv->cfg_read("ark_SessionName");
    $cfg = $serv->name();

    $vs = null;
    if ($data->version != null) $vs = " - V.".$data->version;

    $map_path = "app/dist/img/igmap/".$serv->cfg_read("serverMap").".jpg";
    if (!file_exists($map_path)) $map_path = "app/dist/img/igmap/ark.png";

    $l = strlen($name); $lmax = 25;
    if ($l > $lmax) {
        $name = substr($name, 0 , $lmax) . " ...";
    }

    $list->r("img", $map_path);
    $list->r("name", $name);
    $list->r("cfg", $cfg);
    $list->r("color", $converted["color"]);
    $list->r("state_str", $converted["str"]);
    $list->r("aplayer", $data->aplayers);
    $list->r("mplayer", $serv->cfg_read("ark_MaxPlayers"));
    $list->r("version", $vs);
    $serv_list .= $list->load_var();
}



// Changelogs
$json = $helper->remotefile_to_json($webserver['changelog'], 'changelog.json', 3600);

if (isset($json['file'])) {
    echo 'error error';
} else {
    $c = 0;
    $list = null;
    $now = false;
    for ($i=count($json)-1;$i>-1;$i--) {
        $listtpl = new Template('changelog_list.htm', $tpl_dir);
        $listtpl->load();
        if ($version == $json[$i]['version']) $now = true;
        if ($now) {
            $color = 'success';
            $colortxt = '{::lang::php::home::old}';
        }
        if (!$now) {
            $color = 'danger';
            $colortxt = '{::lang::php::home::new}';
        }
        if ($version == $json[$i]['version']) {
            $color = 'primary';
            $colortxt = '{::lang::php::home::curr}';
        }
        if ($json[$i]['datestring'] == "--.--.----") {
            $color = 'warning';
            $colortxt = '{::lang::php::home::newWIP}';
        }
        $git = false;
        if ($json[$i]['git'] != " " && $json[$i]['git'] != null) $git = true;
        $download = false;
        if ($json[$i]['download'] != " " && $json[$i]['download'] != null) $download = true;
        $listtpl->r('lastupdate', converttime($json[$i]['updated'], false, true));
        $listtpl->r('git', $json[$i]['git']);
        $listtpl->r('download', $json[$i]['download']);
        $listtpl->rif ('ifgit', $git);
        $listtpl->rif ('ifdownload', $download);
        $listtpl->r('state_css', $color);
        $listtpl->r('state', $colortxt);
        $listtpl->r('date', $json[$i]['datestring']);
        $listtpl->r('version', $json[$i]['version']);
        $list .= $listtpl->load_var();
        if ($c == 9) break;
        $c++;
    }
}

// lade in TPL
$tpl->r('listchangelogs', $list);
$tpl->r('version', $version);
$tpl->r('serv_list', $serv_list);

$content = $tpl->load_var();
$pageicon = "<i class=\"fas fa-tachometer-alt\" aria-hidden=\"true\"></i>";
