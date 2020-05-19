<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

class userclass
{

    private $id;
    private $mycon;
    private $myconisset;
    private $frech;

    function __construct()
    {
        global $mycon;
        $this->mycon = $mycon;
        $this->myconisset = false;
    }

    function setid($id)
    {
        $this->id = $id;
        $query = 'SELECT * FROM `ArkAdmin_users` WHERE `id` = \'' . $this->id . '\'';
        if ($this->mycon->query($query)->numRows() > 0) {
            $this->myconisset = true;
            $this->frech = $this->mycon->query($query)->fetchArray();
            return true;
        }
        $this->myconisset = false;
        return false;
    }

    function name()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['username'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    function lasttime()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['lastlogin'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    function regtime()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['registerdate'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    function rang()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['rang'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    function ban()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['ban'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    function email()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['email'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    function pw()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['password'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }
}

?>