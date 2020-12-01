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
 * Class server
 */
class server extends Rcon {

    private $serverfound;
    private $cfg;
    private $ini;

    public $loadedcluster = false;
    public $serv;
    public $iniext;
    public $inipath;
    public $inistr;
    public $cluster_data;

    /**
     * server constructor.
     *
     * @param String $serv Konfigname des Servers
     */
    public function __construct(String $serv) {
        $this->serv = $serv;
        if (file_exists(__ADIR__.'/remote/arkmanager/instances/'.$serv.'.cfg')) {
            $this->cfg = parse_ini_file(__ADIR__.'/remote/arkmanager/instances/'.$serv.'.cfg');
            $this->serverfound = true;
            return TRUE;
        } else {
            $this->serverfound = false;
            return FALSE;
        }
    }

    /**
     * Gibt den Konfignamen des Servers wieder
     *
     * @return String
     */
    public function name() {
        return $this->serv;
    }

    /**
     * Gibt aus ob der Server installiert ist
     *
     * @param bool $bool gibt an ob als String (false) oder als Bool (true) ausgegeben werden soll
     * @return bool|string
     */
    public function isinstalled(bool $bool = false) {
        $dir = $this->cfg_read('arkserverroot');
        $dir = str_replace('/data/ark_serv_dir/', __ADIR__.'/remoteserv/', $dir);
        $dir = $dir.'/ShooterGame/Binaries/Linux/ShooterGameServer';
        if (file_exists($dir)) {
            if ($bool) return true;
            return 'TRUE';
        } else {
            if ($bool) return false;
            return 'FALSE';
        }
    }

    /**
     * Gibt die hauptverzeichnis des Servers wieder
     *
     * @return string
     */
    public function dir_main() {
        global $servlocdir;
        
        $dir = $this->cfg_read('arkserverroot');
        $path = str_replace($servlocdir, __ADIR__."/remote/serv/", $dir);

        return $path;
    }

    /**
     * Gibts das Verzeichnis der Clusterdateien wieder
     *
     * @return string
     */
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

    /**
     * Gibt das Verzeichnis der Backups wieder
     *
     * @return string
     */
    public function dir_backup() {
        global $servlocdir;

        $dir = $this->cfg_read('arkbackupdir');
        $path = str_replace($servlocdir, __ADIR__."/remote/serv/", $dir);

        return $path;
    }

