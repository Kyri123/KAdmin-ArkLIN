<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüft ob die Aktuelle PHP version unter 7.3 ist
if(PHP_VERSION_ID < 70300) {
    function is_countable($arr) {
        return is_array($arr);
    }
}

?>