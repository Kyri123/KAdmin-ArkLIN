<?php

class userclass {

    private $id;
    private $mycon;

    function __construct()
    {
        global $mycon;
        $this->mycon = $mycon;
    }

    function setid($id) {
        $this->id = $id;
    }

    function name() {
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$this->id.'\'';
        if($this->mycon->query($query)->numRows() > 0) {
            $row = $this->mycon->query($query)->fetchArray();
            return $row['username'];
        }
        else {
            return 'Account nicht gefunden!';
        }
    }

    function lasttime() {
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$this->id.'\'';
        if($this->mycon->query($query)->numRows() > 0) {
            $row = $this->mycon->query($query)->fetchArray();
            return $row['lastlogin'];
        }
        else {
            return 'Account nicht gefunden!';
        }
    }

    function regtime() {
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$this->id.'\'';
        if($this->mycon->query($query)->numRows() > 0) {
            $row = $this->mycon->query($query)->fetchArray();
            return $row['registerdate'];
        }
        else {
            return 'Account nicht gefunden!';
        }
    }

    function rang() {
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$this->id.'\'';
        if($this->mycon->query($query)->numRows() > 0) {
            $row = $this->mycon->query($query)->fetchArray();
            return $row['rang'];
        }
        else {
            return 'Account nicht gefunden!';
        }
    }

    function ban() {
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$this->id.'\'';
        if($this->mycon->query($query)->numRows() > 0) {
            $row = $this->mycon->query($query)->fetchArray();
            return $row['ban'];
        }
        else {
            return 'Account nicht gefunden!';
        }
    }

    function email() {
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$this->id.'\'';
        if($this->mycon->query($query)->numRows() > 0) {
            $row = $this->mycon->query($query)->fetchArray();
            return $row['email'];
        }
        else {
            return 'Account nicht gefunden!';
        }
    }

    function pw() {
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \''.$this->id.'\'';
        if($this->mycon->query($query)->numRows() > 0) {
            $row = $this->mycon->query($query)->fetchArray();
            return $row['password'];
        }
        else {
            return 'Account nicht gefunden!';
        }
    }
}

?>