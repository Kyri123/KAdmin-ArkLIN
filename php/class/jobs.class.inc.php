<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

class jobs extends helper
{
    public $server = null;

    public function __construct()
    {
        #empty
    }

    public function set(String $str) {
        $this->server = $str;
    }

    public function arkmanager(String $shell) {
        if ($this->server == null) return "Server nicht gesetzt";
        $serv = new server($this->server);
        $file_jobs = $serv->jobs_file();
        $file_jobs = str_replace("\r", null, $file_jobs);

        // Füge Kommand zur DB hinzu
        global $mycon;
        $command = 'arkmanager ' . $shell . ' @' . $serv->name() . '; exit';
        $query = "INSERT INTO `ArkAdmin_shell` 
        (
            `server`, 
            `command`
        ) VALUES ( 
            '".$serv->name()."',
            'screen -dm bash -c \'".$command."\''
        )";

        return $mycon->query($query);
    }

    public function shell(String $shell) {
        if ($this->server == null) return "Server nicht gesetzt";
        $serv = new server($this->server);
        $file_jobs = $serv->jobs_file();
        $file_jobs = str_replace("\r", null, $file_jobs);

        // Füge Kommand zur DB hinzu
        global $mycon;
        $command = $shell . '; exit';
        $query = "INSERT INTO `ArkAdmin_shell` 
        (
            `server`, 
            `command`
        ) VALUES ( 
            '".$serv->name()."',
            'screen -dm bash -c \'".$command."\''
        )";
        
        return $mycon->query($query);
    }
}

