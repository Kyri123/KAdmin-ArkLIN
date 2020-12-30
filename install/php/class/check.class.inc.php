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
 * Prüfen von Installieren Modulen oder Programmen für Arkadmin2
 * - Benötigt: Helper
 */
class check extends helper {

    private $state;
    private $KUTIL;
    public $json;

    /**
     * Öffnet die check.json
     *
     * @param  string $path
     * @return void
     */
    public function __construct(string $path)
    {
        global $KUTIL;
        $this->KUTIL    = $KUTIL;
        $this->json     = parent::fileToJson($path);
    }
    
    /**
     * Verarbeitet int zu einem Array zu auslesen des Status
     *
     * @param  mixed $type
     * @return array
     */
    private function setvars(): array
    {
        if($this->state == 0) {
            $vars["color"]  = "danger";
            $vars["icon"]   = "fa-thumbs-down";
            $vars["bool"]   = false;
        }
        elseif($this->state == 1) {
            $vars["color"]  = "warning";
            $vars["icon"]   = "fa-thumbs-up";
            $vars["bool"]   = true;
        }
        elseif($this->state >= 2) {
            $vars["color"]  = "success";
            $vars["icon"]   = "fa-thumbs-up";
            $vars["bool"]   = true;
        }
        $vars["code"]       = $this->state;

        return $vars;
    }
        
    /**
     * Prüft einen bestimmten array teil und verarbeitet die Informationen
     * gibt Status in einem Array wieder
     *
     * @param  int $key
     * @return array
     */
    public function check(int $key): array
    {
        $this->state = 0;

        switch($this->json[$key]["php_case"]) {
            // Prüfe CURL
            case "curl":
                $this->state        = in_array('curl', get_loaded_extensions()) ? 2 : 0;
            break;

            // Prüfe Rewrite aktiv
            case "mod_rewrite":
                $JSON               = $this->KUTIL->fileGetContentsURL((isset($_SERVER["HTTPS"]) ? ($_SERVER["HTTPS"] == "on" ? "https" : "http") : "http") ."://".$_SERVER["HTTP_HOST"]."/?mod_rewrite", true)["Assoc"];
                $this->state        = $JSON !== false ? (is_array($JSON) ? (array_key_exists('HTTP_MOD_REWRITE', $JSON) ? 2 : 0) : 0) : 0;
            break;

            // Prüfe Linux
            case "os":
                $this->state        = strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' ? 2 : 0;
            break;

            // Prüfe Arkmanager
            case "am":
                $check              = shell_exec(sprintf("which %s", escapeshellarg("arkmanager")));
                $this->state        = !empty($check) ? 2 : 0;
            break;

            // Prüfe ArkAdmin-Server
            case "aa":
                $webserver          = json_decode(file_get_contents(__ADIR__."/arkadmin_server/config/server.json") ,true);
                $webserver["port"]  = (isset($webserver["port"])) ? $webserver["port"] : 30000;
                $header             = @get_headers("http://127.0.0.1:".$webserver['port']."/");
                $this->state        = is_array($header) ? 2 : 0;
            break;

            // Prüfe Screen
            case "screen":
                $check              = shell_exec(sprintf("which %s", escapeshellarg("screen")));
                $this->state        = !empty($check) ? 2 : 0;
            break;

            // Prüfe Mysql Klasse
            case "mysqli":
                $this->state        = (class_exists("mysqli")) ? 2 : 0;
            break;

            // Prüfe PHP version
            case "php":
                if (PHP_VERSION_ID >= 70300) {
                    $this->state    = 2;
                } else {
                    $this->state    = 0;
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
    public function check_all(): bool
    {
        $bool = true;
        for($i=0;$i<count($this->json);$i++) if(!$this->check($i)["bool"]) $bool = false;
        return $bool;
    }
}


