<?php
$resp = null; $a1 = null; $a2 = null; $a3 = null; $a4 = null;

if(isset($_SESSION["id"])) {
    $session_dropdownmenue = '
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Ausloggen
                </a>';
    $user_ui = '<div class="dropdown">
  <span class="ropdown-toggle" data-toggle="dropdown" style="color: rgba(255,255,255,.5);cursor:pointer;">
     '.getuserdata('username', $_SESSION["id"]).' <i class="fa fa-caret-down" aria-hidden="true"></i>
  </span>
  <div class="dropdown-menu">
    <a class="dropdown-item" href="/de/user/einstellungen">Einstellungen</a>
    <a class="dropdown-item" href="/de/logout">Ausloggen</a>
  </div>
</div>';
}
else {
    $session_username = "Gast";
    $session_dropdownmenue = '
                <a class="dropdown-item" href="/de/login">
                  <i class="fas fa-sign-in-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Einloggen
                </a>
                <a class="dropdown-item" href="/de/register">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Account erstellen
                </a>';
    $user_ui = '<div class="btn-group">
                  <a class="btn btn-info" style="color: #fff;" href="/de/login">Login</a>
                  <a class="btn btn-info" style="color: #fff;" href="/de/register">Registrieren</a>
                </div>';
}

// Einloggen
if(isset($_POST["login"]) && !isset($_SESSION["id"])) {
    $loggedin = filter_var($_POST["loggedin"], FILTER_VALIDATE_BOOLEAN);
    define('pw', $_POST["pw"]);
    
    // Count Username
    $query = 'SELECT * FROM `ArkAdmin_users` WHERE `username` = \''.$_POST["logger"].'\'';
    $mycon->query($query);
    $username_count = $mycon->numRows();
    
    // Count Email
    $query = 'SELECT * FROM `ArkAdmin_users` WHERE `email` = \''.$_POST["logger"].'\'';
    $mycon->query($query);
    $email_count = $mycon->numRows();
    
    if($username_count > 0 || $email_count > 0) {
        if($username_count > 0) {
            $query = 'SELECT * FROM `ArkAdmin_users` WHERE `username` = \''.$_POST["logger"].'\'';
        }
        elseif($email_count > 0) {
            $query = 'SELECT * FROM `ArkAdmin_users` WHERE `email` = \''.$_POST["logger"].'\'';
        }
        $row = $mycon->query($query)->fetchArray();
        if($row["password"] == md5(pw)) {
            if($row["ban"] < 1) {
                $userid = $row['id'];
                $_SESSION["id"] = $row['id'];

                if($loggedin) {
                    $md5_id = md5($userid);
                    $md5_rnd = md5(rndbit(100));
                    setcookie("id", $md5_id, time()+(525600*60*100));
                    setcookie("validate", $md5_rnd, time()+(525600*60*100));
                    $query = "INSERT INTO `ArkAdmin_user_cookies` (`md5id`, `validate`, `userid`) VALUES ('".$md5_id."', '".$md5_rnd."', '".$userid."')";
                    $mycon->query($query);
                }

                header('Location: /home');
                exit;
            }
            else {
                $resp = alert('danger', 'Account gebannt!', '15px 15px 0px 5px', 'Fehler!');
            }
        }
        else {
            $resp = alert('danger', 'Falsches Passwort!', '15px 15px 0px 5px', 'Fehler!');
        }
    }
    else {
        $resp = alert('danger', 'Benutzername oder E-Mail nicht vorhanden', '15px 15px 0px 5px', 'Fehler!');
    }
}

// Prüfe Cookies & logge ggf ein
if(isset($_COOKIE["id"]) && isset($_COOKIE["validate"]) && !isset($_SESSION["id"])) {
    $query = 'SELECT * FROM `ArkAdmin_user_cookies`';
    $mycon->query($query);
    if($mycon->numRows() > 0) {
        $array_vali = $mycon->fetchAll();
        for($i=0;$i<count($array_vali);$i++) {
            if($array_vali[$i]["md5id"] == $_COOKIE["id"] && $array_vali[$i]["validate"] == $_COOKIE["validate"]) {
                $_SESSION["id"] = $array_vali[$i]['userid'];
                header('Location: /home');
                exit;
            }
        }
    }
}

