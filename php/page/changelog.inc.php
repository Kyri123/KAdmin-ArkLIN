<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

//Changelog_function
function changelog($str) {
    $str = str_replace("[t]", '<i class="fas fa-check"></i>', $str);
    $str = str_replace("[x]", '<i class="fas fa-times"></i>', $str);
    $str = str_replace("[n]", 'style="list-style-type: none;"', $str);
    return $str;
}


// Vars
$tpl_dir = 'app/template/core/changelog/';
$tpl_dir_all = 'app/template/all/';
$setsidebar = false;
$pagename = 'Changelogs';
$urltop = '<li class="breadcrumb-item">Changelogs</li>';

$name = array(
    "<strong>Intern",
    "<strong>Cluster System",
    "<strong>ServerCenter",
    "<strong>ServerControllCenter",
    "<strong>Server Controll Center",
    "<strong>Traffic",
    "<strong>Darstellungen",
    "<strong>Style",
    "<strong>Login",
    "<strong>Background Updater",
    "<strong>Allgemein",
    "<strong>Changelog",
    "<strong>SteamAPI",
    "<strong>Dashboard",
    "<strong>Install",
    "<strong>Benutzereinstellungen",
    "<strong>Benutzer",
    "<strong>Account",
    "<strong>Konfiguration",
    "<strong>Crontab",
    "<strong>Sprachsystem",
    "<strong>Server",
    "<strong>Updater",
    "<strong>Webserver",
    "<strong>Crontab",
    "<strong>Shell",
    "<strong>Statusabfrage & Statistiken",
    "<strong>Logs",
    "<strong>API"
);
$withicon = array(
    "<strong><i class='fas fa-code'></i> Intern",
    "<strong><i class='nav-icon fas fa-random'></i> Cluster System",
    "<strong><i class='fas fa-server'></i> ServerCenter",
    "<strong><i class='fas fa-server'></i> ServerControllCenter",
    "<strong><i class='fas fa-server'></i> ServerControllCenter",
    "<strong><i class='fas fa-tachometer-alt'></i> Traffic",
    "<strong><i class=\"fas fa-tv\"></i> Darstellungen",
    "<strong><i class=\"fas fa-tv\"></i> Style",
    "<strong><i class='fas fa-sign-in-alt'></i> Login",
    "<strong><i class=\"fas fa-arrow-circle-up\"></i> Background Updater",
    "<strong><i class='fas fa-code'></i> Allgemein",
    "<strong><i class=\"fas fa-clipboard-list\"></i> Changelog",
    "<strong><i class='fas fa-code'></i> SteamAPI",
    "<strong><i class='fas fa-tachometer-alt'></i> Dashboard",
    "<strong><i class=\"fas fa-download\"></i> Install",
    "<strong><i class=\"fas fa-cogs\"></i> Benutzereinstellungen",
    "<strong><i class=\"fas fa-users\"></i> Benutzer",
    "<strong><i class=\"fas fa-user\"></i> Account",
    "<strong><i class=\"far fa-file-alt\"></i> Konfiguration",
    "<strong><i class=\"fas fa-tasks\"></i> Crontab",
    "<strong><i class=\"fas fa-language\"></i> Sprachsystem",
    "<strong><i class=\"fas fa-server\"></i> Server",
    "<strong><i class=\"fa fa-refresh\"></i> Updater",
    "<strong><i class=\"fa fa-server\"></i> Webserver",
    "<strong><i class=\"fas fa-terminal\"></i> Shell",
    "<strong><i class=\"fas fa-chart-bar\"></i> Statusabfrage & Statistiken",
    "<strong><i class=\"fas fa-list\"></i> Logs",
    "<strong><i class=\"fas fa-sitemap\"></i> API",
    "<strong><i class=\"fas fa-sitemap\"></i> API"
);

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();


$json = $helper->remotefile_to_json($webserver['changelog'], 'changelog.json', 3600);

if (isset($json['file'])) {
    echo 'error error';
} else {
    $list = null;
    $now = false;
    for ($i=count($json)-1;$i>-1;$i--) {
        $listtpl = new Template('list.htm', $tpl_dir);
        $listtpl->load();
        $vsint = intval(str_replace(".", null, $json[$i]['version']));


        if ($now) $color = 'bg-green';
        if (!$now) $color = 'bg-danger';
        if ($version == $json[$i]['version']) $color = 'bg-primary';
        if ($version == $json[$i]['version']) $now = true;
        if ($json[$i]['datestring'] == "--.--.----") $color = 'bg-warning';
        $listtpl->r('color', $color);

        // fix
        if ($json[$i]['fix'] == "") {
            $listtpl->rif ('iffix', false);
        } else {
            $newstring = null;
            $listtpl->rif ('iffix', true);
            $bits = explode("\r", $json[$i]['fix']);
            foreach($bits as $bit)
            {
                $newstring .= str_replace($name, $withicon, changelog($bit));
            }
            $listtpl->r('fix', $newstring);
        }

        // neu
        if ($json[$i]['new'] == "") {
            $listtpl->rif ('ifnew', false);
        } else {
            $newstring = null;
            $listtpl->rif ('ifnew', true);
            $bits = explode("\r", $json[$i]['new']);
            foreach($bits as $bit)
            {
                $newstring .= str_replace($name, $withicon, changelog($bit));
            }
            $listtpl->r('new', $newstring);
        }

        // change
        if ($json[$i]['change'] == "") {
            $listtpl->rif ('ifchange', false);
        } else {
            $newstring = null;
            $listtpl->rif ('ifchange', true);
            $bits = explode("\r", $json[$i]['change']);
            foreach($bits as $bit)
            {
                $newstring .= str_replace($name, $withicon, changelog($bit));
            }
            $listtpl->r('change', $newstring);
        }


        // java
        if ($json[$i]['java'] == "") {
            $listtpl->rif ('ifjava', false);
        } else {
            $newstring = null;
            $listtpl->rif ('ifjava', true);
            $bits = explode("\r", $json[$i]['java']);
            foreach($bits as $bit)
            {
                $newstring .= str_replace($name, $withicon, changelog($bit));
            }
            $listtpl->r('java', $newstring);
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
        $listtpl->r('datestring', $json[$i]['datestring']);
        $listtpl->r('version', $json[$i]['version']);
        $listtpl->rif("style2", $vsint >= 120);
        $listtpl->rif("style1", $vsint <= 119);
        $list .= $listtpl->load_var();
    }
}



// lade in TPL
$tpl->r('list', $list);
$content = $tpl->load_var();
$pageicon = "<i class=\"fa fa-book\" aria-hidden=\"true\"></i>";
$site_name = 'Changelogs';
