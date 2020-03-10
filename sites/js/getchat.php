<?php
include('js_inz.inc.php');

$tpl = new Template('list_chat.htm', 'tpl/serv/sites/list/');
$tpl->load();
$serv = new server($_GET['cfg']);
$path = 'data/saves/chat_'.$serv->show_name().'.log';
$filearray = file($path);
$resp = null;
$z = count($filearray);
for($i=0;$i<count($filearray);$i++) {
    if($filearray[$z] != null) {
        $exp = explode('(-/-)', $filearray[$z]);
        $tpl = new Template('list_chat.htm', 'tpl/serv/sites/list/');
        $tpl->load();
        $tpl->repl('msg', $exp[1]);
        $tpl->repl('time', date('d.m.Y - H:m:s', intval($exp[0])));
        $tpl->repl('time', $exp[0]);
        $resp .= $tpl->loadin();
    }
    $z--;
}
if(empty($errorMSG)){
    echo json_encode(['code'=>200, 'msg'=>$resp]);
    exit;
}
echo json_encode(['code'=>404, 'msg'=>$errorMSG]);
?>
