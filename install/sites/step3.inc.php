<?php
$sitetpl= new Template("step3.htm", $tpl_dir);
$sitetpl->load();
$complete = false;
$ppath = "inc/custom_konfig.json";




$sitetpl->repl("error", $resp);
$title = "Schritt 4: Crontab & Regestrierung";
$content = $sitetpl->loadin();

?>

