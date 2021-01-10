<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

$sitetpl    = new Template("step1.htm", $dirs["tpl"]);
$sitetpl->load();
$complete   = $ok = false;
$json       = $check->json;
$list       = $modals = null;

for($i=0;$i<count($json);$i++) {
    // Erstelle Tabelle mit den Prüfungen
    $checked = $check->check($i);
    $id = rndbit(10);
    $list       .= "
        <tr>
            <td>".$json[$i]['name']."</td>
            <td>".(($checked["code"] <= 1) ? '<button class="btn btn-info btn-sm" onclick="$(\'#'.$id.'\').toggle()">{::lang::install::allg::showinfos}</button>' : null)."</td>
            <td style=\"text-align: center; vertical-align: middle;\" class=\"bg-".$checked['color']."\"><i class=\"fa ".$checked['icon']."\"></i></td>
        </tr>
    ";

    // Lade zusätzlich den Info Modal
    if($checked["code"] <= 1) {
        $list   .= "
            <tr id=\"$id\" style=\"display:none\">
                <td colspan=\"3\">".$json[$i]["lang"]."</td>
            </tr>
        ";
    }
}



$sitetpl->r ("modal", $modals);
$sitetpl->r ("list_check", $list);
$sitetpl->rif ("ifallok", $check->check_all());

$title      = "{::lang::install::step0::title}";
$content    = $sitetpl->load_var();



