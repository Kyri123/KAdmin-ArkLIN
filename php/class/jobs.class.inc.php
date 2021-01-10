<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

/**
 * Class jobs
 */
class jobs extends helper
{
    public $server = null;

    /**
     * jobs constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Setzt den Server wo die Jobs erstellt bzw ausgeführt werden sollen
     *
     * @param String $str
     * @return void
     */
    public function set(String $str) {
        $this->server = $str;
    }

    /**
     * Erstellt ein Job (shell) mit vordefinierten befehl "arkmanager"
     *
     * @param String $shell
     * @return bool|mysql
     */
    public function arkmanager(String $shell) {
        if ($this->server == null) return false;
        $serv = new server($this->server);

        // Füge Kommand zur DB hinzu
        global $mycon;
        $command = 'arkmanager ' . saveshell($shell) . ' @' . saveshell($serv->name()) . '; exit';
        $query = "INSERT INTO `ArkAdmin_shell` (`server`, `command`) VALUES (?, 'screen -dm bash -c \'$command\'')";

        return $mycon->query($query, $serv->name());
    }


    /**
     * Erstellt ein Job (shell)
     * @param String $shell
     * @return bool|mysql
     */
    public function shell(String $shell) {
        if ($this->server == null) return false;
        $serv = new server($this->server);

        // Füge Kommand zur DB hinzu
        global $mycon;
        $command = saveshell($shell) . '; exit';
        $query = "INSERT INTO `ArkAdmin_shell` (`server`, `command`) VALUES (?, 'screen -dm bash -c \'$command\'')";
        
        return $mycon->query($query, $serv->name());
    }
}

