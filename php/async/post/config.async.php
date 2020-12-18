<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

// call functions
require('../main.inc.php');

$case = isset($_GET["case"]) ? $_GET["case"] : "nocase";

switch ($case) {
    // CASE: RCON send command
    case "editAPI":
        $API_path               = __ADIR__."/php/inc/api.json";
        $int                    = $_POST["active"] == "true" ? 1 : 0;

        $API_array              = $helper->fileToJson($API_path, true);
        $API_array["active"]    = $int;

        echo '{"state": '.intval($helper->saveFile($API_array, $API_path)).'}';
        break;
    default:
        echo "Case not found";
        break;
}
$mycon->close();