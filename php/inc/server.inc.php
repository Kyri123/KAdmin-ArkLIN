<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$servers = null;
$count_serv_1 = 0;
$count_serv_max = 0;


$dir = $helper->fileToJson(__ADIR__."/app/json/serverinfo/all.json")["cfgs"];
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $servername = $dir[$i];
        $serv = new server($servername);
        $serv->clusterLoad();
        $count_serv_max++;
        $tpl_serv = new Template("item_list.htm", __ADIR__."/app/template/core/serv/");
        $tpl_serv->load();

        $status = convertstate($serv->stateCode());

        $servername = $serv->cfgRead('ark_SessionName');
        $subline = "{::lang::php::server::subline}";
        $map = $serv->cfgRead("serverMap");

        $l = strlen($servername); $lmax = 18;
        if ($l > $lmax) {
            $servername = substr($servername, 0 , $lmax) . " ...";
        }

        $tpl_serv->r('aplayers', $serv->status()->aplayers);
        $tpl_serv->r('ark_MaxPlayers', $serv->cfgRead('ark_MaxPlayers'));
        $tpl_serv->r('serv_version', $serv->status()->version);
        $tpl_serv->r('state', $status["color"]);
        $tpl_serv->r('serv_pid', null);
        $tpl_serv->rif ('ifin', $serv->clusterIn());
        $tpl_serv->r('clustername', $serv->clusterIn() ? $serv->clusterRead("name") : null);
        $tpl_serv->r("typestr", $serv->clusterIn() ? $clustertype[$serv->clusterRead("type")] : null);
        $tpl_serv->r('subline', $subline);
        $tpl_serv->r('servername', $servername);
        $tpl_serv->r('cfg', $dir[$i]);
        $servers .= $tpl_serv->load_var();
    }
}

$tpl_b->r('servers', $servers);
$tpl_b->r('count_serv_1', $count_serv_1);
$tpl_b->r('count_serv_max', $count_serv_max);
