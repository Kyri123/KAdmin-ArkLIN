<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2020, Oliver Kaufmann
 * @Github: https://github.com/Kyri123
 * *******************************************************************************************
*/

class KUTIL {
    // Public Vars
    public  $debug;

    /**
     * KUTIL constructor.
     * @param   array       $allowedPath  Erlaubte Parts
     * @param   boolean     $debug        Debug ativieren?
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * Schaut ob im Array ein String gefunden wird
     *
     * @param   string|int $haystack
     * @param   array $array
     * @return  bool
     */
    public function strpos_arr($haystack, array $array)
    {
        foreach($array as $str) {
            if(!is_array($str)) {
                if(strpos($haystack, $str) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * - Wandelt den Pfad in einen total Pfad um (ohne ../) <br>
     * - Entfernt Nullbytes
     * @param   string $path
     * @return  array
     */
    public function path(string $path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $key => $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return array(
            "path"      => implode(DIRECTORY_SEPARATOR, $absolutes),
            "last"      => array_pop($absolutes),
            "nolast"    => implode(DIRECTORY_SEPARATOR, $absolutes)
        );
    }

    /**
     * KUtil: Modifizierte version von file_get_contents
     * @link    https://php.net/manual/en/function.file-get-contents.php
     * @param   string $filename
     * @param   string $asJson Als JSON ausgeben
     * @return  boolean|string|array|object
     */
    public function fileGetContents(string $filename, bool $asJson = false) {
        $filename = "/".$this->path($filename)["path"];
        try {
            if(@file_exists($filename) && @fileperms($filename) !== false) {
                if($asJson) {
                    $jsonString     = file_get_contents($filename);
                    $json           = [];
                    $json["Assoc"]  = json_decode($jsonString, true, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    $json["Obj"]    = json_decode($jsonString, false, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    return $json;
                }
                else {
                    return file_get_contents($filename);
                }
            }
            else {
                throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>File not found or no perms <b>$filename</b></p>");
            }
        }
        catch (Exception $e) {
            if($this->debug) echo $e->getMessage();
        }
        return false;
    }

    /**
     * KUtil: Modifizierte version von file_put_contents
     * @link    https://php.net/manual/en/function.file-put-contents.php
     * @param   string $filename
     * @param   $data
     * @param   int $flags
     * @param   $context
     * @return  bool
     */
    public function filePutContents(string $filename, $data) {
        $filename = $this->path($filename);
        if($this->mkdir($filename["nolast"])) {
            try {
                if(!@file_put_contents("/".$filename["path"], $data)) throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Cannot create File with Path <b>$filename</b></p>");
                return true;
            }
            catch (Exception $e) {
                if($this->debug) echo $e->getMessage();
            }
        }
        return false;
    }

    /**
     * KUtil: Erzeugt eine Datei unter dem Pfad
     * @param   string $path
     * @return  bool
     */
    public function makeFile(string $path) {
        $filename = $this->path($path);
        if($this->mkdir($filename["nolast"]) && !@file_exists($filename["path"])) {
            try {
                if(!@file_put_contents("/".$filename["path"], " ")) throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Cannot create File with Path <b>$filename</b></p>");
                return true;
            }
            catch (Exception $e) {
                if($this->debug) echo $e->getMessage();
            }
        }
        return false;
    }

    /**
     * KUtil: Erzeugt ein verzeichnis Rekursiv
     * @param   string $path
     * @return  bool (Gibt True wenn File exsistiert ODER erstellt wurde)
     */
    public function mkdir(string $path) {
        $path = $this->path($path)["path"];
        if(!file_exists($path)) {
            try {
                $parts  = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
                $pather = "/";
                $bool   = false;
                foreach ($parts as $item) {
                    $pather .= "$item/";
                    if(is_int(@fileperms("$pather../"))) if(!file_exists($pather)) if(!mkdir($pather)) {
                        throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Connot create Path <b>$filename</b></p>");
                    }
                    else {
                        echo 1;
                    }
                }
                return $bool;
            }
            catch (Exception $e) {
                if($this->debug) echo $e->getMessage();
            }
        }
        else {
            return true;
        }
        return false;
    }
}



$KUTIL = new KUTIL();
?>