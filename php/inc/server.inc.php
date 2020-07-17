<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$servers = null;
$count_serv_1 = 0;
$count_serv_max = 0;


$dir = dirToArray('remote/arkmanager/instances/');
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv = new server($dir[$i]);
        $serv->cluster_load();
        $count_serv_max++;
        $tpl_serv = new Template("item_list.htm", "app/template/core/serv/");
        $tpl_serv->load();

        $data = parse_ini_file('remote/arkmanager/instances/'.$dir[$i].'.cfg');
        $json = file_get_contents('app/json/serverinfo/'.$dir[$i].'.json');
        $json = json_decode($json, true);

        $json["online"] = (isset($json["online"])) ? filtersh($json["online"]) : "NO";

        $servername = $data['ark_SessionName'];
        $subline = "{::lang::php::server::subline}";
        $map = $serv->cfg_read("serverMap");
        if ($json["online"] == 'Yes') {
            $state = 'bg-success';
            $count_serv_1++;
        }
        elseif ($json["online"] == 'NO') {
            $state = 'bg-danger';
        } else {
            $state = 'bg-warning';
        }
        $l = strlen($servername); $lmax = 18;
        if ($l > $lmax) {
            $servername = substr($servername, 0 , $lmax) . " ...";
        }

        $tpl_serv->r('aplayers', $json["aplayers"]);
        $tpl_serv->r('ark_MaxPlayers', $data['ark_MaxPlayers']);
        $tpl_serv->r('serv_version', $json["version"]);
        $tpl_serv->r('state', $state);
        $tpl_serv->r('serv_pid', null);
        $tpl_serv->rif ('ifin', $serv->cluster_in());
        $tpl_serv->r('clustername', (($serv->cluster_in) ? $serv->cluster_name() : null));
        $tpl_serv->r("typestr", $clustertype[$serv->cluster_type()]);
        $tpl_serv->r('subline', $subline);
        $tpl_serv->r('servername', $servername);
        $tpl_serv->r('cfg', $dir[$i]);
        $tpl_serv->session();
        $servers .= $tpl_serv->load_var();
    }
}

$tpl_b->r('servers', $servers);
$tpl_b->r('count_serv_1', $count_serv_1);
$tpl_b->r('count_serv_max', $count_serv_max);
?>