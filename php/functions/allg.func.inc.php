<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

/**
 * Gibt eine liste der Sprachen aus
 *
 * @return string
 */

function get_lang_list() {
    $re = null;
    $dir = dirToArray(__ADIR__."/app/lang");
    foreach($dir as $k => $v) {
        $ftpl = new Template("lang.htm", __ADIR__."/app/template/universally/default/");
        $ftpl->load();

        $xml = new xml_helper(__ADIR__."/app/lang/$k/info.xml");
        $arr = $xml->array();

        $ftpl->r("lang_icon", $arr["icon_path"]);
        $ftpl->r("lang_short", $k);
        $ftpl->r("lang_name", $arr["lang_name"]);
        $ftpl->r("author", $arr["author"]);
        $ftpl->rif("noimg", ($arr["icon_path"] == "null") ? false : true);

        $re .= $ftpl->load_var();
    }
    return $re;
}