    /**
     * Gibt das Verzeichnis der Spielstände
     *
     * @param bool $getmaindir Hauptverzeichnis der Speicherdateien (true) oder Unterverzeichnis der Speicherdateien (false)
     * @param bool $getmaindir Verwende $ROOT statt __ADIR__
     * @return string
     */
    public function dir_save(bool $getmaindir = false, bool $getROOTDIR = false) {
        global $ROOT;

        $path = $this->dir_main();
        if($getROOTDIR) $path = str_replace(__ADIR__, $ROOT, $path);
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

    /**
     * Gibt das Verzeichnis der Konfig wieder
     *
     * @return string
     */
    public function dir_konfig() {

        if ($this->cfg_read('ark_AltSaveDirectoryName') != "" && $this->cfg_read('ark_AltSaveDirectoryName') != " ") {
            $path = $this->dir_save()."/../Config/LinuxServer/";
        } else {
            $path = $this->dir_save()."/Config/LinuxServer/";
        }

        return $path;
    }

    /**
     * Sendet eine Aktion an die MYSQL Datenbank um diese dann im ArkAdmin-Server zu verarbeiten
     *
     * @param String $shell
     * @param bool $force Definiert ob dieser Befehl erzwingen wird (überspringt die prüfung ob eine Aktion derzeit läuft)
     * @return bool
     */
    public function send_action(String $shell, bool $force = false) {
        global $mycon;
        global $helper;

        if ($this->status()->next == 'TRUE' && !$force) {
            return false;
        }

        $log = __ADIR__.'/app/data/shell_resp/log/'.$this->name().'/last.log';
        $doc_state_file = __ADIR__.'/app/data/shell_resp/state/'.$this->name().'.state';
        $command = 'arkmanager '.$shell.' @'.$this->name().' > '.$log.' ; echo "TRUE" > '.$doc_state_file.' ; echo "<b>Done...</b>" >> '.$log.' ; exit';
        $command = str_replace("\r", null, $command);

        // Füge Kommand zur DB hinzu
        $query = "INSERT INTO `ArkAdmin_shell` 
        (
            `server`, 
            `command`
        ) VALUES ( 
            '".$this->name()."',
            'screen -dm bash -c \'".$command."\''
        )";

        if ($mycon->query($query)) {
            file_put_contents($doc_state_file, "FALSE");

            $path = __ADIR__."/app/json/serverinfo/" . $this->name() . ".json";
            $data = $helper->fileToJson($path);
            $data["next"] = 'TRUE';
            $helper->saveFile($data, $path);

            return true;
        }
        return false;
    }

    /**
     * Gibt die Arkmanager.cfg des Server als String aus
     *
     * @return false|string
     */
    public function cfg_get_str() {
        return file_get_contents(__ADIR__.'/remote/arkmanager/instances/'.$this->serv.'.cfg');
    }

    /**
     * Gibt die Arkmanager.cfg des Server als Array aus
     *
     * @return array|false
     */
    public function cfg_get() {
        return $this->cfg;
    }

    /**
     * Prüft ob eine Optioen ($key) in der Konfig gesetzt ist
     *
     * @param String $key
     * @return bool
     */
    public function cfg_check(String $key) {
        if (isset($this->cfg[$key])) {
            return true;
        }
        return false;
    }

    /**
     * Liest ein werte einer Option aus der CFG
     *
     * @param String $key
     * @return mixed|null
     */
    public function cfg_read(String $key) {
        return ($this->cfg_check($key)) ? $this->cfg[$key] : null;
    }

    /**
     * Schreibt ein werte einer Option aus der CFG
     *
     * @param String $key
     * @param String $value
     * @return array|false
     */
    public function cfg_write(String $key, String $value) {
        $this->cfg[$key] = $value;
        return $this->cfg;
    }

    /**
     * Entfernt ein werte einer Option aus der CFG
     *
     * @param String $key
     * @return array|false
     */
    public function cfg_remove(String $key) {
        if (isset($this->cfg[$key])) unset($this->cfg[$key]);
        return $this->cfg;
    }

    /**
     * Speichert die CFG ab
     *
     * @return bool
     */
    public function cfg_save() {

        if ($this->cfg_check("arkserverroot") && $this->cfg_check("logdir") && $this->cfg_check("arkbackupdir")) {
            $this->write_ini_file($this->cfg, __ADIR__.'/remote/arkmanager/instances/'.$this->serv.'.cfg');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gibt an ob Mods mit der einstellung unterstützt werden
     *
     * @return bool
     */
    public function mod_support() {
        return (!$this->cfg_check("arkflag_crossplay") && !$this->cfg_check("arkflag_epiconly"));
    }

    /**
     * Gibt den Statuscode des Server an
     * - 0 = Offline
     * - 1 = Startet
     * - 2 = Online
     * - 3 = Nicht Installiert
     *
     * @return int
     */
    public function statecode() {
        global $helper;

        $path = __ADIR__."/app/json/serverinfo/" . $this->name() . ".json";
        $data = $helper->fileToJson($path);

        $serverstate = 0;
        if ($this->isinstalled() == "FALSE") {
            $serverstate = 3;
        }
        elseif ($data["listening"] == "Yes" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            $serverstate = 2;
        }
        elseif ($data["listening"] == "No" && $data["online"] == "No" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        elseif ($data["listening"] == "Yes" && $data["online"] == "No" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        elseif ($data["listening"] == "No" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            $serverstate = 1;
        }
        return $serverstate;
    }

    /**
     * Gibt die daten aus der server.json zurück
     *
     * @return data_server
     */
    public function status() {
        global $helper;

        $path = __ADIR__."/app/json/serverinfo/" . $this->name() . ".json";
        $data = $helper->fileToJson($path);
        $class = new data_server();

        $class->aplayers = isset($data["aplayersarr"]) ? (is_countable($data["aplayersarr"]) ? count($data["aplayersarr"]) : 0) : 0;
        $class->players = isset($data["players"]) ? $data["players"] : "";
        $class->listening = isset($data["listening"]) ? $data["listening"] : "";
        $class->online = isset($data["online"]) ? $data["online"] : "";
        $class->cfg = isset($data["cfg"]) ? $data["cfg"] : "";
        $class->ServerMap = isset($data["ServerMap"]) ? $data["ServerMap"] : "";
        $class->ServerName = isset($data["ServerName"]) ? $data["ServerName"] : "";
        $class->ARKServers = isset($data["ARKServers"]) ? $data["ARKServers"] : "";
        $class->connect = isset($data["connect"]) ? $data["connect"] : "";
        $class->run = isset($data["run"]) ? $data["run"] : "";
        $class->pid = isset($data["pid"]) ? $data["pid"] : "";
        $class->aplayersarr = isset($data["aplayersarr"]) ? $data["aplayersarr"] : [];
        $class->version = isset($data["version"]) ? $data["version"] : "";

        return $class;
    }

    // Inis

    /**
     * Läd eine bestimmte ini in die Klasse
     *
     * @param String $ini
     * @param bool $group
     * @return bool
     */
    public function ini_load(String $ini, bool $group = false) {

        $path = $this->dir_main();
        $dir = $path.'/ShooterGame/Saved/Config/LinuxServer/'.$ini;

        if (file_exists($dir) && fileperms($dir)) {
            // Lesen und Codieren
            $ini_str = file_get_contents($dir);
            if(!preg_match('//u', $ini_str)) $ini_str = mb_convert_encoding($ini_str,'UTF-8', 'UCS-2LE');
            $ini_str = str_replace(" ", null, $ini_str);
            file_put_contents("$path/ShooterGame/Saved/Config/LinuxServer/conv_$ini", $ini_str);

            // Schreiben
            $this->ini = parse_ini_string($ini_str, $group, INI_SCANNER_RAW);
            $this->iniext = extend_parse_ini("$path/ShooterGame/Saved/Config/LinuxServer/conv_$ini");
            $this->inipath = $dir;
            $this->inistr = $ini_str;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Gibt die geladene ini als String wieder
     *
     * @return false|string
     */
    public function ini_get_str() {
        /*$INI_STRING = null;
        $FIRST = false;
        foreach ($this->ini_get() as $key => $item){
            $INI_STRING .= !$FIRST ? "[$key]\n" : "\n[$key]\n" ;
            $FIRST = true;
            foreach ($item as $KEY => $ITEM){
                if(is_array($ITEM)) {
                    foreach ($ITEM as $KEY2 => $ITEM2){
                        if(!is_array($ITEM2)) {
                            $INI_STRING .= $KEY."[$KEY2]=$ITEM2\n";
                        }
                    }
                }
                else {
                    $INI_STRING .= "$KEY=".(is_bool($ITEM) ? ($ITEM ? "True" : "False") : $ITEM)."\n";
                }
            }
        }*/
        return $this->inistr;
    }

    /**
     * Gibt den gesamten Pfad zur Konfig (ini) zurück
     *
     * @return mixed
     */
    public function ini_get_path() {
        return $this->inipath;
    }

    /**
     * Gibt die ini als Array aus
     *
     * @return mixed
     */
    public function ini_get() {
        return $this->ini;
    }

    /**
     * liest einen bestimmten Wert aus der Ini
     *
     * @param $key
     * @return mixed
     */
    public function ini_read($key) {
        return ($this->ini_isset($key)) ? $this->ini[$key] : false;
    }

    /**
     * Prüft ob eine Option gesetzt ist
     *
     * @param $key
     * @return bool
     */
    public function ini_isset($key) {
        return isset($this->ini[$key]);
    }

    /**
     * Schreibt einen bestimmten Wert in der Ini um
     *
     * @param $key
     * @param $value
     * @return array|false
     */
    public function ini_write($key, $value) {
        $this->ini[$key] = $value;
        return $this->cfg;
    }

    /**
     * Löscht einen bestimmten Wert aus der Ini
     *
     * @param $key
     * @return bool
     */
    public function ini_remove($key) {
        $bool = isset($this->ini[$key]);
        if($bool) unset($this->ini[$key]);
        return $bool;
    }

    /**
     * Speichert die werte in die Ini
     *
     * @return bool
     */
    public function ini_save()
    {
        $this->safefilerewrite($this->inipath, $this->ini);
        return true;
    }

    //Cluster

    /**
     * Läd Cluster Daten
     */
    public function cluster_load() {
        global $helper;
        $clusterjson_path = __ADIR__."/app/json/panel/cluster_data.json";
        $infos["in"] = false;
        if (file_exists($clusterjson_path)) {
            $json = $helper->fileToJson($clusterjson_path);
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

    /**
     * Gibt gesamten Daten zum Cluster als Array wieder
     *
     * @return mixed
     */
    public function cluster_array() {
        if ($this->loadedcluster) return $this->cluster_data;
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
    }

    /**
     * Gibt aus ob der Server in einem Cluster ist
     *
     * @return mixed
     */
    public function cluster_in() {
        if ($this->loadedcluster) return $this->cluster_data["in"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
    }

    /**
     * Gibt die Cluster ID wieder
     *
     * @return mixed|string
     */
    public function cluster_clusterid() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["clusterid"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    /**
     * Gibt den namen des Clusters wieder
     *
     * @return mixed|string
     */
    public function cluster_name() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["name"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::notincluster}";
    }

    /**
     * Gibt den Cluster Schlüssel wieder
     *
     * @return mixed|string
     */
    public function cluster_key() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["key"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    /**
     * Gibt aus ob der Cluster Mods Syncronisiert
     *
     * @return mixed|string
     */
    public function cluster_mods() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["mods"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }


    /**
     * Gibt aus ob der Cluster Konfigs Syncronisiert
     *
     * @return mixed|string
     */
    public function cluster_konfig() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["konfig"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }


    /**
     * Gibt aus ob der Cluster Admins Syncronisiert
     *
     * @return mixed|string
     */
    public function cluster_admin() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["admin"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }


    /**
     * Gibt aus ob der Cluster Whitelisten Syncronisiert
     *
     * @return mixed|string
     */
    public function cluster_whitelist() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return (isset($this->cluster_data["whitelist"])) ? $this->cluster_data["whitelist"] : false;
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    /**
     * Gibt aus ob der server ein "Slave" oder "Master" ist
     *
     * @return mixed|string
     */
    public function cluster_type() {
        if ($this->loadedcluster && $this->cluster_data["in"]) return $this->cluster_data["type"];
        if (!$this->loadedcluster) echo "{::lang::php::class::clusternotload}";
        if (!$this->cluster_data["in"]) return "{::lang::php::class::nocluster}";
    }

    // RCON Funktionen

    /**
     * Prüft ob der Server eine RCON verbindung überhaupt aufbauen könnte
     *
     * @return bool
     */
    public function check_rcon() {
        return ($this->statecode() == 2 && $this->cfg_read('ark_RCONEnabled') == 'True' && $this->cfg_read('ark_ServerAdminPassword') != '');
    }

    /**
     * Führt einen RCON command aus
     *
     * @param String $commmand
     * @return int
     */
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

    /**
     * Schreibe eine ini (Wandelt wieder in einen String um und ersetzte falsche Zeichen)
     *
     * @param array $array
     * @param String $file
     */
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

    /**
     * @param String $fileName
     * @param String $dataToSave
     */
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

/**
 * Class data_server (Erweiterung für Class server)
 */
class data_server {
    public $aplayersarr;
    public $ServerName;
    public $error;
    public $online;
    public $aplayers;
    public $players;
    public $pid;
    public $run;
    public $listening;
    public $cfg;
    public $ARKServers;
    public $next;
    public $version;
    public $connect;
}

