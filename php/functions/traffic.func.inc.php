<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/


/**
 * // Informationen vom Arbeitsspeicher (in %)
 *
 * @return string
 */

function mem_perc(){
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;
    $memory_usage = round($memory_usage, 2);
    return $memory_usage;
}

/**
 * Informationen vom Arbeitsspeicher
 *
 * @return array
 */

function mem_array(){
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage[0] = $mem[1];
    $memory_usage[1] = $mem[2];
    return $memory_usage;
}


/**
 * Prozzessor auslastung (in %)
 *
 * @return string
 */

function cpu_perc(){
    $load = sys_getloadavg();
    return $load[0];
}

