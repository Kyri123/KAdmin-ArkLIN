<?php

class jobs
{
    public $server = null;

    public function __construct()
    {
        #empty
    }

    public function set($str) {
        $this->server = $str;
    }

    public function arkmanager($shell) {
        if ($this->server == null) return "Server nicht gesetzt";
        $serv = new server($this->server);
        $file_jobs = $serv->jobs_file();
        $file_jobs = str_replace("\r", null, $file_jobs);
        $path_jobs = $serv->jobs_dir();
        $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ;arkmanager ' . $shell . ' @' . $serv->name();
        return $serv->jobs_write($file_jobs);
    }

    public function shell($shell) {
        if ($this->server == null) return "Server nicht gesetzt";
        $serv = new server($this->server);
        $file_jobs = $serv->jobs_file();
        $file_jobs = str_replace("\r", null, $file_jobs);
        $path_jobs = $serv->jobs_dir();
        $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ; ' . $shell;
        return $serv->jobs_write($file_jobs);
    }
}

