<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

class server extends Rcon {

    private $serv;
    private $serverfound;
    private $cfg;
    private $ini;
    private $inipath;
    private $loadedcluster = false;
    public $cluster_data;

    public function __construct(String $serv) {
        $this->serv = $serv;
        if (file_exists('remote/arkmanager/instances/'.$serv.'.cfg')) {
            $this->cfg = parse_ini_file('remote/arkmanager/instances/'.$serv.'.cfg');
            $this->serverfound = true;
            return TRUE;
        } else {
            $this->serverfound = false;
            return FALSE;
        }
    }

    // Gibt cfg namen wieder
    public function name() {
        return $this->serv;
    }

    // Prüfe ob der Server installiert ist
    public function isinstalled(bool $bool = false) {
        $dir = $this->cfg_read('arkserverroot');
        $dir = str_replace('/data/ark_serv_dir/', 'remote/serv/', $dir);
        $dir = $dir.'/ShooterGame/Binaries/Linux/ShooterGameServer';
        if (file_exists($dir)) {
            if ($bool) return false;
            return 'TRUE';
        } else {
            if ($bool) return false;
            return 'FALSE';
        }
    }

    // Bekomme Main Dir
    public function dir_main() {
        global $servlocdir;
        
        $dir = $this->cfg_read('arkserverroot');
        $path = str_replace($servlocdir, "remote/serv/", $dir);

        return $path;
    }

    // Bekomme Cluster Dir
    public function dir_cluster() {

        $dir = $this->cfg_read('arkserverroot');
        $exp = explode('/', $dir);
        $dirp = null;

        for ($i=0;$i<count($exp);$i++) {
            if (strpos($exp[$i], $this->serv)) {
                break;
            }
            $dirp .= $exp[$i]."/";
        }

        return $dirp.'cluster/';
    }

    // Bekomme Backup dir
    public function dir_backup() {
        global $servlocdir;

        $dir = $this->cfg_read('arkbackupdir');
        $path = str_replace($servlocdir, "remote/serv/", $dir);

        return $path;
    }

    // Bekomme Save Dir
    public function dir_save(bool $getmaindir = false) {

        $path = $this->dir_main();
        if ($getmaindir) {
            $path = $path."/ShooterGame/Saved";
        }
        elseif ($this->cfg_read('ark_AltSaveDirectoryName') != "" && $this->cfg_read('ark_AltSaveDirectoryName') != " ") {
            $path = $path."/ShooterGame/Saved/".$this->cfg['ark_AltSaveDirectoryName'];
        } else {
            $path = $path."/ShooterGame/Saved";
        }

        return $path;
    }

    // Bekomme Konfig Dir
    public function dir_konfig() {

        if ($this->cfg_read('ark_AltSaveDirectoryName') != "" && $this->cfg_read('ark_AltSaveDirectoryName') != " ") {
            $path = $this->dir_save()."/../Config/LinuxServer/";
        } else {
            $path = $this->dir_save()."/Config/LinuxServer/";
        }

        return $path;
    }

    // Erstelle Shell mit Log
    public function send_action(String $shell, bool $force = false) {
        if ($this->status()->next == 'TRUE' && !$force) {
            return false;
        }
        $doc = $_SERVER['DOCUMENT_ROOT'];
        $log = $doc.'/sh/resp/'.$this->name().'/last.log';
        $doc_state_file = 'sh/serv/jobs_ID_'.$this->name().'.state';
        $doc_state = $doc.'/'.$doc_state_file;
        $doc = $doc.'/sh/serv/sub_jobs_ID_'.$this->name().'.sh';
        $command = 'echo "" > '.$doc.' ; arkmanager '.$shell.' @'.$this->name().' > '.$log.' ; echo "TRUE" > '.$doc_state.' ; echo "<b>Done...</b>" >> '.$log.' ; exit';
        $command = str_replace("\r", null, $command);
        if (file_put_contents($doc_state_file, 'FALSE') && file_put_contents('sh/serv/sub_jobs_ID_'.$this->name().'.sh', $command)) return true;
        return false;
    }

    // Arkmanager.cfg
    public function cfg_get_str() {
        return file_get_contents('remote/arkmanager/instances/'.$this->serv.'.cfg');
    }

    public function cfg_get() {
        return $this->cfg;
    }

    public function cfg_check(String $key) {
        if (isset($this->cfg[$key])) {
            return true;
        }
        return false;
    }

    public function cfg_read(String $key) {
        return $this->cfg[$key];
    }

    public function cfg_write(String $key, String $value) {
        $this->cfg[$key] = $value;
        return $this->cfg;
    }

    public function cfg_remove(String $key) {
        if (isset($this->cfg[$key])) unset($this->cfg[$key]);
        return $this->cfg;
    }

    public function cfg_save() {

        if ($this->cfg_check("arkserverroot") && $this->cfg_check("logdir") && $this->cfg_check("arkbackupdir")) {
            $this->write_ini_file($this->cfg, 'remote/arkmanager/instances/'.$this->serv.'.cfg');
        } else {
            return false;
        }
    }

    public function mod_support() {
        return (!$this->cfg_check("arkflag_crossplay") && !$this->cfg_check("arkflag_epiconly"));
    }

    // Job funktionen
    public function jobs_dir() {
        return 'sh/serv/sub_jobs_ID_' . $this->name() . '.sh';
    }

    public function jobs_file() {
        $str = file_get_contents('sh/serv/sub_jobs_ID_' . $this->name() . '.sh');
        return $str;
    }

