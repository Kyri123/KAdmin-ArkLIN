<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

//check SQL
$tables = [];
$SQLs = scandir(__ADIR__."/app/sql");
foreach ($SQLs as $FILE) {
    if($FILE != "." && $FILE != ".." && strpos($FILE, "ArkAdmin_")) {
        $FILE_NAME = pathinfo(__ADIR__."/app/sql/$FILE", PATHINFO_FILENAME);
        $tables[] = $FILE_NAME;
    }
}

foreach ($tables as $table) {
    if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
        $query_file = file(__ADIR__."/app/sql/$table.sql");
        foreach ($query_file as $query) {
            $mycon->query($query);
        }
    }
}

//überschreibe alle user auf Admin
if($version == "2.0.0" && $buildid == 200.000) {
    $mycon->query("alter table `ArkAdmin_users` modify `rang` text null");
    $USERS = "SELECT * FROM `ArkAdmin_users`";
    foreach ($mycon->query($USERS)->fetchAll() as $USER) {
        $ID = $USER["id"];
        $FILE = __ADIR__."/app/json/user/".md5($ID).".permissions.json";

        if(file_exists($FILE)) {
            $json = json_decode(file_get_contents($FILE), true);
            if(isset($json["all"]["is_admin"]) && $json["all"]["is_admin"] == "1") {
                $query = 'UPDATE `ArkAdmin_users` SET `rang`=\'[1]\'  WHERE `id` = \''.$ID.'\'';
                $mycon->query($query);
            }
            else {
                $QUERY = "INSERT INTO `ArkAdmin_user_group` (`name`, `editform`, `time`, `permissions`, `canadd`) VALUES ('".$USER["username"]."', 1, 0, '".file_get_contents($FILE)."', '[]')";
                if($mycon->query($QUERY)) {
                    $QUERY = "SELECT * FROM `ArkAdmin_user_group` WHERE `name`='".$USER["username"]."'";
                    $groupid = $mycon->query($QUERY)->fetchArray()["id"];
                    $query = 'UPDATE `ArkAdmin_users` SET `rang`=\'['.$groupid.']\'  WHERE `id` = \''.$ID.'\'';
                    $mycon->query($query);
                }
            }
            //unlink($FILE);
        }
        else {
            $query = 'UPDATE `ArkAdmin_users` SET `rang`=\'[]\'  WHERE `id` = \''.$ID.'\'';
            $mycon->query($query);
        }
    }
}

$check_json["checked"] = true;
$helper->savejson_create($check_json, __ADIR__."/app/data/sql_check.json");