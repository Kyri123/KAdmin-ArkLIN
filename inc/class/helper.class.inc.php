<?php

class helper {
    public function __construct()
    {
        #empty
    }

    public function file_to_json($path) {
        if(file_exists($path)) {
            return json_decode(file_get_contents($path));
        }
        else {
            return json_decode('{"file":"notexsists"}');
        }
    }

    public function str_to_json($str, $array = true) {
        return json_decode($str, $array);
    }

    public function json_to_str($json) {
        return json_encode($json);
    }

    public function savejson_exsists($json, $path) {
        if(file_exists($path)) {
            if(file_put_contents($path, json_encode($json, JSON_INVALID_UTF8_SUBSTITUTE))) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function savejson_create($json, $path) {
        if(file_put_contents($path, json_encode($json, JSON_INVALID_UTF8_SUBSTITUTE))) {
            return true;
        }
        else {
            return false;
        }
    }

}

?>