    public function jobs_write(String $str) {
        if (file_put_contents('sh/serv/sub_jobs_ID_' . $this->name() . '.sh', $str)) {
            return true;
        } else {
            return false;
        }
    }

    // Bekomme Statuscode
    public function statecode() {
        global $helper;

        $path = "app/json/serverinfo/" . $this->name() . ".json";
        $data = $helper->file_to_json($path);

        $serverstate = 0;
        if ($this->isinstalled() == "FALSE") {
            $serverstate = 3;
        }
        elseif ($data["listening"] == "Yes" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            $serverstate = 2;
        }
        elseif ($data["listening"] == "No" && $data["online"] == "NO" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        elseif ($data["listening"] == "Yes" && $data["online"] == "NO" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        elseif ($data["listening"] == "No" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        return $serverstate;
    }

    // Daten aus dem Arkmanager
    public function status() {
        global $helper;

        $path = "app/json/serverinfo/" . $this->name() . ".json";
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

    // Inis

    public function ini_load(String $ini, bool $group = false) {

        $path = $this->dir_main();
        $dir = $path.'/ShooterGame/Saved/Config/LinuxServer/'.$ini;
        if (file_exists($dir)) {
            $this->ini = parse_ini_file($dir, $group, INI_SCANNER_RAW);
            $this->inipath = $dir;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function ini_get_str() {
        return file_get_contents($this->inipath);
    }

    public function ini_get_path() {
        return $this->inipath;
    }

    public function ini_get() {
        return $this->ini;
    }

    public function ini_read($key) {
        return $this->ini[$key];
    }

    public function ini_write($key, $value) {
        $this->ini[$key] = $value;
        return $this->cfg;
    }

    public function ini_save()
    {
        $this->safefilerewrite($this->inipath, $this->ini);
        return true;
    }

    //Cluster
    public function cluster_load() {
        global $helper;
        $clusterjson_path = "app/json/panel/cluster_data.json";
        $infos["in"] = false;
        if (file_exists($clusterjson_path)) {
            $json = $helper->file_to_json($clusterjson_path);
            $infos["mods"] = false;
            $infos["konfig"] = false;
            $infos["admin"] = false;
            $infos["type"] = 0;
            foreach ($json as $mk => $mv) {
                if (array_search($this->name(), array_column($mv["servers"], 'server')) !== FALSE) {
                   //var_dump($mv);
                    $infos["in"] = true;
                    $infos["clusterid"] = $mv["clusterid"];
                    $infos["name"] = $mv["name"];
                    $infos["key"] = $mk;
                    $i = array_search($this->name(), array_column($mv["servers"], 'server'));
                    $infos["type"] =$mv["servers"][$i]["type"];
                    $infos["mods"] = $mv["sync"]["mods"];
                    $infos["konfig"] = $mv["sync"]["konfig"];
                    $infos["admin"] = $mv["sync"]["admin"];
                    $infos["whitelist"] = $mv["sync"]["whitelist"];
                }
            }
        }
        $this->loadedcluster = true;
        $this->cluster_data = $infos;
    }

    public function cluster_array() {
        if ($this->loadedcluster) return $this->cluster_data;
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
    }

    public function cluster_in() {
        if ($this->loadedcluster) return $this->cluster_data["in"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
    }

    public function cluster_clusterid() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["clusterid"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    public function cluster_name() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["name"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::notincluster}";
    }

    public function cluster_key() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["key"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    public function cluster_mods() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["mods"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    public function cluster_konfig() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["konfig"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    public function cluster_admin() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["admin"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    public function cluster_whitelist() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return (isset($this->cluster_data["whitelist"])) ? $this->cluster_data["whitelist"] : false;
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    public function cluster_type() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["type"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    // RCON Funktionen
    public function check_rcon() {
        return ($this->status()->online == 'Yes' && $this->cfg_read('ark_RCONEnabled') == 'True' && $this->cfg_read('ark_ServerAdminPassword') != '');
    }

    public function check_cfg_rcon()
    {
        return ($this->cfg_read('ark_RCONEnabled') == 'True' && $this->cfg_read('ark_ServerAdminPassword') != '');
    }

    public function exec_rcon(String $commmand = "") {
        if ($this->check_rcon()) {
            $re = 12;

            //inz RCON
            $ip = $_SERVER['SERVER_ADDR'];
            $port = $this->cfg_read('ark_RCONPort');
            $pw = $this->cfg_read('ark_ServerAdminPassword');
            $rcon = new parent($ip, $port, $pw, 3);

            if ($rcon->connect()) {
                if ($commmand == "") {
                    $re = 2;
                }
                elseif (!$rcon->send_command($commmand)) {
                    $re = 12;
                }
                else {
                    $re = 108;
                }
                $rcon->disconnect();
            }
        } else {
            $re = 12;
        }
        return $re;
    }

    // Private Functions

    private function write_ini_file(Array $array, String $file)
    {
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $skey => $sval) $res[] = $skey . "=" . (is_numeric($sval) ? $sval : '"' . $sval . '"');
            } else $res[] = $key . "=" . (is_numeric($val) ? $val : '"' . $val . '"');
        }
        $this->safefilerewrite($file, implode("\n", $res));
    }

    private function safefilerewrite(String $fileName, String $dataToSave)
    {
        if ($fp = fopen($fileName, 'w')) {
            $startTime = microtime(TRUE);
            do {
                $canWrite = flock($fp, LOCK_EX);
                if (!$canWrite) usleep(round(rand(0, 100) * 1000));
            } while ((!$canWrite) and ((microtime(TRUE) - $startTime) < 5));

            if ($canWrite) {
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
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