<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

/**
 * Class Template
 */
class Template {
    
    private $filepath;
    private $file = null;
    private $debug = false;
    private $load = false;
    private $file_str = false;
    public $lang = "de_de";
    public $rfrom = array();
    public $rto = array();
    public $rifkey = array();
    public $rifbool = array();
    private $langfrom = array();
    private $langto = array();

    /**
     * Template constructor.
     * @param $file
     * @param $path
     */
    public function __construct($file, $path) {
        if(isset($_COOKIE["lang"])) $this->lang = $_COOKIE["lang"];
        if (file_exists($path.$file)) {
            $this->filepath = $path.$file;
        } else {
            $this->filepath = null;
        }
        $this->file_str = $file;
    }

    /**
     * (De-)Aktiviert Debug
     *
     * @param bool $bool
     */
    public function debug(bool $bool) {
        $this->debug = $bool;
    }

    /**
     * Läd alle Daten von dem Template
     */
    public function load() {
        if ($this->filepath != null) {
            $this->file = file_get_contents($this->filepath);
            $this->load = true;
            if ($this->debug) {
                echo '<p>Template Found ' . $this->file_str . ' </p>';
            }
        } else {
            if ($this->debug) {
                echo '<p>Template not Found ' . $this->file_str . ' </p>';
            }
        }
    }

    /**
     * Fügt Daten hinzu die ersetzt werden sollten
     * - Format in der HTML: {daten}
     *
     * @param $from
     * @param $to
     * @return bool
     */
    public function r($from, $to) {
        if (!$this->load) {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
            return false;
        }
        array_push($this->rfrom, '{'.$from.'}');
        array_push($this->rto, $to);
        array_push($this->rfrom, '__'.$from.'__');
        array_push($this->rto, $to);
        return true;
    }

    /**
     * Fügt Daten hinzu die ersetzt werden sollten
     * - Format in der HTML:
     * -- true: {daten}...{/daten}
     * -- !true: {!daten}...{/!daten}
     * @param $key
     * @param $boolean
     * @return bool
     */
    public function rif ($key, $boolean) {
        if (!$this->load) {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
            return false;
        }
        array_push($this->rifkey, $key);
        array_push($this->rifbool, $boolean);
        return true;
    }

    /**
     * Verarbeitet Session Ränge
     *
     * @param $array
     * @param $key
     */
    private function session($array, $key, $isserver = false) {
        global $permissions;

        foreach ($array as $k => $v) {
            $mkey = $key."::$k";
            if (is_array($v)) {
                $this->session($v, $mkey, (strpos("server", $mkey) !== false));
            } else {
                $sk = "example";
                $exp = explode("::", $mkey);
                if(strpos("server", $mkey) !== false && isset($exp[2])) {
                    $sk = $exp[2];
                }
                if (boolval($v) || boolval($permissions["all"]["is_admin"]) || ($isserver && $permissions["server"][$sk]["is_server_admin"])) {
                    $this->file = preg_replace("/\{".$mkey."\}(.*)\\{\/".$mkey."\}/Uis", '\\1', $this->file);
                    $this->file = preg_replace("/\{!".$mkey."\}(.*)\\{\/!".$mkey."\}/Uis", null, $this->file);
                } else {
                    $this->file = preg_replace("/\{".$mkey."\}(.*)\\{\/".$mkey."\}/Uis", null, $this->file);
                    $this->file = preg_replace("/\{!".$mkey."\}(.*)\\{\/!".$mkey."\}/Uis", '\\1', $this->file);
                }
            }
        }
    }

    /**
     * Ausgabe kann ein eine Variable gelesen werden
     *
     * @return string
     */
    public function load_var() {
        if ($this->load) {
            $this->final();
            return $this->file;
        } else {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
        }
    }

    /**
     * Ausgabe wird direk ausgegeben und kann nicht in eine Variable gelesen werden
     */
    public function echo() {
        if ($this->load) {
            $this->final();
            echo $this->file;
        } else {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
        }
    }

    /**
     * Verarbeitet Replace und IfReplace
     */
    private function rintern() {
        if(is_array($this->rfrom) && is_array($this->rto)) {
            $this->file = str_replace($this->rfrom, $this->rto, $this->file);
            $i = 0;
            foreach ($this->rifkey as $key) {
                if ($this->rifbool[$i] === true) {
                    $this->file = preg_replace("/\{".$key."\}(.*)\\{\/".$key."\}/Uis", '\\1', $this->file);
                    $this->file = preg_replace("/\{!".$key."\}(.*)\\{\/!".$key."\}/Uis", null, $this->file);
                } else {
                    $this->file = preg_replace("/\{".$key."\}(.*)\\{\/".$key."\}/Uis", null, $this->file);
                    $this->file = preg_replace("/\{!".$key."\}(.*)\\{\/!".$key."\}/Uis", '\\1', $this->file);
                }
                $i++;
            } 
        }
    }

