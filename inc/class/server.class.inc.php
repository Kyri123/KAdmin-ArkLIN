<?php

class server {

    private $serv = null;
    private $cfg = null;
    private $ini = null;
    private $inipath = null;

    public function __construct($serv) {
        $this->serv = $serv;
        if(file_exists('remote/arkmanager/instances/'.$serv.'.cfg')) {
            $this->cfg = parse_ini_file('remote/arkmanager/instances/'.$serv.'.cfg');
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    public function show_name() {
        return $this->serv;
    }

    public function check_install() {
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

    public function get_dir() {

        $dir = $this->cfg_read('arkserverroot');
        $exp = explode('/', $dir);

        for($i=0;$i<count($exp);$i++) {
            if(strpos($exp[$i], $this->serv)) {
                $path = 'remote/serv/'.$exp[$i];
                break;
            }
        }

        return $path;
    }

    public function get_backup_dir() {

        $dir = $this->cfg_read('arkbackupdir');
        $exp = explode('/', $dir);

        for($i=0;$i<count($exp);$i++) {
            if(strpos($exp[$i], $this->serv)) {
                $path = 'remote/serv/'.$exp[$i];
                break;
            }
        }

        return $path;
    }

    public function get_save_dir() {

        $path = $this->get_dir();

        if($this->cfg_read('ark_AltSaveDirectoryName') != "" && $this->cfg_read('ark_AltSaveDirectoryName') != " ") {
            $path = $path."/ShooterGame/Saved/".$this->cfg['ark_AltSaveDirectoryName'];
        }
        else {
            $path = $path."/ShooterGame/Saved";
        }

        return $path;
    }

    public function get_konfig_dir() {

        if($this->cfg_read('ark_AltSaveDirectoryName') != "" && $this->cfg_read('ark_AltSaveDirectoryName') != " ") {
            $path = $this->get_save_dir()."/../Config/LinuxServer/";
        }
        else {
            $path = $this->get_save_dir()."/Config/LinuxServer/";
        }

        return $path;
    }

    public function ini_load($ini, $group) {

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

    public function ini_get_str() {
        return file_get_contents($this->inipath);
    }

    public function ini_get_path() {
        return $this->inipath;
    }

    public function cfg_get_str() {
        return file_get_contents('remote/arkmanager/instances/'.$this->serv.'.cfg');
    }

    function cfg_get() {
        return $this->cfg;
    }

    public function cfg_read($key) {
        return $this->cfg[$key];
    }

    public function cfg_write($key, $value) {
        $this->cfg[$key] = $value;
        return $this->cfg;
    }

    public function cfg_save() {

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
                do {
                    $canWrite = flock($fp, LOCK_EX);
                    if(!$canWrite) usleep(round(rand(0, 100)*1000));
                } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

                if ($canWrite) {
                    fwrite($fp, $dataToSave);
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }

        }
    }

    public function ini_get() {
        return $this->ini;
    }

    public function get_job_path() {
        return 'sh/serv/sub_jobs_ID_' . $this->show_name() . '.sh';
    }

    public function get_job_file() {
        $str = file_get_contents('sh/serv/sub_jobs_ID_' . $this->show_name() . '.sh');
        return $str;
    }

    public function write_job_file($str) {
        if(file_put_contents('sh/serv/sub_jobs_ID_' . $this->show_name() . '.sh', $str)) {
            return true;
        }
        else {
            return false;
        }
    }

    public function get_state() {
        global $helper;

        $path = "data/serv/" . $this->show_name() . ".json";
        $data = $helper->file_to_json($path);

        $serverstate = 0;
        if($this->check_install() == "FALSE") {
            $serverstate = 3;
        }
        elseif($data["listening"] == "Yes" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            $serverstate = 2;
        }
        elseif($data["listening"] == "No" && $data["online"] == "NO" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        elseif($data["listening"] == "Yes" && $data["online"] == "NO" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        elseif($data["listening"] == "No" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        return $serverstate;
    }

    public function readdata() {
    global $helper;

    $path = "data/serv/" . $this->show_name() . ".json";
    $data = $helper->file_to_json($path);
    $class = new data_server();

    $class->warning_count = $data["warning_count"];
    $class->error_count = $data["error_count"];
    $class->error = $data["error"];
    $class->warning = $data["warning"];
    $class->online = $data["online"];
    $class->aplayers = $data["aplayers"];
    $class->players = $data["players"];
    $class->pid = $data["pid"];
    $class->run = $data["run"];
    $class->listening = $data["listening"];
    $class->installed = $data["installed"];
    $class->cfg = $data["cfg"];
    $class->bid = $data["bid"];
    $class->ARKServers = $data["ARKServers"];
    $class->next = $data["next"];
    $class->ServerName = $data["ServerName"];
    $class->version = $data["version"];
    $class->connect = $data["connect"];

    return $class;
}

    public function ini_read($key) {
        return $this->ini[$key];
    }

    public function ini_write($key, $value) {
        $this->ini[$key] = $value;
        return $this->cfg;
    }

    public function ini_save() {
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
                do {
                    $canWrite = flock($fp, LOCK_EX);
                    if(!$canWrite) usleep(round(rand(0, 100)*1000));
                } while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));

                if ($canWrite) {
                    fwrite($fp, $dataToSave);
                    flock($fp, LOCK_UN);
                }
                fclose($fp);
            }

        }
    }





}


class data_server {
    public $warning_count;
    public $error_count;
    public $error;
    public $warning;
    public $online;
    public $aplayers;
    public $players;
    public $pid;
    public $run;
    public $listening;
    public $installed;
    public $cfg;
    public $bid;
    public $ARKServers;
    public $next;
    public $ServerName;
    public $version;
    public $connect;
}

?>