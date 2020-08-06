<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// call functions
require('../main.inc.php');

// create get vars
$case = $_GET['case'];

switch ($case) {
    // CASE: Action
    case "action":
        // def. Vars
        $errorMSG = "";
        $cfg = $_POST["cfg"];
        $serv = new server($cfg);
        $para = $_POST["para"];
        $para_txt = (isset($_POST["txt"])) ? $_POST["txt"] : "";
        if ($para == null) $para[0] = null;
        $paraend = null;

        // Aktion
        if (empty($_POST["action"]) && $_POST["custom"] == "") {
            $alert->code = 2;
            $errorMSG = $alert->re();
        } else {
            $action = $_POST["action"];
        }
        
        // erstelle parameter [Type=0]
        if (count($para) > 1) {
            for ($i=0;$i<count($para);$i++) {
                $paraend .= ' '.$para[$i];
            }
            $paraend .= ' ';
        }
        elseif (count($para) <1) {
            $paraend = null;
        } else {
            $paraend = ' '.$para[0].' ';
        }

        // erstelle parameter [Type=1]
        for($i=0;$i<count($para_txt);$i++) {
            $input = $para_txt[$i]["input"];
            $para_input = $para_txt[$i]["para"];
            if($input != "") $paraend .= " $para_input=\"$input\" ";
            if($input != "" && $para_input == "--beta") $paraend .= " --validate ";
        }

        // überschreibe ggf die action liste
        $send_shell = ($_POST["custom"] == "") ? $action.$paraend : $_POST["custom"];

        //sichere shell etwas
        $find = array("|", ";", "&", "\n", "\r");
        $send_shell = str_replace($find, null, $send_shell);

        // sende command
        $server = new server($cfg);
        $json = json_decode(file_get_contents('app/json/serverinfo/'.$cfg.'.json'));
        $force = (isset($_POST["force"])) ? true : false;
        if($errorMSG == "") {
            if (!$serv->send_action($send_shell, $force)) {
                $alert->code = 13;
                $errorMSG = $alert->re();
            }
        }

        if (empty($errorMSG)){
            $alert->code = 105;
            $alert->overwrite_text = $send_shell.' @'.$cfg;
            $msg = $alert->re();

            // Close server
            $json->next = 'TRUE';
            $json = json_encode($json);
            file_put_contents('app/json/serverinfo/'.$cfg.'.json', $json);

            echo json_encode(['code'=>200, 'msg'=>$msg]);
            exit;
        }
        echo json_encode(['code'=>404, 'msg'=>$errorMSG]);
        break;


    default:
        echo "Case not found";
        break;
}
$mycon->close();
?>