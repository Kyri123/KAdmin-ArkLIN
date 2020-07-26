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
        $path_jobs = $serv->jobs_dir();
        $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ;arkmanager ' . $shell . ' @' . $serv->name();
        return $serv->jobs_write($file_jobs);
    }

    public function shell(String $shell) {
        if ($this->server == null) return "Server nicht gesetzt";
        $serv = new server($this->server);
        $file_jobs = $serv->jobs_file();
        $file_jobs = str_replace("\r", null, $file_jobs);
        $path_jobs = $serv->jobs_dir();
        $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ; ' . $shell;
        return $serv->jobs_write($file_jobs);
    }
}

