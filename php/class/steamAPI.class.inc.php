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
 * Class steamapi
 */
class steamapi extends helper {

    private $API_Key;
    public $modid;

    private $jsonpath = "app/json/steamapi/";

    /**
     * steamapi constructor.
     */
    public function __construct()
    {
        $ckonfig = parent::file_to_json('php/inc/custom_konfig.json', true);
        $this->API_Key = $ckonfig['apikey'];
    }

    /**
     * Hole daten von einer ModID
     *
     * @param $modid
     * @param int $time
     * @param false $remove
     * @return mixed
     */
    public function getmod($modid, $time = 3600, $remove = false) {
        return $this->get_API_json($modid, 'mod', $time, null, $remove);
    }

    /**
     * Hole Daten von einer Modliste (array)
     *
     * @param $serv
     * @param $arr
     * @param int $time
     * @param false $remove
     * @return mixed
     */
    public function getmod_list($serv, $arr, $time = 3600, $remove = false) {
        return $this->get_API_json($serv, 'mod', $time, $arr, $remove);
    }

    //Todo: Remove
    /**
     * Wandelt Daten von einer Mod direkt weiter in einer Klasse um diese einfacher zu benutzen
     *
     * @param $modid
     * @param int $time
     * @param false $remove
     * @return steam_mod
     */
    public function getmod_class($modid, $time = 3600, $remove = false) {
        $json = $this->get_API_json($modid, 'mod', $time, null, $remove);

        $mod = new steam_mod();
        if(isset($json->response->publishedfiledetails[0]->publishedfileid)) $mod->publishedfileid = $json->response->publishedfiledetails[0]->publishedfileid;
        if(isset($json->response->publishedfiledetails[0]->result)) $mod->result = $json->response->publishedfiledetails[0]->result;
        if(isset($json->response->publishedfiledetails[0]->creator)) $mod->creator = $json->response->publishedfiledetails[0]->creator;
        if(isset($json->response->publishedfiledetails[0]->creator_app_id)) $mod->creator_app_id = $json->response->publishedfiledetails[0]->creator_app_id;
        if(isset($json->response->publishedfiledetails[0]->consumer_app_id)) $mod->consumer_app_id = $json->response->publishedfiledetails[0]->consumer_app_id;
        if(isset($json->response->publishedfiledetails[0]->filename)) $mod->filename = $json->response->publishedfiledetails[0]->filename;
        if(isset($json->response->publishedfiledetails[0]->file_size)) $mod->file_size = $json->response->publishedfiledetails[0]->file_size;
        if(isset($json->response->publishedfiledetails[0]->file_url)) $mod->file_url = $json->response->publishedfiledetails[0]->file_url;
        if(isset($json->response->publishedfiledetails[0]->hcontent_file)) $mod->hcontent_file = $json->response->publishedfiledetails[0]->hcontent_file;
        if(isset($json->response->publishedfiledetails[0]->preview_url)) $mod->preview_url = $json->response->publishedfiledetails[0]->preview_url;
        if(isset($json->response->publishedfiledetails[0]->hcontent_preview)) $mod->hcontent_preview = $json->response->publishedfiledetails[0]->hcontent_preview;
        if(isset($json->response->publishedfiledetails[0]->title)) $mod->title = $json->response->publishedfiledetails[0]->title;
        if(isset($json->response->publishedfiledetails[0]->description)) $mod->description = $json->response->publishedfiledetails[0]->description;
        if(isset($json->response->publishedfiledetails[0]->time_created)) $mod->time_created = $json->response->publishedfiledetails[0]->time_created;
        if(isset($json->response->publishedfiledetails[0]->time_updated)) $mod->time_updated = $json->response->publishedfiledetails[0]->time_updated;
        if(isset($json->response->publishedfiledetails[0]->visibility)) $mod->visibility = $json->response->publishedfiledetails[0]->visibility;
        if(isset($json->response->publishedfiledetails[0]->banned)) $mod->banned = $json->response->publishedfiledetails[0]->banned;
        if(isset($json->response->publishedfiledetails[0]->ban_reason))  $mod->ban_reason = $json->response->publishedfiledetails[0]->ban_reason;
        if(isset($json->response->publishedfiledetails[0]->subscriptions)) $mod->subscriptions = $json->response->publishedfiledetails[0]->subscriptions;
        if(isset($json->response->publishedfiledetails[0]->favorited)) $mod->favorited = $json->response->publishedfiledetails[0]->favorited;
        if(isset($json->response->publishedfiledetails[0]->lifetime_subscriptions)) $mod->lifetime_subscriptions = $json->response->publishedfiledetails[0]->lifetime_subscriptions;
        if(isset($json->response->publishedfiledetails[0]->lifetime_favorited)) $mod->lifetime_favorited = $json->response->publishedfiledetails[0]->lifetime_favorited;
        if(isset($json->response->publishedfiledetails[0]->views)) $mod->views = $json->response->publishedfiledetails[0]->views;
        if(isset($json->response->publishedfiledetails[0]->tags)) $mod->tags = $json->response->publishedfiledetails[0]->tags;

        $this->modid;
        return $mod;
    }

