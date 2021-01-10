<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

//check SQL
$tables = [];
$SQLs   = scandir(__ADIR__."/app/sql");
foreach ($SQLs as $FILE)
    if($FILE != "." && $FILE != ".." && strpos($FILE, "ArkAdmin_") !== false) {
        $FILE_NAME = pathinfo(__ADIR__."/app/sql/$FILE", PATHINFO_FILENAME);
        $tables[] = $FILE_NAME;
    }

foreach ($tables as $table)
    if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
        $query_file = file(__ADIR__."/app/sql/$table.sql");
        foreach ($query_file as $query) {
            $mycon->query($query);
        }
    }

$check_json["checked"] = true;
$helper->saveFile($check_json, __ADIR__."/app/data/sql_check.json");
//überschreibe alle user auf Admin
if($buildid == 210.53023) {
    $mycon->query("alter table `ArkAdmin_users` modify `rang` text null");
}