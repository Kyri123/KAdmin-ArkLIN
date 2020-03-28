<?php

#Mysql
include('pconfig.inc.php');

#allgmeine
$sitename = "ArkAdmin";
$sitename_short = "AA";
$version = '0.5.0';
$ip = $_SERVER['SERVER_ADDR'];

#Webserver
$webserver['url'] = 'http://data.chiraya.de/';
$webserver['changelog'] = $webserver['url'].'changelog.json';
$webserver['version'] = $webserver['url'].'version.json';

?>