<?php
//check SQL for Cookie_user
$table = "ArkAdmin_user_cookies";
$query = "SHOW TABLES LIKE '".$table."'";
$query_file = "data/update_sql/cookie_login.sql";
$mycon->query($query);
if ($mycon->numRows() == 1) {
    null;
}
else {
    $query_file = file($query_file);
    foreach ($query_file as $query) {
        $mycon->query($query);
    }
}
?>