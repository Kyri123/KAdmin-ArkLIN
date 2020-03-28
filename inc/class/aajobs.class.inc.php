<?php

class jobs
{
    public $cfg_str;

    public function __construct()
    {
        #empty
    }

    public function set($str) {
        $this->cfg_str = $str;
    }

    public function create($shell) {
        $serv = new server($this->cfg_str);
        $file_jobs = $serv->get_job_file();
        $file_jobs = str_replace("\r", null, $file_jobs);
        $path_jobs = $serv->get_job_path();
        $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ;arkmanager ' . $shell . ' @' . $serv->show_name() . ' ;exit';
        return $serv->write_job_file($file_jobs);
    }

    public function create_shell($shell) {
        $serv = new server($this->cfg_str);
        $file_jobs = $serv->get_job_file();
        $file_jobs = str_replace("\r", null, $file_jobs);
        $path_jobs = $serv->get_job_path();
        echo $file_jobs .= "\necho \"\" > " . $_SERVER['DOCUMENT_ROOT'] . '/' . $path_jobs . ' ; ' . $shell;
        return $serv->write_job_file($file_jobs);
    }
}

