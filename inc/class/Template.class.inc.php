<?php 

class Template {
    
    private $filepath;
    private $file = null;
    private $debug = false;
    private $load = false;
    
    function __construct($file, $path) {
        if(file_exists($path.$file)) {
            $this->filepath = $path.$file;
        }
        else {
            $this->filepath = null;
        }
    }

    function debug($bool) {
        $this->debug = $bool;
    }
    
    function load() {
        if($this->filepath != null) {
            $this->file = file_get_contents($this->filepath);
            $this->load = true;
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
        if(!$this->load) {
            echo "Template not Loaded";
            return null;
        }
        $this->file = str_replace('{'.$from.'}', $to, $this->file);
    }

    function replif($key, $boolean) {
        if(!$this->load) {
            echo "Template not Loaded";
            return null;
        }
        $this->file = str_replace('{'.$from.'}', $to, $this->file);
        if($boolean === true) {
            $this->file = preg_replace("/\{".$key."\}(.*)\\{\/".$key."\}/Uis", '\\1', $this->file);
            $this->file = preg_replace("/\{!".$key."\}(.*)\\{\/!".$key."\}/Uis", null, $this->file);
        }
        else {
            $this->file = preg_replace("/\{".$key."\}(.*)\\{\/".$key."\}/Uis", null, $this->file);
            $this->file = preg_replace("/\{!".$key."\}(.*)\\{\/!".$key."\}/Uis", '\\1', $this->file);
        }
    }

    function rplSession() {
        if(!$this->load) {
            echo "Template not Loaded";
            return null;
        }
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
    }

    function loadin() {
        if($this->load) {
            return $this->file;
        }
        else {
            echo "Template not Loaded";
        }
    }

    function display() {
        if($this->load) {
            echo $this->file;
        }
        else {
            echo "Template not Loaded";
        }
    }
}

?>