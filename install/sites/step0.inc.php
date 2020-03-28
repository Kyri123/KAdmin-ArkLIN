<?php
$sitetpl= new Template("step0.htm", $tpl_dir);
$sitetpl->load();
$complete = false;
$ok = false;

if(check_curl()) {
    $sitetpl->repl("curl_state_color", "success");
    $sitetpl->repl("curl_btn", "up");
    $sitetpl->repl("curl_state", "Gefunden");
    $curl = true;
}
else {
    $sitetpl->repl("curl_state_color", "danger");
    $sitetpl->repl("curl_btn", "down");
    $sitetpl->repl("curl_state", "Nicht gefunden");
    $curl = false;
}

if(check_rew()) {
    $sitetpl->repl("rew_state_color", "success");
    $sitetpl->repl("rew_btn", "up");
    $sitetpl->repl("rew_state", "Gefunden");
    $rew = true;
}
else {
    $sitetpl->repl("rew_state_color", "danger");
    $sitetpl->repl("rew_btn", "down");
    $sitetpl->repl("rew_state", "Nicht gefunden");
    $rew = false;
}

if(check_OS()) {
    $sitetpl->repl("linux_state_color", "success");
    $sitetpl->repl("linux_btn", "up");
    $sitetpl->repl("linux_state", "Gefunden");
    $os = true;
}
else {
    $sitetpl->repl("linux_state_color", "danger");
    $sitetpl->repl("linux_btn", "down");
    $sitetpl->repl("linux_state", "Nicht gefunden");
    $os = false;
}

if(check_arkmanager()) {
    $sitetpl->repl("am_state_color", "success");
    $sitetpl->repl("am_btn", "up");
    $sitetpl->repl("am_state", "Gefunden");
    $am = true;
}
else {
    $sitetpl->repl("am_state_color", "danger");
    $sitetpl->repl("am_btn", "down");
    $sitetpl->repl("am_state", "Nicht gefunden");
    $am = false;
}
if($am && $os && $curl && $rew) $ok = true;
$sitetpl->replif("ifallok", $ok);
$sitetpl->replif("ifcurl", $curl);
$sitetpl->replif("ifrewrite", $rew);
$sitetpl->replif("ifos", $os);
$sitetpl->replif("ifam", $am);

$title = "Schritt 1: Überprüfung";
$content = $sitetpl->loadin();

?>

