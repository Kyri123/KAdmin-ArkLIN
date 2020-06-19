<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$dir = dirToArray("php/page");
foreach ($dir as $k => $v) {
    if (!is_array($v)) {
        $sitename = str_replace(".inc.php", null, $v);
        $visit = "aa_nav_hover";
        if ($sitename == $page) $visit = "aa_main_active aa_nav_hover";
        $tpl_b->r("_$sitename", $visit);
    }
}

$json = $helper->remotefile_to_json($webserver['changelog'], 'changelog.json');
$c = true;
for ($i=count($json)-1;$i>-1;$i--) {
    if ($version == $json[$i]['version']) {
        $cc = $i;
        break;
    }
}

$n_changelog = '<span class="badge badge-secondary">Neu!</span>';
for ($i=count($json)-1;$i>-1;$i--) {
    if ($cc >= $i) {
        $n_changelog = null;
        break;
    }
    elseif ($json[$i]['datestring'] == "--.--.----") {

    } else {
        if ($version == $json[$i]['version']) {
            $n_changelog = null;
            break;
        }
        elseif ($version != $json[$i]['version']) {
            $n_changelog = '<span class="badge badge-success">Neu!</span>';
            break;
        } else {
            break;
        }
        if ($version != $json[$i]['version']) $n_changelog = '<span class="badge badge-success">{::lang::php::c::newchangelog}</span>';
    }
}



$tpl_b->r('curr_changelog', $n_changelog);




// Speicher Sprache in einer user.json
if(isset($_SESSION["id"]) && isset($_COOKIE["lang"])) {
    $json = null;
    $uid = md5($_SESSION["id"]);
    $path = "app/json/user/$uid.json";
    // speichern wenn die json existiert
    if(file_exists($path)) {
        $json = $helper->file_to_json($path);
        // prüfe ob die json gleich dem Cookies entspricht
        if($json["lang"] != $_COOKIE["lang"]) {
            $json["lang"] = $_COOKIE["lang"];
            $helper->savejson_exsists($json, $path);
        }
    // speichern wenn die json NICHT existiert
    } else {
    // speichern wenn die json existiert
        $json["lang"] = $_COOKIE["lang"];
        $helper->savejson_create($json, $path);
    }
}





?>

