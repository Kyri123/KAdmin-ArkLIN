<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

if (isset($_COOKIE["id"]) && isset($_COOKIE["validate"])) {
    $query = "DELETE FROM `ArkAdmin_user_cookies` WHERE (`validate`='".$_COOKIE["validate"]."')";
    $mycon->query($query);
    setcookie("id", "", time() - 3600);
    setcookie("validate", "", time() - 3600);
}

session_destroy();
//header('Location: /de/home');
//exit;
