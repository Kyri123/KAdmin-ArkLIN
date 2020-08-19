<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

/**
 * Class userclass
 */
class userclass extends helper
{

    private $id;
    private $mycon;
    private $myconisset;
    public $frech;

    /**
     * userclass constructor.
     * @param int $id
     */
    function __construct(int $id = 0)
    {
        global $mycon;
        $this->mycon = $mycon;
        $this->myconisset = false;
        if($id != 0) $this->setid($id);
    }

    /**
     * Setzte die ID des Users um die MYSQL auszuführen und die Daten zu holen
     *
     * @param int $id
     * @return bool
     */
    public function setid(int $id)
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

    /**
     * Gibt einen gewünschten Wert aus
     *
     * @param String $key
     * @return string
     */
    public function read(String $key) {
        if ($this->myconisset) {
            $frech = $this->frech;
            return $frech[$key];
        } else {
            return 'Account nicht gefunden oder gesetzt!';
        }
    }

    /**
     * Schreibt einen Wert des Users
     *
     * @param String $key
     * @param String $value
     * @return bool
     */
    public function write(String $key, String $value) {
        $id = $this->id;
        $query = 'UPDATE `ArkAdmin_users` SET `'.$key.'`=\''.$value.'\'  WHERE `id` = \''.$id.'\'';
        if ($this->mycon->query($query)) {
            $this->setid($id);
            return true;
        }
        $this->myconisset = false;
        return false;
    }

    /**
     * Prüfe ob der Expertenmodus aktiv ist
     *
     * @return bool
     */
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

    /**
     * Liest einen wert aus der user.json
     *
     * @param String $mode
     * @return bool
     */
    public function show_mode(String $mode)
    {
        $id = md5($this->id);
        $path = "app/json/user/$id.json";
        if (file_exists($path)) {
            $json = parent::file_to_json($path, true);
            if(isset($json[$mode])) {
                return ($json[$mode] == 1) ? true : false;
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