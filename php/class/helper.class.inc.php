<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

/**
 * helper
 * - Zum lesen von JSON
 * - Schreiben von Array in Dateien
 * - Lesen von XML
 */
class helper extends KUTIL {

    /**
     * __construct
     *
     * @return void
     */
    public function __construct() {}
    
    /**
     * Ruft eine Json von einem anderen Webserver ab
     *
     * @param  string $path Pfad zur Datei
     * @param  string $filename Dateiname wie sie im Cache gespeichert werden soll
     * @param  int $differ wie groß soll die Differnz zwischen der Remote und der Localen Datei sein?
     * @param  bool $array Soll es als Array ausgebene werden (true) oder als Object (false)
     * @return false|void
     */
    public function remoteFileToJson(String $path, String $filename, int $differ = 0, Bool $array = true) {
        $filename = __ADIR__.'/app/cache/'.$filename;
        $diff = 0;
        $string = null;

        if (@file_exists($filename)) {
            $filetime = filemtime($filename);
            $diff = time()-$filetime;
        }
        if ($diff > $differ && @file_exists($filename)) {
            if($string = file_get_contents($path)) {
                parent::filePutContents($filename, $string);
                return json_decode($string, $array);
            }
            else {
                return (!file_exists($filename)) ? false : $this->fileToJson($filename, $array);
            }
        }
        elseif (!file_exists($filename)) {
            if($string = file_get_contents($path)) {
                $handle = fopen($filename, 'w');
                fclose($handle);
                parent::filePutContents($filename, $string);
                return json_decode($string, $array);
            }
            else {
                return (!file_exists($filename)) ? false : $this->fileToJson($filename, $array);
            }
        } else {
            return (!file_exists($filename)) ? false : $this->fileToJson($filename, $array);
        }
    }
        
    /**
     * Wandelt ein Pfad direkt in eine Json
     *
     * @param  string $path Pfad zur Datei
     * @param  bool $array Soll es als Array ausgebene werden (true) oder als Object (false)
     * @return array|string
     */
    public function fileToJson(String $path, Bool $array = true) {
        return parent::fileGetContents($path, true)[($array ? "Assoc" : "Obj")];
    }
    
    /**
     * Wandelt einen String in ein Array (Json Format)
     *
     * @param  mixed $str String der in ein Array gewandelt werden soll
     * @param  bool $array Soll es als Array ausgebene werden (true) oder als Object (false)
     * @return array
     */
    public function stringToJson(String $str, Bool $array = true) {
        return json_decode($str, $array);
    }
    
    /**
     * Wandelt einen Array in ein String (Json Format)
     *
     * @param  mixed $array Array die umgewandelt werden soll
     * @return string
     */
    public function jsonToString($array) {
        return json_encode($array, JSON_INVALID_UTF8_SUBSTITUTE);
    }
    
    /**
     * Speichert eine Json hier muss die Datei NICHT exsistieren
     *
     * @param  mixed $data Array der gespeichert werden soll
     * @param  mixed $path Pfad wo die Datei gespeichert werden soll
     * @return bool
     */
    public function saveFile($data, String $path) {
        return parent::filePutContents($path, is_array($data) ? $this->jsonToString($data) : $data);
    }
    
    /**
     * Gibt den letzte Abruf vom Panel wieder
     * - In Sekunden
     *
     * @return int
     */
    public function getHelperDiff() {
        return (time() - intval(parent::fileGetContents(__ADIR__."/app/check/webhelper")));
    }
    
    /**
     * Wandelt eine .XML in ein Array
     *
     * @param  mixed $file Pfad wo die Datei liegt
     * @return array
     */
    public function xmlFileToArray(String $file) {
        return $this->stringToJson($this->jsonToString(simplexml_load_file(parent::path($file)["/path"])), true);
    }

}


