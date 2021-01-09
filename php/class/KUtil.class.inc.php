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
    public  $debug              = false;
    public  $replacePathFrom    = [];
    public  $replacePathTo      = [];

    /**
     * KUTIL constructor.
     * @param   boolean     $debug        Debug ativieren?
     */
    public function __construct($debug = false)
    {
        if($debug === true) $this->debug = true;
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
        $path   = str_replace($this->replacePathFrom, $this->replacePathTo,$path);
        $path   = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts  = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
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
            "/path"      => "/".implode(DIRECTORY_SEPARATOR, $absolutes),
            "/path/"      => "/".implode(DIRECTORY_SEPARATOR, $absolutes)."/",
            "path"      => implode(DIRECTORY_SEPARATOR, $absolutes),
            "last"      => array_pop($absolutes),
            "nolast"    => implode(DIRECTORY_SEPARATOR, $absolutes)
        );
    }

    /**
     * KUtil: Modifizierte version von file_get_contents
     * @param   string $filename
     * @param   bool $asJson Als JSON ausgeben [Assoc|Obj]
     * @return  boolean|string|array|object
     */
    public function fileGetContents(string $filename, bool $asJson = false) {
        $filename = $this->path($filename)["/path"];
        try {
            if(@file_exists($filename) && @fileperms($filename) !== false) {
                if($asJson) {
                    $jsonString     = @file_get_contents($filename);
                    $json           = [];
                    $json["Assoc"]  = json_decode($jsonString, true, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    $json["Obj"]    = json_decode($jsonString, false, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    return $json;
                }
                else {
                    return @file_get_contents($filename);
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
     * KUtil: hole Content von einer Webseite
     * @param string $url URL zur Webseite
     * @param bool $asJson Als JSON ausgeben [Assoc|Obj]
     * @return  boolean|string|array|object
     */
    public function fileGetContentsURL(string $url, bool $asJson = false) {
        try {
            $ch         = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, true );
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            $content    = curl_exec($ch);
            curl_close($ch);

            if($content !== false) {
                if($asJson) {
                    $json["Assoc"]  = json_decode($content, true, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    $json["Obj"]    = json_decode($content, false, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    return $json;
                }
                else {
                    return $content;
                }
            }
            else {
                throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>File not found or no perms <b>$url</b></p>");
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
     * @return  bool
     */
    public function filePutContents(string $filename, $data): bool
    {
        $filename = $this->path($filename);
        if($this->mkdir($filename["nolast"], true)) {
            try {
                if(!@file_put_contents($filename["/path"], $data)) throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Cannot write File with Path <b>".$filename['/path']."</b></p>");
                return true;
            }
            catch (Exception $e) {
                if($this->debug) echo $e->getMessage();
                echo "<hr>";
            }
        }
        return false;
    }

    /**
     * KUtil: Erzeugt eine Datei unter dem Pfad
     * @param   string $path
     * @param   string $data
     * @return  bool
     */
    public function createFile(string $path, string $data = " "): bool
    {
        $filename = $this->path($path);
        if(@file_exists($filename["/path"])) return true;
        if($this->mkdir($filename["nolast"]) && !@file_exists($filename["/path"])) {
            try {
                if(!@file_put_contents($filename["/path"], $data)) throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Cannot create File with Path <b>$filename</b></p>");
                return true;
            }
            catch (Exception $e) {
                if($this->debug) echo $e->getMessage();
            }
        }
        return false;
    }

    /**
     * KUtil: Entfernt eine Datei (ist diese nicht vorhanden ist der <b>return</b> true)
     * @param   string $path
     * @param   bool $recursive soll es Rekursiv gelöscht werden?
     * @return  bool
     */
    public function removeFile(string $path, bool $recursive = true): bool
    {
        $filename = $this->path($path);
        if(@file_exists($filename["/path"])) {
            try {
                if($recursive && @is_dir($filename["/path"])) {
                    if(!@$this->removeRecursive($filename["/path"])) throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Cannot remove File OR Path with Path <b>".$filename["/path"]."</b></p>");
                }
                else {
                    if(!@unlink($filename["/path"])) throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Cannot remove File OR Path with Path <b>".$filename["/path"]."</b></p>");
                }
                return true;
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

    /**
     * KUtil: Entfernt ein Ordner Rekursiv
     * @param $path
     * @return mixed
     */
    private function removeRecursive($path) {
        foreach (@array_diff(scandir($path), array('.','..')) as $file) {
            if (@is_dir("$path/$file")) {
                @$this->removeRecursive("$path/$file");
            }
            else {
                @unlink("$dir/$file");
            }
        }
        return @rmdir($path);
    }

    /**
     * KUtil: Erzeugt ein verzeichnis Rekursiv
     * @param   string $path
     * @param   bool $recursiv
     * @return  bool (Gibt True wenn File exsistiert ODER erstellt wurde)
     */
    public function mkdir(string $path, bool $recursiv = true) {
        $path = $this->path($path)["/path"];
        if(!file_exists($path)) {
            try {
                if(!@mkdir($path, 777, $recursiv)) {
                    throw new \Exception("<p style='margin-bottom:0px; margin-left: 80px;'>Connot create Path <b>$filename</b></p>");
                }
                return true;
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