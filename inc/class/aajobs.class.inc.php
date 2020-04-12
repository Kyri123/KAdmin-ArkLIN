<?php

class jobs
{
    public $server;

    public function __construct()
    {
        #empty
    }

    public function set($str) {
        $this->server = $str;
    }

    public function create($shell) {
        $serv = new server($this->server);
        $file_jobs = $serv->get_job_file();
        $file_jobs = str_replace("\r", null, $file_jobs);
        $path_jobs = $serv->get_job_path();
        $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ;arkmanager ' . $shell . ' @' . $serv->show_name();
        return $serv->write_job_file($file_jobs);
    }

    public function create_shell($shell) {
        $serv = new server($this->server);
        $file_jobs = $serv->get_job_file();
        $file_jobs = str_replace("\r", null, $file_jobs);
        $path_jobs = $serv->get_job_path();
        $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ; ' . $shell;
        return $serv->write_job_file($file_jobs);
    }
}