    /**
     * Finale verarbeitung des Templates (Sprachdateien usw)
     */
    private function final() {
        global $permissions;
        global $_SESSION;

        $langfile = "app/lang/$this->lang/";
        if (!file_exists($langfile)) $langfile = "app/lang/de_de/";

        $arr = scandir($langfile);
        foreach ($arr as $item) {
            if ($item != "." && $item != "..") $this->load_xml($langfile . $item);
        }

        // verarbeite Sprache, Permissions & Eingaben
        if(isset($_SESSION["id"]) && is_array($permissions)) $this->session($permissions, "permissions::");
        $this->rlang(); $this->rintern(); // 3x um {xxx{xxx}} aus der XML zu verwenden
        $this->rlang(); $this->rintern(); // 3x um {xxx{xxx}} aus der XML zu verwenden
        $this->rlang(); $this->rintern(); // 3x um {xxx{xxx}} aus der XML zu verwenden
        // Wende BB-Codes an
        $this->bb_codes();
    }

    /**
     * Verarbeitet die Sprachdateien
     *
     * @return string|string[]|null
     */
    private function rlang() {
        // ersetzte im Template
        $this->file = str_replace($this->langfrom, $this->langto, $this->file);
        return $this->file;
    }

    /**
     * Lade Sprachdateien aus der XML
     *
     * @param $langfile
     */
    private function load_xml($langfile) {
        global $helper;
        // mache XML zu einem Array
        $xml = simplexml_load_file($langfile);
        $xml = $helper->str_to_json($helper->json_to_str($xml), true);

        //splite array um ein im Template einzubinden
        $this->read_xml($xml, "::lang");
    }

    /**
     * Verarbeitet die Sprachdateien
     *
     * @param $array
     * @param $key
     */
    private function read_xml($array, $key) {
        foreach ($array as $k => $v) {
            $mkey = $key."::$k";
            if (is_array($v)) {
                $this->read_xml($v, $mkey);
            } else {
                array_push($this->langfrom, "{".$mkey."}");
                array_push($this->langto, nl2br($v));
            }
        }
    }

    /**
     * BBCodes für die Sprachdateien (XML)
     */
    private function bb_codes() {
        $s = array(
            '#\[cl="(.*?)"\](.*?)\[\/c\]#si',
            '#\[c=(.*?)\](.*?)\[\/c\]#si',
            '#\[d=(.*?)\](.*?)\[\/d\]#si',
            '#\[b](.*?)\[\/b\]#si',
            '#\[i](.*?)\[\/i\]#si',
            '#\[u](.*?)\[\/u\]#si',
            '#\[s](.*?)\[\/s\]#si',
            '#\[hr]#si',
            '#\[br]#si',
            '#\[table="(.*?)"](.*?)\[\/table\]#si',
            '#\[table](.*?)\[\/table\]#si',
            '#\[tr="(.*?)"](.*?)\[\/tr\]#si',
            '#\[tr](.*?)\[\/tr\]#si',
            '#\[td="(.*?)"](.*?)\[\/td\]#si',
            '#\[tr](.*?)\[\/tr\]#si',
            '#\[a="(.*?)"](.*?)\[\/a\]#si',
            '#\[a="(.*?)" blank](.*?)\[\/a\]#si',
            '#\[ico="(.*?)"]\[\/ico\]#si',
            '#\[img="(.*?)"]\[\/img\]#si'
        );
        $r = array(
            "<span class=\"$1\">$2</span>",
            "<span class=\"text-$1\">$2</span>",
            "<span class=\"d-$1\">$2</span>",
            "<b>$1</b>",
            "<i>$1</i>",
            "<u>$1</u>",
            "<s>$1</s>",
            "<hr />",
            "<br />",
            "<table class=\"table $1\">$2</table>",
            "<table class=\"table\">$1</table>",
            "<tr class=\"$1\">$2</tr>",
            "<tr>$1</tr>",
            "<td class=\"$1\">$2</td>",
            "<td>$1</td>",
            "<a href=\"$1\">$2</a>",
            "<a href=\"$1\" target=\"_blank\">$2</a>",
            "<i class=\"$1\"></i>",
            "<img src=\"$1\" />"
        );
        $this->file = preg_replace($s, $r, $this->file);
    }
}

?>