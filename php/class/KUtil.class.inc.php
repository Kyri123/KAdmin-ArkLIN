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
    public  $allowedPath;
    public  $debug;

    /**
     * KUTIL constructor.
     * @param   array       $allowedPath  Erlaubte Parts
     * @param   boolean     $debug        Debug ativieren?
     */
    public function __construct($allowedPath = [], $debug = false)
    {
        $this->allowedPath      = $allowedPath;
        $this->debug            = $debug;
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
            "path"      =>  implode(DIRECTORY_SEPARATOR, $absolutes),
            "last"      =>  $parts[array_key_last($parts)],
            "nolast"    =>  str_replace($parts[array_key_last($parts)], null, implode(DIRECTORY_SEPARATOR, $absolutes))
        );
    }

    /**
     * - Pfad zu allowedPath Hinzufügen
     * @param   string|array $Path
     * @return  void
     */
    public function addPath($Path) {
        if(is_array($Path)) {
            $this->allowedPath = array_merge($this->allowedPath, $Path);
        }
        else {
            $this->allowedPath = array_push($this->allowedPath, $Path);
        }
    }

    /**
     * Prüft ob der Pfad erlaubt ist
     * @param   string $Path
     * @return  bool
     */
    public function checkAllowedPath(string $Path) {
        return is_array($this->allowedPath) ?
            count($this->allowedPath) > 0 ?
                strpos_arr($Path, $this->allowedPath)
                : false
            : false;
    }

    /**
     * KUtil: Modifizierte version von file_get_contents
     * @link    https://php.net/manual/en/function.file-get-contents.php
     * @param   string $filename
     * @param   string $asJson Als JSON ausgeben
     * @param   false $use_include_path
     * @param   null $context
     * @param   int $offset
     * @param   null $maxlen
     * @return  boolean|string|array|object
     */
    public function fileGetContents(string $filename, bool $asJson = false, $use_include_path = false, $context = null, $offset = 0, $maxlen = null) {
        $filename = $this->path($filename)["path"];
        if($this->checkAllowedPath($filename)) {
            try {
                if($asJson) {
                    $jsonString     = file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
                    $json           = [];
                    $json["Assoc"]  = json_decode($jsonString, true, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    $json["Obj"]    = json_decode($jsonString, false, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
                    return $json;
                }
                else {
                    return file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
                }
            }
            catch (Exception $e) {
                if($this->debug) echo $e->getMessage();
            }
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
    public function filePutContents(string $filename, $data, $flags = 0, $context = null) {
        $filename = $this->path($filename)["path"];
        if($this->checkAllowedPath($filename)) {
            try {
                file_put_contents($filename, $data, $flags, $context);
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
     * @link    https://php.net/manual/en/function.file-put-contents.php
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
                        throw new \Exception($output, System::CAN_NOT_MAKE_DIRECTORY);
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