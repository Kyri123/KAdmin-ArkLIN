<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg = $_GET['serv'];
$serv = new server($cfg);
$case = $_GET['case'];
$ckonfig = $helper->file_to_json('php/inc/custom_konfig.json', true);
$servlocdir = $ckonfig['servlocdir'];
$dir = $servlocdir.$_GET["path"];
$dirp = $_GET["path"];

switch ($case) {
    // CASE: file list
    case "files":
        // scane bzw gebe default
        if($dir == $servlocdir || !file_exists($dir)) {
            $dir_scan = array();
            if(strpos($serv->cfg_read("arkserverroot"), $servlocdir) !== false && file_exists($serv->cfg_read("arkserverroot"))) $dir_scan[] = str_replace($servlocdir, null, $serv->cfg_read("arkserverroot"));
            if(strpos($serv->cfg_read("logdir"), $servlocdir) !== false && file_exists($serv->cfg_read("logdir"))) $dir_scan[] = str_replace($servlocdir, null, $serv->cfg_read("logdir"));
            if(strpos($serv->cfg_read("arkbackupdir"), $servlocdir) !== false && file_exists($serv->cfg_read("arkbackupdir"))) $dir_scan[] = str_replace($servlocdir, null, $serv->cfg_read("arkbackupdir"));
            if(file_exists("remote/serv/cluster/")) $dir_scan[] = "cluster";
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
                $list = new Template("file_manager.htm", "app/template/lists/serv/jquery/");
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
                $list = new Template("file_manager.htm", "app/template/lists/serv/jquery/");
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
        $list = new Template("load_file_manager.htm", "app/template/universally/jquery/");
        $list->load();
        $list->echo();
    break;


    case "del":
        if($user->perm("server/$cfg/filebrowser/remove")) {
            $file = $_GET["file"];
            $size = strlen($file)-1;
            $file=substr($file,1,$size); 
            if(file_exists($file)) {
                if(!is_dir($file)) {
                    if(unlink($file)) {
                        $re["code"] = 200;
                    }
                    else {
                        $re["code"] = 1;
                    }
                }
                else {
                    $re["code"] = 33;
                }
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
