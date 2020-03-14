<?php

class server {

    private $serv = null;
    private $cfg = null;
    private $ini = null;
    private $inipath = null;

    function __construct($serv) {
        $this->serv = $serv;
        if(file_exists('remote/arkmanager/instances/'.$serv.'.cfg')) {
            $this->cfg = parse_ini_file('remote/arkmanager/instances/'.$serv.'.cfg');
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    function show_name() {
        return $this->serv;
    }

    function check_install() {
        $dir = $this->cfg_read('arkserverroot');
        $dir = str_replace('/data/ark_serv_dir/', 'remote/serv/', $dir);
        $dir = $dir.'/ShooterGame/Binaries/Linux/ShooterGameServer';
        if(file_exists($dir)) {
            return 'TRUE';
        }
        else {
            return 'FALSE';
        }
    }

    function get_dir() {

        $dir = $this->cfg_read('arkserverroot');
        $exp = explode('/', $dir);

        for($i=0;$i<count($exp);$i++) {
            if(strpos($exp[$i], $this->serv) !== false) {
                $path = 'remote/serv/'.$exp[$i];
                break;
            }
        }

        return $path;
    }

    function get_save_dir() {

        $path = $this->get_dir();

        if($this->cfg_read('ark_AltSaveDirectoryName') != "" AND $this->cfg_read('ark_AltSaveDirectoryName') != " ") {
            $path = $path."/ShooterGame/Saved/".$this->cfg['ark_AltSaveDirectoryName'];
        }
        else {
            $path = $path."/ShooterGame/Saved";
        }

        return $path;
    }

    function ini_load($ini, $group) {

        $path = $this->get_dir();
        $dir = $path.'/ShooterGame/Saved/Config/LinuxServer/'.$ini;
        if(file_exists($dir)) {
            $this->ini = parse_ini_file($dir, $group);
            $this->inipath = $dir;
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    function ini_get_str() {
        return file_get_contents($this->inipath);
    }

    function ini_get_path() {
        return $this->inipath;
    }

    function cfg_get_str() {
        return file_get_contents('remote/arkmanager/instances/'.$this->serv.'.cfg');
    }

    function cfg_get() {
        return $this->cfg;
    }

    function cfg_read($key) {
        return $this->cfg[$key];
    }

    function cfg_write($key, $value) {
        $this->cfg[$key] = $value;
        return $this->cfg;
    }

    function cfg_save() {

        write_ini_file($this->cfg, 'remote/arkmanager/instances/'.$this->serv.'.cfg');
        return true;

        function write_ini_file($array, $file) {
            $res = array();
            foreach($array as $key => $val) {
                if(is_array($val)) {
                    $res[] = "[$key]";
                    foreach($val as $skey => $sval) $res[] = $skey."=".(is_numeric($sval) ? $sval : '"'.$sval.'"');
                }
                else $res[] = $key."=".(is_numeric($val) ? $val : '"'.$val.'"');
            }
            safefilerewrite($file, implode("\n", $res));
        }
        function safefilerewrite($fileName, $dataToSave) {
            if ($fp = fopen($fileName, 'w')) {
                $startTime = microtime(TRUE);
                do {            $canWrite = flock($fp, LOCK_EX);
                    // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                    if(!$canWrite) usleep(round(rand(0, 100)*1000));
                } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

                //file was locked so now we can store information
                if ($canWrite) {            fwrite($fp, $dataToSave);
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }

        }
    }

    function ini_get() {
        return $this->ini;
    }

    function ini_read($key) {
        return $this->ini[$key];
    }

    function ini_write($key, $value) {
        $this->ini[$key] = $value;
        return $this->cfg;
    }

    function ini_save() {
        safefilerewrite($this->inipath, $this->ini);
        return true;

        function write_ini_file($array, $file) {
            $res = array();
            foreach($array as $key => $val) {
                if(is_array($val)) {
                    $res[] = "[$key]";
                    foreach($val as $skey => $sval) $res[] = $skey."=".(is_numeric($sval) ? $sval : '"'.$sval.'"');
                }
                else $res[] = $key."=".(is_numeric($val) ? $val : '"'.$val.'"');
            }
            safefilerewrite($file, implode("\n", $res));
        }
        function safefilerewrite($fileName, $dataToSave) {
            if ($fp = fopen($fileName, 'w')) {
                $startTime = microtime(TRUE);
                do {            $canWrite = flock($fp, LOCK_EX);
                    // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                    if(!$canWrite) usleep(round(rand(0, 100)*1000));
                } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

                //file was locked so now we can store information
                if ($canWrite) {            fwrite($fp, $dataToSave);
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }

        }
    }





}

?>