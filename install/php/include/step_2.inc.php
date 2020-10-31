<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/
$resp = null;
$sitetpl= new Template("step2.htm", $dirs["tpl"]);
$sitetpl->load();
$complete = false;
$resp = null;

if (isset($_POST["send"])) {
    $dbhost = $_POST["host"];
    $dbuser = $_POST["user"];
    $dbpass = $_POST["pw"];
    $dbname = $_POST["base"];
    error_reporting(0);
    $mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);
    if ($mycon->is) {
//check SQL
        $tables = [];
        $SQLs = scandir(__ADIR__."/app/sql");
        foreach ($SQLs as $table) {
            if(strpos($table, "ArkAdmin_")) {
                if ($mycon->query("SHOW TABLES LIKE '$table'")->numRows() == 0) {
                    $query_file = file(__ADIR__."/app/sql/$table.sql");
                    foreach ($query_file as $query) {
                        $mycon->query($query);
                    }
                }
            }
        }

        $mycon->close();
        $str = "<?php
                    \$dbhost = '$dbhost';
                    \$dbuser = '$dbuser';
                    \$dbpass = '$dbpass';
                    \$dbname = '$dbname';
                ?>";
        if (file_put_contents(__ADIR__."/php/inc/pconfig.inc.php", $str)) {
            $array["dbhost"] = $dbhost;
            $array["dbuser"] = $dbuser;
            $array["dbpass"] = $dbpass;
            $array["dbname"] = $dbname;
            if ($helper->savejson_create($array, __ADIR__."/arkadmin_server/config/mysql.json")) {
                header("Location: $ROOT/install.php/3");
                exit;
            }
            else {
                $resp = $alert->rd(1);
            }
        }
        else {
            $resp = $alert->rd(1);
        }
    } else {
        $resp = $alert->rd(29);
    }
}

$sitetpl->r("error", $resp);
$title = "{::lang::install::step1::title}";
$content = $sitetpl->load_var();