// Erstellen von einem Account
if(isset($_POST["register"]) && !isset($_SESSION["id"])) {
    define('username', $_POST["username"]);
    define('code', $_POST["code"]);
    define('email', $_POST["email"]);
    define('pw1', $_POST["pw1"]);
    define('pw2', $_POST["pw2"]);
    define('code', $_POST["code"]);
    $cont = 1;
    if(username == null) {
        $cont = 0;
    }
    if(code == null) {
        $cont = 0;
    }
    if(email == null) {
        $cont = 0;
    }
    if(pw1 == null) {
        $cont = 0;
    }
    if(pw2 == null) {
        $cont = 0;
    }
    if($cont == 1) {
        if(pw1 == pw2) {
            $query = 'SELECT * FROM `ArkAdmin_users` WHERE `username` = \''.username.'\'';
            $mycon->query($query);
            if($mycon->numRows() == 0) {
                $query = 'SELECT * FROM `ArkAdmin_users` WHERE `email` = \''.email.'\'';
                $mycon->query($query);
                if($mycon->numRows() == 0) {

                    $q_code = 'SELECT * FROM `ArkAdmin_reg_code` WHERE `used` = \'0\' AND `code` = \''.code.'\'';
                    if($mycon->query($q_code)->numRows() > 0) {
                        $row_code = $mycon->query($q_code)->fetchArray();
                        $query = 'INSERT INTO `ArkAdmin_users` (`username`, `email`, `password`, `rang`, `registerdate`) VALUES (\''.username.'\', \''.email.'\', \''.md5(pw1).'\', \'1\', \''.time().'\')';
                        $mycon->query($query);
                        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `email` = \''.email.'\'';
                        $row = $mycon->query($query)->fetchArray();
                        $_SESSION["id"] = $row['id'];
                        $_SESSION["rank"] = $row['rang'];
                        $query = 'UPDATE `ArkAdmin_reg_code` SET `used`=\'1\' WHERE (`id`=\''.$row_code["id"].'\')';
                        $mycon->query($query);
                        header('Location: /home');
                        exit;
                    }
                    else {
                        $resp = alert('danger', 'Falscher Code oder dieser wurde schon Benutzt!', '15px 15px 0px 5px', 'Fehler!');
                    }
                }
                else {
                    $resp = alert('danger', 'E-Mail ist bereits Verwendet!', '15px 15px 0px 5px', 'Fehler!');
                }
            }
            else {
                $resp = alert('danger', 'Benutzername ist bereits Verwendet!', '15px 15px 0px 5px', 'Fehler!');
            }
        }
        else {
            $resp = alert('danger', 'Passwörter stimmen nicht überein!', '15px 15px 0px 5px', 'Fehler!');
        }
    }
    else {
        $resp = alert('danger', 'Bitte fülle alle Felder aus!', '15px 15px 0px 5px', 'Fehler!');
    }
    
    
    
    $a1 = username;
    $a2 = code;
    $a3 = email;
    $a4 = code;
}


$tpl_register = new Template("register.htm", "tpl/session/");
$tpl_login = new Template("login.htm", "tpl/session/");
$tpl_register->load();
$tpl_login->load();

$tpl_register->repl('bg', '/dist/img/backgrounds/side.jpg');
$tpl_register->repl('meld', $resp);
$tpl_register->repl('1', $a1);
$tpl_register->repl('2', $a2);
$tpl_register->repl('3', $a3);
$tpl_register->repl('4', $a4);

$tpl_login->repl('bg', '/dist/img/backgrounds/side.jpg');
$tpl_login->repl('meld', $resp);

?>