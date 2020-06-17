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
$sitetpl= new Template("step1.htm", $tpl_dir);
$sitetpl->load();
$complete = false;
$resp = $alert->rd(1);

if (isset($_POST["send"])) {
    $dbhost = $_POST["host"];
    $dbuser = $_POST["user"];
    $dbpass = $_POST["pw"];
    $dbname = $_POST["base"];
    error_reporting(0);
    $mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);
    if ($mycon->is) {
        $sql = file("app/sql/sql.sql");
        foreach ($sql as $query) {
            $mycon->query($query);
        }
        $mycon->close();
        $str = "<?php
\$dbhost = '".$dbhost."';
\$dbuser = '".$dbuser."';
\$dbpass = '".$dbpass."';
\$dbname = '".$dbname."';
?>";
        if (file_put_contents("php/inc/pconfig.inc.php", $str)) {
            $resp = $alert->rd(100);
            header("Location: /install.php/2");
            exit;
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

?>

