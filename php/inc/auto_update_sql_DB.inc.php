<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

//check SQL for Cookie_user
$table = "ArkAdmin_user_cookies";
$query_file = "app/sql/cookie_login.sql";
if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//check SQL for jobs
$table = "ArkAdmin_jobs";
$query_file = "app/sql/jobs.sql";
if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//check SQL for statistiken
$table = "ArkAdmin_statistiken";
$query_file = "app/sql/statistiken.sql";
if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//check SQL for jobs
$table = "ArkAdmin_shell";
$query_file = "app/sql/shell.sql";
if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//check SQL for players
$table = "ArkAdmin_players";
$query_file = "app/sql/players.sql";
if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//check SQL for tribes
$table = "ArkAdmin_tribe";
$query_file = "app/sql/tribe.sql";
if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//überschreibe alle user auf Admin
$path = "app/json/user";
$dir_arr = scandir($path);
$query = "SELECT * FROM `ArkAdmin_users`";

if($query = $mycon->query($query)) {
    $arr = $query->fetchAll();
    foreach ($arr as $item) {
        $permissions_default = $helper->file_to_json("app/json/user/permissions.tpl.json");
        $permissions_default["all"]["is_admin"] = 1;
        if(!file_exists("app/json/user/".md5($item["id"]).".permissions.json")) $helper->savejson_create($permissions_default, "app/json/user/".md5($item["id"]).".permissions.json");
    }
}

// Sende Daten an Server
$array["dbhost"] = $dbhost;
$array["dbuser"] = $dbuser;
$array["dbpass"] = $dbpass;
$array["dbname"] = $dbname;
$helper->savejson_create($array, "arkadmin_server/config/mysql.json");

$check_json["checked"] = true;
$helper->savejson_create($check_json, "app/data/sql_check.json");
