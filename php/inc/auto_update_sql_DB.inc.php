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
?>