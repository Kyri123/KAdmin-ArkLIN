<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

class steamapi {

    public $API_Key;
    public $modid;

    private $jsonpath = "app/json/steamapi/";

    public function __construct()
    {
        global $API_Key;
        $this->API_Key = $API_Key;
    }

    public function getmod($modid) {
        return $this->get_API_json($modid, 'mod');
        $this->modid;
    }

    public function getmod_class($modid) {
        $json = $this->get_API_json($modid, 'mod');

        $mod = new steam_mod();
        $mod->publishedfileid = $json->response->publishedfiledetails[0]->publishedfileid;
        $mod->result = $json->response->publishedfiledetails[0]->result;
        $mod->creator = $json->response->publishedfiledetails[0]->creator;
        $mod->creator_app_id = $json->response->publishedfiledetails[0]->creator_app_id;
        $mod->consumer_app_id = $json->response->publishedfiledetails[0]->consumer_app_id;
        $mod->filename = $json->response->publishedfiledetails[0]->filename;
        $mod->file_size = $json->response->publishedfiledetails[0]->file_size;
        $mod->file_url = $json->response->publishedfiledetails[0]->file_url;
        $mod->hcontent_file = $json->response->publishedfiledetails[0]->hcontent_file;
        $mod->preview_url = $json->response->publishedfiledetails[0]->preview_url;
        $mod->hcontent_preview = $json->response->publishedfiledetails[0]->hcontent_preview;
        $mod->title = $json->response->publishedfiledetails[0]->title;
        $mod->description = $json->response->publishedfiledetails[0]->description;
        $mod->time_created = $json->response->publishedfiledetails[0]->time_created;
        $mod->time_updated = $json->response->publishedfiledetails[0]->time_updated;
        $mod->visibility = $json->response->publishedfiledetails[0]->visibility;
        $mod->banned = $json->response->publishedfiledetails[0]->banned;
        $mod->ban_reason = $json->response->publishedfiledetails[0]->ban_reason;
        $mod->subscriptions = $json->response->publishedfiledetails[0]->subscriptions;
        $mod->favorited = $json->response->publishedfiledetails[0]->favorited;
        $mod->lifetime_subscriptions = $json->response->publishedfiledetails[0]->lifetime_subscriptions;
        $mod->lifetime_favorited = $json->response->publishedfiledetails[0]->lifetime_favorited;
        $mod->views = $json->response->publishedfiledetails[0]->views;
        $mod->tags = $json->response->publishedfiledetails[0]->tags;

        $this->modid;
        return $mod;
    }

    public function getsteamprofile($sid) {
        return $this->get_API_json($sid, 'profile');
    }

    public function getsteamprofile_class($sid) {
        $json = $this->get_API_json($sid, 'profile');

        $player = new steam_profile();
        $player->steamid = $json->response->players[0]->steamid;
        $player->communityvisibilitystate = $json->response->players[0]->communityvisibilitystate;
        $player->profilestate = $json->response->players[0]->profilestate;
        $player->personaname = $json->response->players[0]->personaname;
        $player->lastlogoff = $json->response->players[0]->lastlogoff;
        $player->commentpermission = $json->response->players[0]->commentpermission;
        $player->profileurl = $json->response->players[0]->profileurl;
        $player->avatar = $json->response->players[0]->avatar;
        $player->avatarmedium = $json->response->players[0]->avatarmedium;
        $player->avatarfull = $json->response->players[0]->avatarfull;
        $player->personastate = $json->response->players[0]->personastate;
        $player->realname = $json->response->players[0]->realname;
        $player->primaryclanid = $json->response->players[0]->primaryclanid;
        $player->timecreated = $json->response->players[0]->timecreated;
        $player->personastateflags = $json->response->players[0]->personastateflags;
        $player->loccountrycode = $json->response->players[0]->loccountrycode;
        $player->locstatecode = $json->response->players[0]->locstatecode;
        $player->loccityid = $json->response->players[0]->loccityid;

        return $player;
    }

    public function check_mod() {
        if ($this->getmod_class($this->modid)->consumer_app_id == 346110) {
            return true;
        } else {
            return false;
        }
    }

    private function get_API_json($id, $type) {
        chdir($_SERVER['DOCUMENT_ROOT']);
        $file = $this->jsonpath.$type.$id.'.json';

        if (file_exists($file)) {
            $filetime = filemtime($file);
            $time = time();
            $diff = $time - $filetime;
            if ($diff > 3600) {
                if ($this->gen_API_json($id, $type)) {
                    $json = file_get_contents($file);
                    $json = json_decode($json);
                }
            } else {
                $json = file_get_contents($file);
                $json = json_decode($json);
            }
        } else {
            if ($this->gen_API_json($id, $type)) {
                $json = file_get_contents($file);
                $json = json_decode($json);
            }
        }
        return $json;
    }


    private function gen_API_json($id, $type) {
        $set = 0;
        $is = 0;
        $fields = array();

        if ($type == "profile") {
            $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=".$this->API_Key."&steamids=".$id;
            $set = 2;
            $is = 1;
        }
        elseif ($type == "mod") {
            $url = 'https://api.steampowered.com/ISteamRemoteStorage/GetPublishedFileDetails/v1/';
            $set = 1;
            $is = 1;
        }

        if ($set == 1) {
            $fields = array(
                'itemcount' => 1,
                'publishedfileids[0]' => $id,
            );
            $postvars = http_build_query($fields);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                "itemcount=1&publishedfileids[0]=".$id);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close ($ch);

        }
        elseif ($set == 2) {
            $res = file_get_contents($url);
        }

        if ($is == 1) {
            if (file_put_contents($this->jsonpath.$type.$id.'.json', $res)) {
                return true;
            } else {
                return false;
            }
        }
    }
}


class steam_profile {
    public $steamid;
    public $communityvisibilitystate;
    public $profilestate;
    public $personaname;
    public $lastlogoff;
    public $commentpermission;
    public $profileurl;
    public $avatar;
    public $avatarmedium;
    public $avatarfull;
    public $personastate;
    public $realname;
    public $primaryclanid;
    public $timecreated;
    public $personastateflags;
    public $loccountrycode;
    public $locstatecode;
    public $loccityid;
}

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