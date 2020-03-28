<?php

file_put_contents("data/done", "true");

if(!file_exists("data/mods")) mkdir("data/mods");
if(!file_exists("data/saves")) mkdir("data/saves");
if(!file_exists("data/serv")) mkdir("data/serv");
if(!file_exists("data/config")) mkdir("data/config");
if(!file_exists("cache")) mkdir("cache");
if(!file_exists("sh")) mkdir("sh");
if(!file_exists("sh/resp")) mkdir("sh/resp");
if(!file_exists("sh/serv")) mkdir("sh/serv");
if(!file_exists("sh/main.sh")) file_put_contents("sh/main.sh", " ");

del_dir("install/sites");
unlink("install/sites");
del_dir("install");
unlink("install");
unlink("install.php");

header("Location: /login"); exit;

?>

