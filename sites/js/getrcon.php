<?php
include('js_inz.inc.php');

$tpl = new Template('list_chat.htm', 'tpl/serv/sites/list/');
$tpl->load();
$serv = new server($_GET['cfg']);
$path = 'data/saves/rconlog_'.$serv->show_name().'.txt';
$filearray = file($path);
$resp = null;
$z = count($filearray);
$ib = 0;
for($i=0;$i<count($filearray);$i++) {
    if($filearray[$z] != null) {
        $exp = explode('(-/-)', $filearray[$z]);
        $tpl = new Template('list_chat.htm', 'tpl/serv/sites/list/');
        $tpl->load();
        $tpl->repl('msg', $exp[1]);
        $tpl->repl('time', date('d.m.Y - H:i:s', $exp[0]));
        $tpl->repl('i', $ib);
        $resp .= $tpl->loadin();
    }
    $z--;
    $ib++;
    if($ib>99) break;
}
if(empty($errorMSG)){
    echo json_encode(['code'=>200, 'msg'=>$resp]);
    exit;
}
echo json_encode(['code'=>404, 'msg'=>$errorMSG]);
?>
