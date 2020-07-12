<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$mainfile = null;
$dir = dirToArray('remote/arkmanager/instances/');
for ($i=0;$i<count($dir);$i++) {
    $server[$i]['cfg'] = $dir[$i];
    $re .= 'Read... ' . $dir[$i] . '<br />';
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {

        $check = $root_dir.'/sh/serv/check_server_ID_'.$dir[$i].".sh";
        $job = $root_dir.'/sh/serv/jobs_ID_'.$dir[$i].".sh";
        $sub_job = $root_dir.'/sh/serv/sub_jobs_ID_'.$dir[$i].".sh";

        $c_serv = new server($dir[$i]);
        
        $shell_path = 'sh/serv/check_server_ID_'.$c_serv->name().'.sh';
        // Shell Command
        $shell_command = 'echo "" > ' . $check . ' ;arkmanager status @'.$c_serv->name().' > '.$root_dir.'/sh/resp/'.$c_serv->name().'/status.log ;exit';

        if (!file_exists($shell_path)) file_put_contents($shell_path, null);
        $file_time = filemtime($shell_path);
        $diff = $time - $file_time;
        if ($diff > $timediff['shell']) {
            file_put_contents($shell_path, $shell_command);
        }

        $mainfile .= 'screen -d -m -t check_server_ID_'.$dir[$i]." sh $check\n";
        $mainfile .= 'screen -d -m -t jobs_ID_'.$dir[$i]." sh $job\n";
        $mainfile .= 'screen -d -m -t sub_jobs_ID_'.$dir[$i]." sh $sub_job\n";

    }
}
$mainfile .= 'exit';
//Schreibe main.sh
$mainfile = str_replace("\r", null, $mainfile);
echo $mainfile;
file_put_contents('sh/main.sh', $mainfile);

?>