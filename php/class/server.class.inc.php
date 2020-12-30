<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

/**
 * Class server
 */
class server extends Rcon {

    private $KUTIL;
    private $serverfound;
    private $cfg;
    private $ini;
    private $serv_cfg_path;

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
        global $KUTIL;
        $this->KUTIL            = $KUTIL;
        $this->serv             = $serv;
        $this->serv_cfg_path    = $this->KUTIL->path(__ADIR__.'/remote/arkmanager/instances/'.$serv.'.cfg')["/path"];

        if (@file_exists($this->serv_cfg_path)) {
            $this->cfg          = parse_ini_file($this->serv_cfg_path);
            $this->serverfound  = true;
            return true;
        } else {
            $this->serverfound  = false;
            return false;
        }
    }

    /**
     * Gibt den Konfignamen des Servers wieder
     *
     * @return String
     */
    public function name(): string
    {
        return $this->serv;
    }

    /**
     * Gibt aus ob der Server installiert ist
     *
     * @param bool $bool gibt an ob als String (false) oder als Bool (true) ausgegeben werden soll
     * @return bool|string
     */
    public function isInstalled(bool $bool = false) {
        return @file_exists($this->KUTIL->path($this->cfgRead('arkserverroot').'/ShooterGame/Binaries/Linux/ShooterGameServer')["/path"])
            ? ($bool    ? true     : 'TRUE' )
            : ($bool    ? false    : 'FALSE');
    }

    /**
     * Gibt die hauptverzeichnis des Servers wieder
     *
     * @return string
     */
    public function dirMain(): string
    {
        return $this->KUTIL->path($this->cfgRead('arkserverroot'))["/path"];
    }

    /**
     * Gibts das Verzeichnis der Clusterdateien wieder
     *
     * @return string
     */
    public function dirCluster(): string
    {
        $dir = $this->cfgRead('arkserverroot');
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
    public function dirBackup(): string
    {
        return $this->KUTIL->path($this->cfgRead('arkbackupdir'))["/path"];
    }

    /**
     * Gibt das Verzeichnis der Spielstände
     *
     * @param bool $getmaindir Verwende $ROOT statt __ADIR__
     * @param bool $getROOTDIR
     * @return string
     */
    public function dirSavegames(bool $getmaindir = false, bool $getROOTDIR = false): string
    {
        global $ROOT;

        $path                   = $this->dirMain();
        if($getROOTDIR) $path   = str_replace(__ADIR__, $ROOT, $path);

        if ($this->cfgRead('ark_AltSaveDirectoryName') != "" && $this->cfgRead('ark_AltSaveDirectoryName') != " " && !$getmaindir) {
            $path   = $path."/ShooterGame/Saved/".$this->cfg['ark_AltSaveDirectoryName'];
        } else {
            $path   = $path."/ShooterGame/Saved";
        }

        return $path;
    }

    /**
     * Gibt das Verzeichnis der Konfig wieder
     *
     * @return string
     */
    public function dirKonfig(): string
    {
        return $this->KUTIL->path($this->dirSavegames(true)."/Config/LinuxServer/")["/path"];
    }

    /**
     * Sendet eine Aktion an die MYSQL Datenbank um diese dann im ArkAdmin-Server zu verarbeiten
     *
     * @param String $shell
     * @param bool $force Definiert ob dieser Befehl erzwingen wird (überspringt die prüfung ob eine Aktion derzeit läuft)
     * @return bool
     */
    public function sendAction(String $shell, bool $force = false) {
        global $mycon, $helper;

        if ($this->status()->next == 'TRUE' && !$force) {
            return false;
        }

        $log                = $this->KUTIL->path(__ADIR__.'/app/data/shell_resp/log/'.$this->name().'/last.log')["/path"];
        $doc_state_file     = $this->KUTIL->path(__ADIR__.'/app/data/shell_resp/state/'.$this->name().'.state')["/path"];
        $command            = 'arkmanager '.$shell.' @'.$this->name().' > '.$log.' ; echo "TRUE" > '.$doc_state_file.' ; echo "<b>Done...</b>" >> '.$log.' ; exit';
        $command            = str_replace("\r", null, $command);

        // Füge Kommand zur DB hinzu
        $query              = "INSERT INTO `ArkAdmin_shell` (`server`, `command`) VALUES (?, 'screen -dm bash -c \'".$command."\'')";

        if ($mycon->query($query, $this->name())) {
            $this->KUTIL->filePutContents($doc_state_file, "FALSE");

            $path               = $this->KUTIL->path(__ADIR__."/app/json/serverinfo/" . $this->name() . ".json")["/path"];
            $data               = $helper->fileToJson($path);
            $data["next"]       = 'TRUE';
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
    public function cfgGetString() {
        return $this->KUTIL->fileGetContents(__ADIR__.'/remote/arkmanager/instances/'.$this->serv.'.cfg');
    }

    /**
     * Gibt die Arkmanager.cfg des Server als Array aus
     *
     * @return array|false
     */
    public function cfgGetArray() {
        return $this->cfg;
    }

    /**
     * Prüft ob eine Optioen ($key) in der Konfig gesetzt ist
     *
     * @param String $key
     * @return bool
     */
    public function cfgKeyExists(String $key): bool
    {
        return isset($this->cfg[$key]);
    }

    /**
     * Liest ein werte einer Option aus der CFG
     *
     * @param String $key
     * @param bool $nfAsBool Nicht gefunden als Bool ausgeben
     * @return mixed|null
     */
    public function cfgRead(String $key, bool $nfAsBool = false) {
        return $this->cfgKeyExists($key) ? $this->cfg[$key] : ($nfAsBool ? false : null);
    }

    /**
     * Schreibt ein werte einer Option aus der CFG
     *
     * @param String $key
     * @param String $value
     * @return boolean
     */
    public function cfgWrite(String $key, String $value): bool
    {
        $this->cfg[$key] = $value;
        return $this->cfg[$key] === $value;
    }

    /**
     * Entfernt ein werte einer Option aus der CFG
     *
     * @param String $key
     * @return boolean
     */
    public function cfgRemove(String $key): bool
    {
        if(isset($this->cfg[$key])) {
            unset($this->cfg[$key]);
            return !isset($this->cfg[$key]);
        }
        else {
            return true;
        }
    }

    /**
     * Speichert die CFG ab
     *
     * @return bool
     */
    public function cfgSave(): bool
    {
        if ($this->cfgKeyExists("arkserverroot") && $this->cfgKeyExists("logdir") && $this->cfgKeyExists("arkbackupdir")) {
            $this->writeIniFile($this->cfg, $this->KUTIL->path(__ADIR__.'/remote/arkmanager/instances/'.$this->serv.'.cfg')["/path"]);
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
    public function modSupport(): bool
    {
        return (!$this->cfgKeyExists("arkflag_crossplay") && !$this->cfgKeyExists("arkflag_epiconly"));
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
    public function stateCode(): int
    {
        global $helper;
        $data = $helper->fileToJson($this->KUTIL->path(__ADIR__."/app/json/serverinfo/" . $this->name() . ".json")["/path"]);

        if ($this->isInstalled() == "FALSE") {
            return 3;
        }
        elseif ($data["listening"] == "Yes" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            return 2;
        }
        elseif ($data["listening"] == "No" && $data["online"] == "No" && $data["run"] == "Yes") {
            return 1;
        }
        elseif ($data["listening"] == "Yes" && $data["online"] == "No" && $data["run"] == "Yes") {
            return 1;
        }
        elseif ($data["listening"] == "No" && $data["online"] == "Yes" && $data["run"] == "Yes") {
            return 1;
        }
        return 0;
    }

    /**
     * Gibt die daten aus der server.json zurück
     *
     * @return data_server
     */
    public function status(): data_server
    {
        global $helper;

        $data                   = $helper->fileToJson($this->KUTIL->path(__ADIR__."/app/json/serverinfo/" . $this->name() . ".json")["/path"]);
        $class                  = new data_server();

        $class->aplayers        = isset($data["aplayersarr"])   ? (is_countable($data["aplayersarr"]) ? count($data["aplayersarr"]) : 0) : 0;
        $class->players         = isset($data["players"])       ? $data["players"]      : "";
        $class->listening       = isset($data["listening"])     ? $data["listening"]    : "";
        $class->online          = isset($data["online"])        ? $data["online"]       : "";
        $class->cfg             = isset($data["cfg"])           ? $data["cfg"]          : "";
        $class->ServerMap       = isset($data["ServerMap"])     ? $data["ServerMap"]    : "";
        $class->ServerName      = isset($data["ServerName"])    ? $data["ServerName"]   : "";
        $class->ARKServers      = isset($data["ARKServers"])    ? $data["ARKServers"]   : "";
        $class->connect         = isset($data["connect"])       ? $data["connect"]      : "";
        $class->run             = isset($data["run"])           ? $data["run"]          : "";
        $class->pid             = isset($data["pid"])           ? $data["pid"]          : "";
        $class->aplayersarr     = isset($data["aplayersarr"])   ? $data["aplayersarr"]  : [];
        $class->version         = isset($data["version"])       ? $data["version"]      : "";

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
    public function iniLoad(String $ini, bool $group = false) {
        $path       = $this->dirMain();
        $ini_str    = $this->KUTIL->fileGetContents($path.'/ShooterGame/Saved/Config/LinuxServer/'.$ini);

        if ($ini_str !== false) {
            // Lesen und Codieren
            if(!preg_match('//u', $ini_str)) $ini_str = mb_convert_encoding($ini_str,'UTF-8', 'UCS-2LE');
            $ini_str        = str_replace(" ", null, $ini_str);
            $this->KUTIL->filePutContents("$path/ShooterGame/Saved/Config/LinuxServer/conv_$ini", $ini_str);

            // Schreiben
            $this->ini      = parse_ini_string($ini_str, $group, INI_SCANNER_RAW);
            $this->iniext   = extend_parse_ini($this->KUTIL->path("$path/ShooterGame/Saved/Config/LinuxServer/conv_$ini")["/path"]);
            $this->inipath  = $this->KUTIL->path($path.'/ShooterGame/Saved/Config/LinuxServer/'.$ini)["/path"];
            $this->inistr   = $ini_str;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gibt die geladene ini als String wieder
     *
     * @return false|string
     */
    public function iniGetString() {
        return $this->inistr;
    }

    /**
     * Gibt den gesamten Pfad zur Konfig (ini) zurück
     *
     * @return mixed
     */
    public function iniGetPath() {
        return $this->inipath;
    }

    /**
     * Gibt die ini als Array aus
     *
     * @return mixed
     */
    public function iniGetArray() {
        return $this->ini;
    }

    /**
     * liest einen bestimmten Wert aus der Ini
     *
     * @param $key
     * @return mixed
     */
    public function iniRead($key) {
        return ($this->iniIsKeySet($key)) ? $this->ini[$key] : false;
    }

    /**
     * Prüft ob eine Option gesetzt ist
     *
     * @param $key
     * @return bool
     */
    public function iniIsKeySet($key) {
        return isset($this->ini[$key]);
    }

    /**
     * Schreibt einen bestimmten Wert in der Ini um
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function iniWrite($key, $value) {
        $this->ini[$key] = $value;
        return $this->ini[$key] === $value;
    }

    /**
     * Löscht einen bestimmten Wert aus der Ini
     *
     * @param $key
     * @return bool
     */
    public function iniRemove($key) {
        if(isset($this->ini[$key])) {
            unset($this->ini[$key]);
            return !isset($this->ini[$key]);
        }
        else {
            return true;
        }
    }

    /**
     * Speichert die werte in die Ini
     *
     * @return bool
     */
    public function iniSave()
    {
        $this->safeFileWrite($this->inipath, $this->ini);
        return true;
    }

    //Cluster

    /**
     * Läd Cluster Daten
     */
    public function clusterLoad() {
        global $helper;
        $clusterjson_path = $this->KUTIL->path(__ADIR__."/app/json/panel/cluster_data.json")["/path"];
        $infos["in"] = false;
        if (@file_exists($clusterjson_path)) {
            $json = $helper->fileToJson($clusterjson_path);
            $infos["mods"]      = false;
            $infos["konfig"]    = false;
            $infos["admin"]     = false;
            $infos["type"]      = 0;
            foreach ($json as $mk => $mv) {
                if (array_search($this->name(), array_column($mv["servers"], 'server')) !== false) {
                   //var_dump($mv);
                    $infos["in"]            = true;
                    $infos["clusterid"]     = $mv["clusterid"];
                    $infos["name"]          = $mv["name"];
                    $infos["key"]           = $mk;
                    $infos["type"]          = $mv["servers"][array_search($this->name(), array_column($mv["servers"], 'server'))]["type"];
                    $infos["mods"]          = $mv["sync"]["mods"];
                    $infos["konfig"]        = $mv["sync"]["konfig"];
                    $infos["admin"]         = $mv["sync"]["admin"];
                    $infos["whitelist"]     = $mv["sync"]["whitelist"];
                }
            }
        }
        $this->loadedcluster    = true;
        $this->cluster_data     = $infos;
    }

    /**
     * Gibt gesamten Daten zum Cluster als Array wieder
     *
     * @return array|bool
     */
    public function clusterArray() {
        return $this->loadedcluster ? $this->cluster_data : false;
    }

    /**
     * Gibt aus ob der Server in einem Cluster ist
     *
     * @return bool
     */
    public function clusterIn() {
        return $this->loadedcluster ? $this->cluster_data["in"] : false;
    }

    /**
     * Gibt die Cluster ID wieder
     *
     * @param string $key
     * @return mixed|string
     */
    public function clusterRead(string $key) {
        return isset($this->cluster_data[$key]) && $this->clusterIn() ? $this->cluster_data[$key] : false;
    }

    // RCON Funktionen

    /**
     * Prüft ob der Server eine RCON verbindung überhaupt aufbauen könnte
     *
     * @return bool
     */
    public function checkRcon() {
        return ($this->stateCode() == 2 && $this->cfgRead('ark_RCONEnabled') == 'True' && $this->cfgRead('ark_ServerAdminPassword') != '');
    }

    /**
     * Führt einen RCON command aus
     *
     * @param String $commmand
     * @param bool $getResponse
     * @return int
     */
    public function execRcon(String $commmand = "", bool $getResponse = false) {
        if ($this->checkRcon()) {
            $re     = $getResponse ? false : 12;

            //inz RCON
            $ip     = $_SERVER['SERVER_ADDR'];
            $port   = $this->cfgRead('ark_RCONPort');
            $pw     = $this->cfgRead('ark_ServerAdminPassword');
            $rcon   = new parent($ip, $port, $pw, 3);

            if ($rcon->connect()) {
                if ($commmand == "") {
                    $re = $getResponse ? false : 2;
                }
                elseif (!$rcon->send_command($commmand)) {
                    $re = $getResponse ? false : 12;
                }
                else {
                    $re = $getResponse ? $rcon->get_response() : 108;
                }
                $rcon->disconnect();
            }
        } else {
            $re = $getResponse ? false : 12;
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
    private function writeIniFile(Array $array, String $file)
    {
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $skey => $sval) $res[] = $skey . "=" . (is_numeric($sval) ? $sval : '"' . $sval . '"');
            } else $res[] = $key . "=" . (is_numeric($val) ? $val : '"' . $val . '"');
        }
        $this->safeFileWrite($file, implode("\n", $res));
    }

    /**
     * @param String $fileName
     * @param String $dataToSave
     */
    private function safeFileWrite(String $fileName, String $dataToSave)
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

