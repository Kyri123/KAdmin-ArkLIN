<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

class helper {
    public function __construct()
    {
        #empty
    }

    public function remotefile_to_json($path, $filename, $differ = 0, $array = true) {
        $filename = 'cache/'.$filename;
        $diff = 0;
        if (file_exists($filename)) {
            $filetime = filemtime($filename);
            $diff = time()-$filetime;
        }
        if (file_get_contents($path) && $diff > $differ && file_exists($filename)) {
            $string = file_get_contents($path);
            file_put_contents($filename, $string);
            return json_decode($string, $array);
        }
        elseif (file_get_contents($path) && !file_exists($filename)) {
            $string = file_get_contents($path);
            $handle = fopen($filename, 'w');
            fclose($handle);
            file_put_contents($filename, $string);
            return json_decode($string, $array);
        } else {
            return json_decode(file_get_contents($filename), $array);
            file_put_contents($filename, $string);
        }
    }
    public function file_to_json($path, $array = true) {
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), $array);
        } else {
            return json_decode('{"file":"notexsists"}', $array);
        }
    }

    public function str_to_json($str, $array = true) {
        return json_decode($str, $array);
    }

    public function json_to_str($json) {
        return json_encode($json);
    }

    public function savejson_exsists($json, $path) {
        if (file_exists($path)) {
            if (file_put_contents($path, json_encode($json, JSON_INVALID_UTF8_SUBSTITUTE))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function savejson_create($json, $path) {
        $content = json_encode($json, JSON_INVALID_UTF8_SUBSTITUTE);
        if (file_put_contents($path, $content)) {
            return true;
        } else {
            return false;
        }
    }

    public function gethelpertime() {
        $path = "app/check/webhelper";
        if (file_exists($path)) return file_get_contents($path);
        return 0;
    }

    public function gethelperdiff() {
        $lastcheck = $this->gethelpertime();
        $diff = time() - $lastcheck;
        return $diff;
    }

    public function xmlfile_to_array($file) {
        $xml = simplexml_load_file($file);
        $xml = $this->str_to_json($this->json_to_str($xml), true);
        return $xml;
    }

}

?>
