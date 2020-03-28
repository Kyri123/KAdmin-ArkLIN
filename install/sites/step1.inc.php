<?php
$sitetpl= new Template("step1.htm", $tpl_dir);
$sitetpl->load();
$complete = false;

if(isset($_POST["send"])) {
    $dbhost = $_POST["host"];
    $dbuser = $_POST["user"];
    $dbpass = $_POST["pw"];
    $dbname = $_POST["base"];
    error_reporting(0);
    $mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);
    if($mycon->is) {
        $sql = file("data/sql.sql");
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
        if(file_put_contents("inc/pconfig.inc.php", $str)) {
            header("Location: /install.php/2");
            exit;
        }
    }
    else {
        $resp = meld('danger', 'Konnte keine Verbindung herstellen.', 'Fehler!', null);
    }
}

$sitetpl->repl("error", $resp);
$title = "Schritt 2: MySQL";
$content = $sitetpl->loadin();

?>

