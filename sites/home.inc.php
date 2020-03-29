<?php

// Vars
$tpl_dir = 'tpl/home/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$pagename = "Dashboard";
$urltop = '<li class="breadcrumb-item">Dashboard</li>';

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

//lasten


// Server
$all = $helper->file_to_json("data/serv/all.json");
$a_cfg = $all["cfgs"];
$count_server = count($a_cfg);

$serv_list = null;
foreach ($a_cfg as $key => $value) {
    $value = str_replace(".cfg", null, $value);
    $serv = new server($value);

    $list = new Template('server_list.htm', $tpl_dir);
    $list->load();

    $state_code = $serv->get_state();
    if($state_code == 0) {
        $color = "danger";
    }
    elseif($state_code == 1) {
        $color = "info";
    }
    elseif($state_code == 2) {
        $color = "success";
    }
    else {
        $color = "warning";
    }
    $data = $serv->readdata();

    $name = $serv->cfg_read("ark_SessionName");
    $cfg = $serv->show_name();

    $vs = null;
    if($data->version != null) $vs = " - V.".$data->version;

    $map_path = "dist/img/igmap/".$serv->cfg_read("serverMap").".jpg";
    if(!file_exists($map_path)) $map_path = "dist/img/igmap/ark.png";

    $l = strlen($name); $lmax = 25;
    if($l > $lmax) {
        $name = substr($name, 0 , $lmax) . " ...";
    }

    $list->repl("img", $map_path);
    $list->repl("name", $name);
    $list->repl("cfg", $cfg);
    $list->repl("state", $color);
    $list->repl("aplayer", $data->aplayers);
    $list->repl("mplayer", $serv->cfg_read("ark_MaxPlayers"));
    $list->repl("version", $vs);
    $serv_list .= $list->loadin();
}



// Changelogs
$json = $helper->remotefile_to_json($webserver['changelog'], 'changelog.json', 300);

if(isset($json['file'])) {
    echo 'error 404';
}
else {
    $c = 0;
    $list = null;
    $now = false;
    for($i=count($json)-1;$i>-1;$i--) {
        $listtpl = new Template('changelog_list.htm', $tpl_dir);
        $listtpl->load();
        if($version == $json[$i]['version']) $now = true;
        if($now) {
            $color = 'success';
            $colortxt = 'Veraltet';
        }
        if(!$now) {
            $color = 'danger';
            $colortxt = 'Neuer';
        }
        if($version == $json[$i]['version']) {
            $color = 'primary';
            $colortxt = 'Aktuell';
        }
        if($json[$i]['datestring'] == "--.--.----") {
            $color = 'warning';
            $colortxt = 'Neuer (WIP)';
        }
        $git = false;
        if($json[$i]['git'] != " " && $json[$i]['git'] != null) $git = true;
        $download = false;
        if($json[$i]['download'] != " " && $json[$i]['download'] != null) $download = true;
        $listtpl->repl('git', $json[$i]['git']);
        $listtpl->repl('download', $json[$i]['download']);
        $listtpl->replif('ifgit', $git);
        $listtpl->replif('ifdownload', $download);
        $listtpl->repl('state_css', $color);
        $listtpl->repl('state', $colortxt);
        $listtpl->repl('date', $json[$i]['datestring']);
        $listtpl->repl('version', $json[$i]['version']);
        $list .= $listtpl->loadin();
        if($c == 9) break;
        $c++;
    }
}

// lade in TPL
$tpl->repl('listchangelogs', $list);
$tpl->repl('version', $version);
$tpl->repl('count_server', $count_server);
$tpl->repl('serv_list', $serv_list);
$tpl->repl('cpu_perc', cpu_perc());
$tpl->repl('free', bitrechner(disk_free_space ( "remote/serv/" )));
$tpl->repl('ram_perc', mem_perc());

$content = $tpl->loadin();
$H_btn_group = null;
$H_btn_extra = null;
$site_name = 'Startseite / Willkommen';
?>