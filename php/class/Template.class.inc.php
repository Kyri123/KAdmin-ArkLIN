<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
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

    public function __construct($file, $path) {
        if(isset($_COOKIE["lang"])) $this->lang = $_COOKIE["lang"];
        if (file_exists($path.$file)) {
            $this->filepath = $path.$file;
        } else {
            $this->filepath = null;
        }
        $this->file_str = $file;
    }

    public function debug(bool $bool) {
        $this->debug = $bool;
    }

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

    public function rif ($key, $boolean) {
        if (!$this->load) {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
            return false;
        }
        array_push($this->rifkey, $key);
        array_push($this->rifbool, $boolean);
        return true;
    }

    public function session() {
        if (!$this->load) {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
            return null;
        }
        global $_SESSION;
        $srank = (isset($_SESSION['rank'])) ? $_SESSION['rank'] : 0;
        if (isset($_SESSION['id'])) {
            $this->file = preg_replace("/\{issetS\}(.*)\\{\/issetS\}/Uis", '\\1', $this->file);
            $this->file = preg_replace("/\{!issetS\}(.*)\\{\/!issetS\}/Uis", null, $this->file);
            // -----------1------------
            for ($i=0;$i<10;$i++) {
                if ($srank > $i) {
                    $this->file = preg_replace("/\{rank".$i."\}(.*)\\{\/rank".$i."\}/Uis", '\\1', $this->file);
                    $this->file = preg_replace("/\{!rank".$i."\}(.*)\\{\/!rank".$i."\}/Uis", null, $this->file);
                }
                else {
                    $this->file = preg_replace("/\{rank".$i."\}(.*)\\{\/rank".$i."\}/Uis", null, $this->file);
                    $this->file = preg_replace("/\{!rank".$i."\}(.*)\\{\/!rank".$i."\}/Uis", '\\1', $this->file);
                }
            }
        } else {
            $this->file = preg_replace("/\{issetS\}(.*)\\{\/issetS\}/Uis", null, $this->file);
            $this->file = preg_replace("/\{!issetS\}(.*)\\{\/!issetS\}/Uis", '\\1', $this->file);
        }
    }

    public function load_var() {
        if ($this->load) {
            $this->final();
            return $this->file;
        } else {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
        }
    }

    public function echo() {
        if ($this->load) {
            $this->final();
            echo $this->file;
        } else {
            echo "<p>Template not Loaded ' . $this->file_str . ' </p>";
        }
    }

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

    private function final() {
        $langfile = "app/lang/$this->lang/";
        if (!file_exists($langfile)) $langfile = "app/lang/de_de/";
        if (file_exists($langfile.'htm.xml') && file_exists($langfile.'php.xml') && file_exists($langfile.'install.xml')) {
            $this->load_xml($langfile.'htm.xml');
            $this->load_xml($langfile.'php.xml');
            $this->load_xml($langfile.'install.xml');
            $this->load_xml($langfile.'alert.xml');
        } else {
            $this->load_xml("app/lang/de_de/htm.xml");
            $this->load_xml("app/lang/de_de/php.xml");
            $this->load_xml("app/lang/de_de/install.xml");
            $this->load_xml("app/lang/de_de/alert.xml");
        }
        
        $this->rlang(); $this->rintern(); // 3x um {xxx{xxx}} aus der XML zu verwenden
        $this->rlang(); $this->rintern(); // 3x um {xxx{xxx}} aus der XML zu verwenden
        $this->rlang(); $this->rintern(); // 3x um {xxx{xxx}} aus der XML zu verwenden

        //Todo: nochmals mit der Standartsprachdatei drüber gehen?

        $this->bb_codes();
    }

    // Private :: Lang
    private function rlang() {
        // ersetzte im Template
        $this->file = str_replace($this->langfrom, $this->langto, $this->file);
        return $this->file;
    }

    private function load_xml($langfile) {
        global $helper;
        // mache XML zu einem Array
        $xml = simplexml_load_file($langfile);
        $xml = $helper->str_to_json($helper->json_to_str($xml), true);

        //splite array um ein im Template einzubinden
        $this->read_xml($xml, "::lang");
    }

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
            '#\[a="(.*?)"](.*?)\[\/a\]#si'
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
        );
        $this->file = preg_replace($s, $r, $this->file);
    }
}

?>