<?php
require('js_inz.inc.php');


$errorMSG = "";


/* cfg */
$cfg = $_POST["cfg"];
$serv = new server($cfg);
$para = $_POST["para"];
if($para == null) $para[0] = null;
$paraend = null;
/* action */
if (empty($_POST["action"])) {
    $errorMSG = meld('danger', 'keine Action!', 'Error', 'fas fa-exclamation-circle');
} else {
    $action = $_POST["action"];
}
if(count($para) > 1) {
    for($i=0;$i<count($para);$i++) {
        $paraend .= ' '.$para[$i];
    }
    $paraend .= ' ';
}
elseif(count($para) <1) {
    $paraend = null;
}
else {
    $paraend = ' '.$para[0].' ';
}


$server = new server($cfg);
$json = json_decode(file_get_contents('data/serv/'.$cfg.'.json'));
if($json->next == 'TRUE') {
    $errorMSG = meld('danger', 'Server ist derzeit für Aktionen gesperrt!', 'Error', 'fas fa-exclamation-circle');
}
else {
    if(!$serv->set_action($action.$paraend)) $errorMSG = meld('danger', 'Etwas ist Schief gelaufen!', 'Error', 'fas fa-exclamation-circle');
}



if(empty($errorMSG)){
    $msg = meld('success', $action.$paraend.' @'.$cfg, 'Erfolgreich', 'fas fa-check');
    echo json_encode(['code'=>200, 'msg'=>$msg]);
    $json->next = 'TRUE';
    $json = json_encode($json);
    file_put_contents('data/serv/'.$cfg.'.json', $json);
    exit;
}
echo json_encode(['code'=>404, 'msg'=>$errorMSG]);


?>