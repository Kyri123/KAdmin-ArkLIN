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
    function strpos_arr($haystack, array $array)
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
     * @param   string $Path
     * @return  false|string
     */
    public function path(string $Path) {
        return realpath(str_replace(chr(0), '', $Path));
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
        $filename = $this->path($filename);
        if($this->checkAllowedPath($filename)) {
            try {
                if($asJson) {
                    $jsonString     = file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
                    $json           = [];
                    $json["Assoc"]  = json_decode($jsonString, false, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
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
        $filename = $this->path($filename);
        if($this->checkAllowedPath($filename)) {
            try {
                file_put_contents($filename, $data, $flags, $context);
            }
            catch (Exception $e) {
                if($this->debug) echo $e->getMessage();
            }
        }
        return false;
    }
}



$KUTIL = new KUTIL();
?>