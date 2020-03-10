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

    function rplSession() {
        global $_SESSION;
        if(isset($_SESSION['id'])) {
            $this->file = preg_replace("/\{issetS\}(.*)\\{\/issetS\}/Uis", '\\1', $this->file);
            $this->file = preg_replace("/\{!issetS\}(.*)\\{\/!issetS\}/Uis", null, $this->file);
            // -----------1------------
            if($_SESSION['rank'] > 0) {
                $this->file = preg_replace("/\{rank1\}(.*)\\{\/rank1\}/Uis", '\\1', $this->file);
                $this->file = preg_replace("/\{!rank1\}(.*)\\{\/!rank1\}/Uis", null, $this->file);
            }
            else {
                $this->file = preg_replace("/\{rank1\}(.*)\\{\/rank1\}/Uis", null, $this->file);
                $this->file = preg_replace("/\{!rank1\}(.*)\\{\/!rank1\}/Uis", '\\1', $this->file);
            }
            // -----------2------------
            if($_SESSION['rank'] > 1) {
                $this->file = preg_replace("/\{rank2\}(.*)\\{\/rank2\}/Uis", '\\1', $this->file);
                $this->file = preg_replace("/\{!rank2\}(.*)\\{\/!rank2\}/Uis", null, $this->file);
            }
            else {
                $this->file = preg_replace("/\{rank2\}(.*)\\{\/rank2\}/Uis", null, $this->file);
                $this->file = preg_replace("/\{!rank2\}(.*)\\{\/!rank2\}/Uis", '\\1', $this->file);
            }
            // -----------3------------
            if($_SESSION['rank'] > 2) {
                $this->file = preg_replace("/\{rank3\}(.*)\\{\/rank3\}/Uis", '\\1', $this->file);
                $this->file = preg_replace("/\{!rank3\}(.*)\\{\/!rank3\}/Uis", null, $this->file);
            }
            else {
                $this->file = preg_replace("/\{rank3\}(.*)\\{\/rank3\}/Uis", null, $this->file);
                $this->file = preg_replace("/\{!rank3\}(.*)\\{\/!rank3\}/Uis", '\\1', $this->file);
            }
            // -----------4------------
            if($_SESSION['rank'] > 3) {
                $this->file = preg_replace("/\{rank4\}(.*)\\{\/rank4\}/Uis", '\\1', $this->file);
                $this->file = preg_replace("/\{!rank4\}(.*)\\{\/!rank4\}/Uis", null, $this->file);
            }
            else {
                $this->file = preg_replace("/\{rank4\}(.*)\\{\/rank4\}/Uis", null, $this->file);
                $this->file = preg_replace("/\{!rank4\}(.*)\\{\/!rank4\}/Uis", '\\1', $this->file);
            }
            // -----------5------------
            if($_SESSION['rank'] > 4) {
                $this->file = preg_replace("/\{rank5\}(.*)\\{\/rank5\}/Uis", '\\1', $this->file);
                $this->file = preg_replace("/\{!rank5\}(.*)\\{\/!rank5\}/Uis", null, $this->file);
            }
            else {
                $this->file = preg_replace("/\{rank5\}(.*)\\{\/rank5\}/Uis", null, $this->file);
                $this->file = preg_replace("/\{!rank5\}(.*)\\{\/!rank5\}/Uis", '\\1', $this->file);
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