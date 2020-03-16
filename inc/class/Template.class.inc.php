<?php 

class Template {
    
    private $filepath;
    private $file = null;
    private $debug = false;
    
    function __construct($file, $path) {
        if(file_exists($path.$file)) {
            $this->filepath = $path.$file;
        }
        else {
            $this->filepath = null;
        }
    }

    function debug($boolean) {
        $this->debug = $boolean;
    }
    
    function load() {
        if($this->filepath != null) {
            $this->file = file_get_contents($this->filepath);
            if($this->debug) {
                echo '<p>Template Found</p>';
            }
        }
        else {
            if($this->debug) {
                echo '<p>Template not Found</p>';
            }
        }
    }
    
    function repl($from, $to) {
        $this->file = str_replace('{'.$from.'}', $to, $this->file);
        return $this->file;
    }

    function replif($key, $boolean) {
        $this->file = str_replace('{'.$from.'}', $to, $this->file);
        if($boolean === true) {
            $this->file = preg_replace("/\{".$key."\}(.*)\\{\/".$key."\}/Uis", '\\1', $this->file);
        }
        else {
            $this->file = preg_replace("/\{".$key."\}(.*)\\{\/".$key."\}/Uis", null, $this->file);
        }
        return $this->file;
    }

    function rplSession() {
        global $_SESSION;
        if(isset($_SESSION['id'])) {
            $this->file = preg_replace("/\{issetS\}(.*)\\{\/issetS\}/Uis", '\\1', $this->file);
            $this->file = preg_replace("/\{!issetS\}(.*)\\{\/!issetS\}/Uis", null, $this->file);
            // -----------1------------
            for($i=0;$i<10;$i++) {
                if($_SESSION['rank'] > $i) {
                    $this->file = preg_replace("/\{rank".$i."\}(.*)\\{\/rank".$i."\}/Uis", '\\1', $this->file);
                    $this->file = preg_replace("/\{!rank".$i."\}(.*)\\{\/!rank".$i."\}/Uis", null, $this->file);
                }
                else {
                    $this->file = preg_replace("/\{rank".$i."\}(.*)\\{\/rank".$i."\}/Uis", null, $this->file);
                    $this->file = preg_replace("/\{!rank".$i."\}(.*)\\{\/!rank".$i."\}/Uis", '\\1', $this->file);
                }
            }
        }
        else {
            $this->file = preg_replace("/\{issetS\}(.*)\\{\/issetS\}/Uis", null, $this->file);
            $this->file = preg_replace("/\{!issetS\}(.*)\\{\/!issetS\}/Uis", '\\1', $this->file);
        }
        return $this->file;
    }

    function loadin() {
        return $this->file;
    }

    function display() {
        echo $this->file;
    }
}

?>