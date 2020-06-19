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
        $c_serv = new server($dir[$i]);
        
        $shell_path = 'sh/serv/check_server_ID_'.$c_serv->name().'.sh';
        // Shell Command
        $shell_command = 'echo "" > '.$root_dir.'/sh/serv/check_server_ID_'.$dir[$i].'.sh ;arkmanager status @'.$c_serv->name().' > '.$root_dir.'/sh/resp/'.$c_serv->name().'/status.log ;exit';

        if (!file_exists($shell_path)) file_put_contents($shell_path, null);
        $file_time = filemtime($shell_path);
        $diff = $time - $file_time;
        if ($diff > $timediff['shell']) {
            file_put_contents($shell_path, $shell_command);
        }

        $mainfile .= 'screen -d -m -t check_server_ID_'.$dir[$i].' sh '.$root_dir.'/sh/serv/check_server_ID_'.$dir[$i].".sh\n";
        $mainfile .= 'screen -d -m -t jobs_ID_'.$dir[$i].' sh '.$root_dir.'/sh/serv/jobs_ID_'.$dir[$i].".sh\n";
        $mainfile .= 'screen -d -m -t sub_jobs_ID_'.$dir[$i].' sh '.$root_dir.'/sh/serv/sub_jobs_ID_'.$dir[$i].".sh\n";

    }
}
//Schreibe main.sh
$mainfile = str_replace("\r", null, $mainfile);
file_put_contents('sh/main.sh', $mainfile);

?>