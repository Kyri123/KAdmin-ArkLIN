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

    private $id = 0;
    private $mycon;
    private $myconisset;
    public $frech;
    public $permissions;
    public $group_array;

    /**
     * userclass constructor.
     * @param int $id
     */
    function __construct(int $id = 0)
    {
        parent::__construct();
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

            // Lade Rechte
            global $D_PERM_ARRAY;
            $this->group_array      = parent::str_to_json($this->frech["rang"], true);
            $this->permissions      = $D_PERM_ARRAY;

            foreach ($this->group_array as $ITEM) {
                $QUERY     = $this->mycon->query("SELECT * FROM `ArkAdmin_user_group` WHERE `id`='$ITEM'");
                if($QUERY->numRows() > 0) {
                    $this->permissions = array_replace_recursive($this->permissions, parent::str_to_json($QUERY->fetchArray()["permissions"]));
                }
            }

            parent::saveFile($this->permissions, __ADIR__."/app/json/arkadmin_server/".md5($id).".permissions.json");

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
        // Prüfe ob Benutzer gesetzt ist
        if ($this->myconisset && $this->id != 0) {
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
        // Prüfe ob Benutzer gesetzt ist
        if ($this->myconisset && $this->id != 0) {
            $query = 'UPDATE `ArkAdmin_users` SET `'.$key.'`=\''.$value.'\'  WHERE `id` = \''.$this->id.'\'';
            if ($this->mycon->query($query)) {
                $this->setid($this->id);
                return true;
            }
            $this->myconisset = false;
            return false;
        }
        else {
            return false;
        }
    }

    /**
     * Prüfe ob der Expertenmodus aktiv ist
     *
     * @return bool
     */
    public function expert()
    {
        // Prüfe ob Benutzer gesetzt ist
        if ($this->myconisset && $this->id != 0) {
            $id = md5($this->id);
            $path = __ADIR__."/app/json/user/$id.json";
            if (file_exists($path)) {
                $json = parent::file_to_json($path, true);
                if(isset($json["expert"])) {
                    return $json["expert"] == 1 && $this->perm("usersettings/expert");
                }
                else {
                    return false;
                }
            } else {
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
        // Prüfe ob Benutzer gesetzt ist
        if ($this->myconisset && $this->id != 0) {
            $id = md5($this->id);
            $path = __ADIR__."/app/json/user/$id.json";
            if (file_exists($path)) {
                $json = parent::file_to_json($path, true);
                if(isset($json[$mode])) {
                    return $json[$mode] == 1;
                }
                else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Gibt aus ob der Benutzer die Rechte zu der jeweiligen aktion hat
     *
     * @param String $key Schlüssel zur permissions (multi array ist mit / zu trennen)
     * @return bool
     */
    public function perm(String $key)
    {
        // Prüfe ob Benutzer gesetzt ist
        if ($this->myconisset && $this->id != 0) {
            // Prüfe das Format
            if(!($key = explode("/", $key))) return false;

            // werte Permissions aus
            $found = true;
            $value = $this->permissions;
            foreach ($key as $item) {
                if(!isset($value[$item])) {
                    $found = false;
                }
                else {
                    $value = $value[$item];
                }
            }


            // gebe bool aus
            return (
                ($found && boolval($value)) ||
                (($key[0] != "server") ? false : (isset($key[1]) ? boolval($this->permissions["server"][$key[1]]["is_server_admin"]) : false)) ||
                boolval($this->permissions["all"]["is_admin"])
            );
        } else {
            return false;
        }
    }
}

