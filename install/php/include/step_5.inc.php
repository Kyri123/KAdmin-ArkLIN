<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

file_put_contents(__ADIR__."/app/check/done", "true");

if (!file_exists(__ADIR__."/app/json/saves")) mkdir(__ADIR__."/app/json/saves");
if (!file_exists(__ADIR__."/app/data/serv")) mkdir(__ADIR__."/app/data/serv");
if (!file_exists(__ADIR__."/app/data/config")) mkdir(__ADIR__."/app/data/config");
if (!file_exists("cache")) mkdir("cache");
if (!file_exists("sh")) mkdir("sh");
if (!file_exists("sh/resp")) mkdir("sh/resp");
if (!file_exists("sh/serv")) mkdir("sh/serv");
if (!file_exists("sh/main.sh")) file_put_contents("sh/main.sh", " ");

del_dir(__ADIR__."install/sites");
unlink(__ADIR__."install/sites");
del_dir("install");
unlink("install");
unlink("install.php");

header("Location: /login"); exit;



