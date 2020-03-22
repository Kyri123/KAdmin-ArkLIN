<?php
require('js_inz.inc.php');
$errorMSG = "";


/* cfg */

$type = $_POST["type"];
$text = $_POST["text"];
$cfg = $_POST["cfg"];

if(strpos($type, '.ini') !== false) {
    $server = parse_ini_file('../../remote/arkmanager/instances/'.$cfg.'.cfg');

    $dir = $server['arkserverroot'];
    $dir = str_replace('/data/ark_serv_dir/', 'remote/serv/', $dir);
    $dir = '../../'.$dir.'/ShooterGame/Saved/Config/LinuxServer/'.$type;
}

if(file_put_contents($dir, ini_save_rdy($text))) {} else {$errorMSG = meld('danger mb-4', 'Irgendetwas ist Schief gelaufe ... Code: <b>#0001</b>', 'Error', 'fas fa-exclamation-circle');}


if(empty($errorMSG)){
    $msg = meld('success mb-4', $type.' wurde gespeichert!', 'Gespeichert', 'fas fa-check', 'fas fa-exclamation-circle');
    echo json_encode(['code'=>200, 'msg'=>$msg]);
    exit;
}
echo json_encode(['code'=>404, 'msg'=>$errorMSG]);


?>