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
 * Prüfen von Installieren Modulen oder Programmen für Arkadmin2
 * - Benötigt: Helper
 */
class check extends helper {

    private $state;
    public $json;

    /**
     * Öffnet die check.json
     *
     * @param  string $path
     * @return void
     */
    public function __construct(string $path)
    {
        $this->json = parent::file_to_json($path);
    }
    
    /**
     * Verarbeitet int zu einem Array zu auslesen des Status
     *
     * @param  mixed $type
     * @return array
     */
    private function setvars() {
        if($this->state == 0) {
            $vars["color"] = "danger";
            $vars["icon"] = "fa-thumbs-down";
            $vars["bool"] = false;
        }
        elseif($this->state == 1) {
            $vars["color"] = "warning";
            $vars["icon"] = "fa-thumbs-up";
            $vars["bool"] = true;
        }
        elseif($this->state >= 2) {
            $vars["color"] = "success";
            $vars["icon"] = "fa-thumbs-up";
            $vars["bool"] = true;
        }
        $vars["code"] = $this->state;

        return $vars;
    }
        
    /**
     * Prüft einen bestimmten array teil und verarbeitet die Informationen
     * gibt Status in einem Array wieder
     *
     * @param  int $key
     * @return array
     */
    public function check(int $key) {
        $this->state = 0;

        switch($this->json[$key]["php_case"]) {
            // Prüfe CURL
            case "curl":
                $this->state = (in_array('curl', get_loaded_extensions())) ? 2 : 0;
            break;
            //________________________________________________//
            // Prüfe Rewrite aktiv
            case "mod_rewrite":
                $this->state = (array_key_exists('HTTP_MOD_REWRITE', $_SERVER)) ? 2 : 0;
            break;
            //________________________________________________//
            // Prüfe Linux
            case "os":
                $this->state = (!(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) ? 2 : 0;
            break;
            //________________________________________________//
            // Prüfe Arkmanager
            case "am":
                $check = shell_exec(sprintf("which %s", escapeshellarg("arkmanager")));
                $this->state = (!empty($check)) ? 2 : 0;
            break;
            //________________________________________________//
            // Prüfe ArkAdmin-Server
            case "aa":
                $webserver = json_decode(file_get_contents("arkadmin_server/config/server.json") ,true);
                $webserver["port"] = (isset($webserver["port"])) ? $webserver["port"] : 30000;
                $header = @get_headers("http://127.0.0.1:".$webserver['port']."/");
                $this->state = (is_array($header)) ? 2 : 0;
            break;
            //________________________________________________//
            // Prüfe Screen
            case "screen":
                $check = shell_exec(sprintf("which %s", escapeshellarg("screen")));
                $this->state = (!empty($check)) ? 2 : 0;
            break;
            //________________________________________________//
            // Prüfe Mysql Klasse
            case "mysqli":
                $this->state = (class_exists("mysqli")) ? 2 : 0;
            break;
            //________________________________________________//
            // Prüfe PHP version
            case "php":
                if (!PHP_VERSION_ID >= 70300) {
                    $this->state = 2;
                } elseif(PHP_VERSION_ID >= 70000) {
                    $this->state = 1;
                } else {
                    $this->state = 0;
                }
            break;
        }

        return $this->setvars();
    }
                
    /**
     * Überprüft alle Informationen
     *
     * @return bool
     */
    public function check_all() {
        $bool = true;
        for($i=0;$i<count($this->json);$i++) if(!$this->check($i)["bool"]) $bool = false;
        return $bool;
    }
}



?>