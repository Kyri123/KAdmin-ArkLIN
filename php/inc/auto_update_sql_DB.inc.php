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
$query = "SHOW TABLES LIKE '".$table."'";
$query_file = "app/sql/cookie_login.sql";
$mycon->query($query);
if ($mycon->numRows() == 1) {
    null;
} else {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//check SQL for jobs
$table = "ArkAdmin_jobs";
$query = "SHOW TABLES LIKE '".$table."'";
$query_file = "app/sql/jobs.sql";
$mycon->query($query);
if ($mycon->numRows() == 1) {
    null;
} else {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

//check SQL for jobs
$table = "ArkAdmin_shell";
$query = "SHOW TABLES LIKE '".$table."'";
$query_file = "app/sql/shell.sql";
$mycon->query($query);
if ($mycon->numRows() == 1) {
    null;
} else {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}

// Sende Daten an Server
$array["dbhost"] = $dbhost;
$array["dbuser"] = $dbuser;
$array["dbpass"] = $dbpass;
$array["dbname"] = $dbname;
$helper->savejson_create($array, "arkadmin_server/config/mysql.json");
?>