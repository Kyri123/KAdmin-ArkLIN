<?php
//from: https://gist.github.com/Sp1rit/d8776427620d01a61f3c6c453541febd
//Modifiziert: Kyri123 (Oliver Kaufmann)
class Container
{
    public $Players = array();
    public $Tribes = array();

    function LoadDirectory($path, $linkPlayerTribes = true)
    {
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot()) continue;

            if ($fileinfo->getExtension() == 'arkprofile') {
                array_push($this->Players, PlayerFileParser::Parse($fileinfo->getPathName()));
            } else if ($fileinfo->getExtension() == 'arktribe') {
                array_push($this->Tribes, TribeFileParser::Parse($fileinfo->getPathName()));
            }
        }
    }

    function LinkPlayersAndTribes()
    {
        foreach($this->Players as $k => $player)
        {
            $player->OwnedTribes = [];
            foreach($this->Tribes as $tribe)
            {
                if (in_array($player->CharacterName, $tribe->Members)) {
                    $this->Players[$k]->TribeId = $tribe->Id;
                    $this->Players[$k]->TribeName = $tribe->Name;
                }
            }
        }
    }
}

class PlayerFileParser
{
    static function Parse($path)
    {
        $handle = fopen($path, 'rb');
        $data = fread($handle, filesize($path));

        $player = new Player();
        $player->Id = PlayerFileParser::GetId($data);
        $player->SteamId = PlayerFileParser::GetSteamId($data);
        $player->SteamName = null;
        $player->CharacterName = BinaryHelper::GetString($data, 'PlayerCharacterName');
        $player->TribeId = pathinfo($path)["basename"];
        $player->TribeName = "";
        $player->Level = BinaryHelper::GetUInt16($data, 'CharacterStatusComponent_ExtraCharacterLevel');
        $player->ExperiencePoints = BinaryHelper::GetFloat($data, 'CharacterStatusComponent_ExperiencePoints');
        $player->TotalEngramPoints = BinaryHelper::GetInt($data, 'PlayerState_TotalEngramPoints');
        $player->FirstSpawned = BinaryHelper::GetBool($data, 'FirstSpawned');

        $player->FileCreated = filectime($path);
        $player->FileUpdated = filemtime($path);

        fclose($handle);

        return $player;
    }

    private static function GetId($data)
    {
        $id = 'PlayerDataID';
        $intProperty = 'UInt64Property';

        $idPos = strpos($data, $id);
        $intPropertyPos = strpos($data, $intProperty, $idPos);

        $int64String = substr($data, $intPropertyPos + strlen($intProperty) + 9, 8);
        return unpack('V*', $int64String)[1];
    }

    private static function GetSteamId($data)
    {
        $name = 'UniqueNetIdRepl';
        $namePos = strpos($data, $name);

        return substr($data, $namePos + strlen($name) + 9, 17);
    }
}

class TribeFileParser
{
    static function Parse($path)
    {
        $handle = fopen($path, 'rb');
        $data = fread($handle, filesize($path));

        $tribe = new Tribe();
        $tribe->Id = BinaryHelper::GetInt($data, 'TribeID');
        $tribe->Name = BinaryHelper::GetString($data, 'TribeName');
        $tribe->OwnerId = TribeFileParser::GetOwnerId($data);
        // Experimentell! {
        $tribe->Members = TribeFileParser::GetMemberOfTribe($path);
        // }

        $tribe->FileCreated = filectime($path);
        $tribe->FileUpdated = filemtime($path);

        fclose($handle);

        return $tribe;
    }

    // Experimentell! {
    private function GetMemberOfTribe($path) {
        $cont = file_get_contents($path);
        $cont = preg_replace('/[\x01-\x09\x0B\x0C\x0E-\x1F\x7F]/', null, $cont);
        $cont = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', ' ', $cont);
        $cont = trim(preg_replace('/\s+/', ' ', $cont));
        $cont = str_replace(" ", "---", $cont);
        $exp = explode("---", $cont);

        $found = false; $player = array();
        for ($i=0;$i<count($exp);$i++) {
            if ($exp[$i] == "MembersPlayerName") {
                $i += 3;
                $found = true;
            }
            elseif ($exp[$i] == "MembersPlayerDataID") {
                $found = false;
            }

            if ($found && $exp[$i] != "StrProperty") $player[count($player)] = $exp[$i];
        }

        return $player;
    }
    // }

