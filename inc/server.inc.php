<?php
$servers = null;
$count_serv_1 = 0;
$count_serv_max = 0;


$dir = dirToArray('remote/arkmanager/instances/');
for($i=0;$i<count($dir);$i++) {
    if($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $serv = new server($dir[$i]);
        $serv->load_cluster();
        $count_serv_max++;
        $tpl_serv = new Template("item_list.htm", "tpl/serv/");
        $tpl_serv->load();

        $data = parse_ini_file('remote/arkmanager/instances/'.$dir[$i].'.cfg');
        $json = file_get_contents('data/serv/'.$dir[$i].'.json');
        $json = json_decode($json);

        $json->online = filtersh($json->online);

        $servername = $data['ark_SessionName'];
        $subline = $json->aplayers.' / '.$data['ark_MaxPlayers'].' Spieler | V.'.$json->version;
        $map = 'https://i.pinimg.com/originals/1d/01/30/1d01304174bbe64965d47559c61470cb.png';
        if($json->online == 'Yes') {
            $state = 'bg-success';
            $count_serv_1++;
        }
        elseif($json->online == 'NO') {
            $state = 'bg-danger';
        }
        else {
            $state = 'bg-warning';
        }
        $l = strlen($servername); $lmax = 18;
        if($l > $lmax) {
            $servername = substr($servername, 0 , $lmax) . " ...";
        }

        $tpl_serv->repl('map', $map);
        $tpl_serv->repl('state', $state);
        $tpl_serv->replif('ifin', $serv->cluster_in());
        $tpl_serv->repl('clustername', $serv->cluster_name());
        $tpl_serv->repl("typestr", $clustertype[$serv->cluster_type()]);
        $tpl_serv->repl('subline', $subline);
        $tpl_serv->repl('servername', $servername);
        $tpl_serv->repl('cfg', $dir[$i]);
        $tpl_serv->rplSession();
        $servers .= $tpl_serv->loadin();
    }
}

$tpl_b->repl('servers', $servers);
$tpl_b->repl('count_serv_1', $count_serv_1);
$tpl_b->repl('count_serv_max', $count_serv_max);
?>