<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
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
        $errorMSG           = "";
        $cfg                = $_POST["cfg"];
        $_POST["custom"]    = isset($_POST["custom"]) ? saveshell($_POST["custom"]) : null;
        $serv               = new server($cfg);
        $para               = isset($_POST["para"]) ? $_POST["para"] : [];
        $para_txt           = (isset($_POST["txt"])) ? $_POST["txt"] : "";
        if ($para == null) $para[0] = null;
        $paraend            = null;

        if($session_user->perm("server/$cfg/actions")) {
            // Aktion
            if (empty($_POST["action"]) && $_POST["custom"] == "") {
                $errorMSG   = $alert->rd(2);
            } else {
                $action     = saveshell($_POST["action"]);
            }

            // erstelle parameter [Type=0]
            if (count($para) > 1) {
                for ($i=0;$i<count($para);$i++)
                    $paraend .= ' '.$para[$i];
                $paraend .= ' ';
            }
            elseif (count($para) <1) {
                $paraend = null;
            } else {
                $paraend = ' '.$para[0].' ';
            }

            // erstelle parameter [Type=1]
            for($i=0;$i<count($para_txt);$i++) {
                $input          = isset($para_txt[$i]["input"]) ? $para_txt[$i]["input"] : "";
                $para_input     = isset($para_txt[$i]["para"]) ? $para_txt[$i]["para"] : "";
                if($input != "") $paraend                               .= " $para_input=\"$input\" ";
                if($input != "" && $para_input == "--beta") $paraend    .= " --validate ";
            }

            // überschreibe ggf die action liste
            $send_shell = ($_POST["custom"] == "") ? $action.$paraend : $_POST["custom"];

            //sichere shell etwas
            $find           = array("|", ";", "&", "\n", "\r");
            $send_shell     = str_replace($find, null, $send_shell);

            // sende command
            $server         = new server($cfg);
            $json       = json_decode($KUTIL->fileGetContents(__ADIR__.'/app/json/serverinfo/'.$cfg.'.json'));
            $force      = (isset($_POST["force"])) ? true : false;
            if($errorMSG == "")
                if (!$serv->sendAction($send_shell, $force)) {
                    $errorMSG = $alert->rd(13);
                }
        }
        else {
            $errorMSG = $alert->rd(99);
        }

        if (empty($errorMSG)){
            $alert->code            = 105;
            $alert->overwrite_text  = $send_shell.' @'.$cfg;
            $msg                    = $alert->re();

            // Close server
            $json->next     = 'TRUE';
            $json           = json_encode($json);
            $KUTIL->filePutContents(__ADIR__.'/app/json/serverinfo/'.$cfg.'.json', $json);

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
