<?php
$url = $_SERVER["REQUEST_URI"];
$url = explode("/", $url);


if(isset($url[2]) && $url[2] == "checkthis") echo 1;
if($url[2] != "checkthis") {

    include("inc/class/Template.class.inc.php");
    include("inc/func/allg.func.inc.php");
    include("inc/class/mysql.class.inc.php");
    include("install/func.inc.php");
    include("inc/class/helper.class.inc.php");

    $helper = new helper();
    $tpl_dir = "install/";

    $tpl = new Template("main.htm", $tpl_dir);
    $tpl->load();

    $step = 0;
    if(isset($url[2])) $step = $url[2];

    for($i=0;$i<20;$i++) {
        if($step == $i) {
            include("install/sites/step".$i.".inc.php");
        }
    }

    $tpl->repl("stepid", ($step+1));
    $tpl->repl("title", $title);
    $tpl->repl("pagename", "Installer");
    $tpl->repl("time", time());
    $tpl->repl("content", $content);
    $tpl->display();

}
?>