    /**
     * Hole Liste von Profilen von der API
     *
     * @param $serv
     * @param $arr
     * @param int $time
     * @param false $remove
     * @return mixed
     */
    public function getsteamprofile_list($serv, $arr, $time = 3600, $remove = false) {
        return $this->get_API_json($serv, 'profile', $time, $arr, $remove);
    }

    /**
     * Prüfe ob eine vorher gesetzte mod ein gültiges Workshop item ist
     *
     * @modid int|string
     * @return bool
     */
    public function check_mod($modid = null) {
        if ($this->getmod_class(((!$modid == null) ? $modid : $this->modid), 0, true)->consumer_app_id == 346110) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Hole Json von der SteamAPI oder Local vom Server
     *
     * @param $id
     * @param $type
     * @param $time_differ
     * @param null $arr
     * @param false $remove
     * @return mixed
     */
    private function get_API_json($id, $type, $time_differ, $arr = null, $remove = false) {
        chdir($_SERVER['DOCUMENT_ROOT']);
        $file = $this->jsonpath.$type."_".$id.'.json';

        if (file_exists($file)) {
            $filetime = filemtime($file);
            $time = time();
            $diff = $time - $filetime;
            if ($diff > $time_differ) {
                if ($this->gen_API_json($id, $type, $arr)) {
                    $json = file_get_contents($file);
                    $json = json_decode($json);
                    if($remove) unlink($file);
                }
            } else {
                $json = file_get_contents($file);
                $json = json_decode($json);
                if($remove) unlink($file);
            }
        } else {
            if ($this->gen_API_json($id, $type, $arr)) {
                $json = file_get_contents($file);
                $json = json_decode($json);
                if($remove) unlink($file);
            }
        }
        return $json;
    }

    /**
     * Generiert aus der API die JSON auf dem Server
     *
     * @param $id
     * @param $type
     * @param null $arr
     * @return bool
     */
    private function gen_API_json($id, $type, $arr = null) {
        $set = 0;
        $is = 0;
        $fields = array();

        if ($type == "profile") {
            $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=".$this->API_Key."&steamids=".(($arr == null) ? $id : implode(",", $arr));
            $set = 2;
            $is = 1;
        }
        elseif ($type == "mod") {
            $url = 'https://api.steampowered.com/ISteamRemoteStorage/GetPublishedFileDetails/v1/';
            $set = 1;
            $is = 1;
        }

        if ($set == 1) {
            $fields = ($arr == null) ? array(
                'itemcount' => 1,
                'publishedfileids[0]' => $id,
            ):
            $fields = array(
                'itemcount' => count($arr),
                'publishedfileids' => $arr,
            );
            $postvars = http_build_query($fields);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close ($ch);

        }
        elseif ($set == 2) {
            $res = file_get_contents($url);
        }

        if ($is == 1) {
            if(!file_exists($this->jsonpath."mods")) mkdir($this->jsonpath."mods");
            if (file_put_contents($this->jsonpath.$type."_".$id.'.json', $res)) {
                return true;
            } else {
                return false;
            }
        }
    }
}

//Todo: Remove
class steam_mod {
    public $publishedfileid;
    public $result;
    public $creator;
    public $creator_app_id;
    public $consumer_app_id;
    public $filename;
    public $file_size;
    public $file_url;
    public $hcontent_file;
    public $preview_url;
    public $hcontent_preview;
    public $title;
    public $description;
    public $time_created;
    public $time_updated;
    public $visibility;
    public $banned;
    public $ban_reason;
    public $subscriptions;
    public $favorited;
    public $lifetime_subscriptions;
    public $lifetime_favorited;
    public $views;
    public $tags;
}

?>