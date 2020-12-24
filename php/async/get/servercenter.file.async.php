<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

require('../main.inc.php');
$cfg            = $_GET['serv'];
$serv           = new server($cfg);
$case           = $_GET['case'];
$ckonfig        = $helper->fileToJson(__ADIR__.'/php/inc/custom_konfig.json', true);
$servlocdir     = $ckonfig['servlocdir'];
$dir            = $servlocdir.$_GET["path"];
$dirp           = $_GET["path"];

switch ($case) {
    // CASE: file list
    case "files":
        // scane bzw gebe default
        if($dir == $servlocdir || !file_exists($dir)) {
            $dir_scan = array();
            if(strpos($serv->cfgRead("arkserverroot"), $servlocdir) !== false && @file_exists($serv->cfgRead("arkserverroot"))) $dir_scan[] = str_replace($servlocdir, null, $serv->cfgRead("arkserverroot"));
            if(strpos($serv->cfgRead("logdir"), $servlocdir) !== false && @file_exists($serv->cfgRead("logdir"))) $dir_scan[] = str_replace($servlocdir, null, $serv->cfgRead("logdir"));
            if(strpos($serv->cfgRead("arkbackupdir"), $servlocdir) !== false && @file_exists($serv->cfgRead("arkbackupdir"))) $dir_scan[] = str_replace($servlocdir, null, $serv->cfgRead("arkbackupdir"));
            if(@file_exists(__ADIR__."/remote/serv/cluster/")) $dir_scan[] = "cluster";
        }
        else {
            $dir_scan = scandir($dir);
        }
        
        $file_list = $dir_list = null;
        // erstelle dateien pfade
        $i = 0;
        foreach($dir_scan as $item) {
            if($item != "." && $item != "..") {
                $target = "$dir/$item";
                $list = new Template("file_manager.htm", __ADIR__."/app/template/lists/serv/jquery/");
                $list->load();

                $fileinfos = pathinfo( $target);
                $filesize = (is_dir($target)) ? null : bitrechner(filesize($target));
                
                $list->r("cfg", $cfg);
                $list->r("size", $filesize);
                $list->r("dirname", $fileinfos["dirname"]);
                $list->r("id", md5($i));
                $list->r("name", $item);
                $list->r("surl", "$dirp$item/");
                $list->r("item", $item);
                $list->r("durl", "/remote/serv/$dirp$item");
                $list->rif("dir", is_dir($target));
                $list->rif("re", false);
                $list->r("ico", setico($target));
                if(!is_dir($target)){ 
                    $file_list .= $list->load_var();
                }
                else {
                    $dir_list .= $list->load_var();
                } 
            }
            elseif($item == "..") {
                $target = $dir;
                $list = new Template("file_manager.htm", __ADIR__."/app/template/lists/serv/jquery/");
                $list->load();

                $dirpz = "$dirp/";

                $exp = explode("/", $dirpz);
                if(is_array($exp)) if(is_countable($exp)) if(count($exp) > 1) unset($exp[(count($exp)-1)]);
                if(is_array($exp)) if(is_countable($exp)) if(count($exp) > 1) unset($exp[(count($exp)-2)]);
                $imp = implode("/", $exp);

                $list->r("cfg", $cfg);
                $list->r("name", $item);
                $list->r("details", null);
                $list->r("surl", $imp);
                $list->r("item", $item);
                $list->rif("dir", is_dir($target));
                $list->rif("re", true);
                $list->r("ico", setico($target));
                if(!is_dir($target)){ 
                    $file_list .= $list->load_var();
                }
                else {
                    $dir_list .= $list->load_var();
                } 
            }
            $i++;
        }
        echo $dir_list.$file_list;
    break;


    case "load":
        $list = new Template("load_file_manager.htm", __ADIR__."/app/template/universally/jquery/");
        $list->load();
        $list->echo();
    break;


    case "del":
        if($session_user->perm("server/$cfg/filebrowser/remove")) {
            $file = $_GET["file"];
            $size = strlen($file)-1;
            $file=substr($file,1,$size);
            if($KUTIL->removeFile($file)) {
                $re["code"] = 200;
            }
            else {
                $re["code"] = 1;
            }
        }
        else {
            $re["code"] = 99;
        }
        echo json_encode($re);
    break;


    default:
        echo "Case <b>$case</b> not found!";
        break;
}
$mycon->close();
