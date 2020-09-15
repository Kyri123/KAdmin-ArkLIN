<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$resp = null; $a1 = null; $a2 = null; $a3 = null; $a4 = null;

// Einloggen
if (isset($_POST["login"]) && !isset($_SESSION["id"])) {
    $loggedin = filter_var($_POST["loggedin"], FILTER_VALIDATE_BOOLEAN);
    define('pw', $_POST["pw"]);
    
    // Count Username
    $query = 'SELECT * FROM `ArkAdmin_users` WHERE `username` = \''.$_POST["logger"].'\' AND `password` = \''.md5(pw).'\'';
    $username_count = $mycon->query($query)->numRows();
    
    // Count Email
    $query = 'SELECT * FROM `ArkAdmin_users` WHERE `email` = \''.$_POST["logger"].'\' AND `password` = \''.md5(pw).'\'';
    $email_count = $mycon->query($query)->numRows();
    
    if ($username_count > 0 || $email_count > 0) {
        if ($username_count > 0) {
            $query = 'SELECT * FROM `ArkAdmin_users` WHERE `username` = \''.$_POST["logger"].'\' AND `password` = \''.md5(pw).'\'';
        }
        elseif ($email_count > 0) {
            $query = 'SELECT * FROM `ArkAdmin_users` WHERE `email` = \''.$_POST["logger"].'\' AND `password` = \''.md5(pw).'\'';
        }
        $row = $mycon->query($query)->fetchArray();
        if ($row["password"] == md5(pw)) {
            if ($row["ban"] < 1) {
                $userid = $row['id'];
                $_SESSION["id"] = $row['id'];

                if ($loggedin) {
                    $md5_id = md5($userid);
                    $md5_rnd = md5(rndbit(100));
                    setcookie("id", $md5_id, time()+(525600*60*100), "/");
                    setcookie("validate", $md5_rnd, time()+(525600*60*100), "/");
                    $query = "INSERT INTO `ArkAdmin_user_cookies` (`md5id`, `validate`, `userid`) VALUES ('".$md5_id."', '".$md5_rnd."', '".$userid."')";
                    $mycon->query($query);
                }

                header('Location: /home');
                exit;
            } else {
                $resp = $alert->rd(21, 3);
            }
        } else {
            $resp = $alert->rd(22, 3);
        }
    } else {
        $resp = $alert->rd(23, 3);
    }
}

// Prüfe Cookies & logge ggf ein
if (isset($_COOKIE["id"]) && isset($_COOKIE["validate"]) && !isset($_SESSION["id"])) {
    $query = 'SELECT * FROM `ArkAdmin_user_cookies`';
    $mycon->query($query);
    if ($mycon->numRows() > 0) {
        $array_vali = $mycon->fetchAll();
        for ($i=0;$i<count($array_vali);$i++) {
            if ($array_vali[$i]["md5id"] == $_COOKIE["id"] && $array_vali[$i]["validate"] == $_COOKIE["validate"]) {
                $_SESSION["id"] = $array_vali[$i]['userid'];
                header('Location: /home');
                exit;
            }
        }
    }
}

// Erstellen von einem Account
if (isset($_POST["register"]) && !isset($_SESSION["id"])) {
    // Definiere Vars
    define('username', $_POST["username"]);
    define('code', $_POST["code"]);
    define('email', $_POST["email"]);
    define('pw1', $_POST["pw1"]);
    define('pw2', $_POST["pw2"]);
    define('code', $_POST["code"]);
    $cont = 1;
    if (
            username == null ||
            code == null ||
            email == null ||
            pw1 == null ||
            pw2 == null
    ) $cont = 0;

    // wenn alle Felder ausgefüllt sind
    if ($cont == 1) {
        // Prüfe ob Passwörter übereinstimmen
        if (pw1 == pw2) {
            $query = 'SELECT * FROM `ArkAdmin_users` WHERE `username` = \''.username.'\'';
            $mycon->query($query);
            // schaue ob es den benutzer schon gibt
            if ($mycon->numRows() == 0) {
                $q_code = 'SELECT * FROM `ArkAdmin_reg_code` WHERE `used` = \'0\' AND `code` = \''.code.'\'';
                // Prüfe ob der Code benutzt werden darf
                if ($mycon->query($q_code)->numRows() > 0) {
                    $codeid = $mycon->fetchArray()["id"];
                    $row_code = $mycon->query($q_code)->fetchArray();
                    $query = 'INSERT INTO `ArkAdmin_users` (`username`, `email`, `password`, `rang`, `registerdate`) VALUES (\''.username.'\', \''.email.'\', \''.md5(pw1).'\', \'1\', \''.time().'\')';
                    // Wenn der Benutzer erstellt wurde
                    if($mycon->query($query)) {
                        $mycon->query("UPDATE `ArkAdmin_reg_code` SET `used` = '1' WHERE `id` = '$codeid'");

                        // schau ob code = admin ist (datensatz ist mit time benannt ja...)
                        if($row_code["time"] == "1") {
                            $userdata = $mycon->query('SELECT * FROM `ArkAdmin_users` WHERE `username` = \''.username.'\'')->fetchArray();
                            $permissions_default = $helper->file_to_json("app/json/user/permissions.tpl.json");
                            $permissions = (isset($userdata["id"]) && file_exists("app/json/user/".md5($userdata["id"]).".permissions.json")) ? $helper->file_to_json("app/json/user/".md5($userdata["id"]).".permissions.json") : $helper->file_to_json("app/json/user/permissions.tpl.json");
                            $permissions["all"]["is_admin"] = 1;
                            if(!file_exists("app/json/user/".md5($userdata["id"]).".permissions.json")) $helper->savejson_create($permissions, "app/json/user/".md5($userdata["id"]).".permissions.json");
                        }

                        $resp = $alert->rd(109, 3);
                    }
                    else {
                        $resp = $alert->rd(3, 3);
                    }
                }
                else {
                    $resp = $alert->rd(24, 3);
                }
            } else {
                $resp = $alert->rd(26, 3);
            }
        } else {
            $resp = $alert->rd(27, 3);
        }
    } else {
        $resp = $alert->rd(28, 3);
    }
    
    
    
    $a1 = username;
    $a2 = code;
    $a3 = email;
    $a4 = code;
}

$tpl_register = new Template("register.htm", "app/template/core/session/");
$tpl_login = new Template("login.htm", "app/template/core/session/");
$tpl_register->load();
$tpl_login->load();

$tpl_register->r('bg', '/app/dist/img/backgrounds/side.jpg');
$tpl_register->r('meld', $resp);
$tpl_register->r('1', $a1);
$tpl_register->r('2', $a2);
$tpl_register->r('3', $a3);
$tpl_register->r('4', $a4);

$tpl_login->r('bg', '/app/dist/img/backgrounds/side.jpg');
$tpl_login->r('meld', $resp); 

