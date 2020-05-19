<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

file_put_contents("app/check/done", "true");

if (!file_exists("app/data/mods")) mkdir("app/data/mods");
if (!file_exists("app/json/saves")) mkdir("app/json/saves");
if (!file_exists("app/data/serv")) mkdir("app/data/serv");
if (!file_exists("app/data/config")) mkdir("app/data/config");
if (!file_exists("cache")) mkdir("cache");
if (!file_exists("sh")) mkdir("sh");
if (!file_exists("sh/resp")) mkdir("sh/resp");
if (!file_exists("sh/serv")) mkdir("sh/serv");
if (!file_exists("sh/main.sh")) file_put_contents("sh/main.sh", " ");

del_dir("install/sites");
unlink("install/sites");
del_dir("install");
unlink("install");
unlink("install.php");

header("Location: /login"); exit;

?>

