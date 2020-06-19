<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$file = 'app/json/serverinfo/all.json';
$json_all = json_decode(file_get_contents($file), true);
$json_all = null;
$on = 0;
$max = 0;
$z = 0;
$s = 0;
$dir = dirToArray('remote/arkmanager/instances/');
for ($i=0;$i<count($dir);$i++) {
    if ($dir[$i] = str_replace(".cfg", null, $dir[$i])) {
        $servdata = new server($dir[$i]);

        $data = parse_ini_file('remote/arkmanager/instances/'.$dir[$i].'.cfg');
        $json = $helper->file_to_json('app/json/serverinfo/'.$dir[$i].'.json', true);

        $json['online'] = filtersh($json['online']);
        $json_all['cfgs'][$s] = $servdata->name().'.cfg';

        $max++;
        $z++;
        if ($json['online'] == 'Yes') {
            $on++;
        }
        $path = 'app/json/saves/chat_'.$dir[$i].'.log';

        $array = file($path);
        $y = sizeof($array);
        $filestring = null;
        for ($z=0;$z<$y;$z++) {
            $string = json_encode($array[$z]);
            if (strpos($string, '(-\/-)') !== false) {
                $exp = explode('(-\/-)', $string);
                $string = $exp[1];
            }
            $string = str_replace(" ", null, $string);
            $string = str_replace("\n", null, $string);
            $string = str_replace('\n', null, $string);
            $string = str_replace('\u0000\u0000', null, $string);
            $string = str_replace("\"", null, $string);
            if ($string != null && $string != "") {
                $filestring .= $array[$z];
            }
        }
        file_put_contents($path, $filestring);
        $s++;
    }
}

$json_all['maxserv'] = $max;
$json_all['onserv'] = $on;
$json_all = json_encode($json_all, true);
if (file_put_contents($file, $json_all));
?>