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
        $errorMSG = "";


        /* cfg */
        $cfg = $_POST["cfg"];
        $serv = new server($cfg);
        $para = $_POST["para"];
        if ($para == null) $para[0] = null;
        $paraend = null;
        /* action */
        if (empty($_POST["action"])) {
            $alert->code = 2;
            $errorMSG = $alert->re();
        } else {
            $action = $_POST["action"];
        }
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


        $server = new server($cfg);
        $json = json_decode(file_get_contents('app/json/serverinfo/'.$cfg.'.json'));
        if ($json->next == 'TRUE') {
            $alert->code = 12;
            $errorMSG = $alert->re();
        } else {
            if (!$serv->send_action($action.$paraend)) {
                $alert->code = 1;
                $errorMSG = $alert->re();
            }
        }



        if (empty($errorMSG)){
            $alert->code = 105;
            $alert->overwrite_text = $action.$paraend.' @'.$cfg;
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
?>