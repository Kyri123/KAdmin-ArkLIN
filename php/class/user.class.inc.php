<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

class userclass extends helper
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

    public function setid($id)
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

    public function name()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['username'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    public function lasttime()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['lastlogin'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    public function regtime()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['registerdate'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    public function rang()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['rang'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    public function ban()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['ban'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    public function email()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['email'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    public function pw()
    {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech['password'];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    //write user_data
    public function write($key, $value) {
        $id = $this->id;
        $query = 'UPDATE `ArkAdmin_users` SET `'.$key.'`=\''.$value.'\'  WHERE `id` = \''.$id.'\'';
        if ($this->mycon->query($query)) {
            $this->setid($id);
            return true;
        }
        $this->myconisset = false;
        return false;
    }

    // sende expert modus
    public function expert()
    {
        $id = md5($this->id);
        $path = "app/json/user/$id.json";
        if (file_exists($path)) {
            $json = parent::file_to_json($path, true);
            if(isset($json["expert"])) {
                return ($json["expert"] == 1) ? true : false;
            }
            else {
                return false;
            }
        } else {
            return false;
        }
    }
}

?>