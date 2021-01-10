<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

require('../main.inc.php');

$case = $_GET["case"];

switch ($case) {
    // CASE: Whitelist list
    case "create":
        $section    = $_GET["section"];
        $list       = new Template("add.htm", __ADIR__."/app/template/lists/serv/konfig/");
        $list->load();
        $list->r("sk", $section);
        $list->rif("item", true);
        $list->r("rnd", md5(rndbit(50)));
        $list->echo();
    break;

    case "create_section":
        $section    = $_GET["section"];
        $list       = new Template("add.htm", __ADIR__."/app/template/lists/serv/konfig/");
        $list->load();
        $list->r("sk", $section);
        $list->rif("item", false);
        $list->r("rnd", md5(rndbit(50)));
        $list->echo();
    break;

    default:
    break;
}
$mycon->close();
