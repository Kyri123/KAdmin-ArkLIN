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
 * Class xml_helper
 */
class xml_helper extends helper {

    public $xml_path;
    public $xml_string;
    public $find;

    /**
     * xml_helper constructor.
     * @param String $xml_path
     */
    public function __construct(String $xml_path)
    {
        parent::__construct();
        $this->xml_path = $xml_path;
        if (file_exists($xml_path)) {
            $this->xml_string = file_get_contents($xml_path);
            $this->find = true;
        } else {
            $this->find = false;
            return false;
        }
    }

    /**
     * Suche einen Wert mit einem bestimmten Attribut
     *
     * @param String $attr
     * @return bool|SimpleXMLElement
     */
    public function byattr(String $attr) {
        if ($this->find) {
            $xml = simplexml_load_string($this->xml_string);
            $result = $xml->xpath($attr);
            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * Gibt die XML datei in einen Array aus
     *
     * @param bool $obj (true) Objecte | (false) Array
     * @return SimpleXMLElement|array|bool
     */
    public function array($obj = false) {
        if ($this->find && $obj) {
            return simplexml_load_file($this->xml_path);
        } elseif ($this->find) {
            return parent::xmlfile_to_array($this->xml_path);
        } else {
            return false;
        }
    }

}