    private static function GetOwnerId($data)
    {
        $id = 'OwnerPlayerDataID';
        $intProperty = 'UInt32Property';

        $idPos = strpos($data, $id);
        $intPropertyPos = strpos($data, $intProperty, $idPos);

        $int64String = substr($data, $intPropertyPos + strlen($intProperty) + 9, 8);
        return unpack('V*', $int64String)[1];
    }
}

class BinaryHelper
{
    static function GetInt($data, $name)
    {
        $intProperty = 'IntProperty';
        $namePos = strpos($data, $name);
        $intPropertyPos = strpos($data, $intProperty, $namePos);

        $intString = substr($data, $intPropertyPos + strlen($intProperty) + 9, 4);
        return unpack('i*', $intString)[1];
    }

    static function GetUInt16($data, $name)
    {
        $intProperty = 'UInt16Property';
        $namePos = strpos($data, $name);
        $intPropertyPos = strpos($data, $intProperty, $namePos);

        $intString = substr($data, $intPropertyPos + strlen($intProperty) + 9, 2);
        return unpack('v*', $intString)[1];
    }

    static function GetFloat($data, $name)
    {
        $intProperty = 'FloatProperty';
        $namePos = strpos($data, $name);
        $intPropertyPos = strpos($data, $intProperty, $namePos);

        $intString = substr($data, $intPropertyPos + strlen($intProperty) + 9, 4);
        return unpack('f*', $intString)[1];
    }

    static function GetBool($data, $name)
    {
        $intProperty = 'BoolProperty';
        $namePos = strpos($data, $name);
        $intPropertyPos = strpos($data, $intProperty, $namePos);

        return ord($data[$intPropertyPos + strlen($intProperty) + 9]) == 1;
    }

    static function GetString($data, $name)
    {
        $strProperty = 'StrProperty';
        $namePos = strpos($data, $name);
        $strPropertyPos = strpos($data, $strProperty, $namePos);

        $length = ord($data[$strPropertyPos + strlen($strProperty) + 1]) - 5;

        return substr($data, $strPropertyPos + strlen($strProperty) + 13, $length);
    }
}

class Player
{
    public $Id;
    public $SteamId;
    public $SteamName;
    public $CharacterName;
    public $Level;
    public $ExperiencePoints;
    public $TotalEngramPoints;
    public $FirstSpawned;
    public $FileCreated;
    public $FileUpdated;
    public $TribeId;
    public $TribeName;
}

class Tribe
{
    public $Id;
    public $Name;
    public $OwnerId;
    public $FileCreated;
    public $FileUpdated;
    public $Members;
}

//--------------
class player_json_helper {
    public function player($json, $id) {
        $re = new Player();

        $re->SteamId = $json[$id]->SteamId;
        $re->SteamName = $json[$id]->SteamName;
        $re->SteamId = $json[$id]->SteamId;
        $re->CharacterName = $json[$id]->CharacterName;
        $re->Level = $json[$id]->Level;
        $re->Id = $json[$id]->Id;
        $re->ExperiencePoints = $json[$id]->ExperiencePoints;
        $re->TotalEngramPoints = $json[$id]->TotalEngramPoints;
        $re->FirstSpawned = $json[$id]->FirstSpawned;
        $re->FileCreated = $json[$id]->FileCreated;
        $re->FileUpdated = $json[$id]->FileUpdated;
        $re->TribeId = $json[$id]->TribeId;

        return $re;
    }

    public function tribe($json, $id) {
        $re = new Tribe();

        $re->Id = $json[$id]->Id;
        $re->Name = $json[$id]->Name;
        $re->OwnerId = $json[$id]->OwnerId;
        $re->FileCreated = $json[$id]->FileCreated;
        $re->FileUpdated = $json[$id]->FileUpdated;

        return $re;
    }
}


?>