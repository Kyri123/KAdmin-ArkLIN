<?php


class xml_helper extends helper {

    private $xml_path = null;
    private $find = false;
    private $xml_string;

    public function __construct($xml_path)
    {
        $this->xml_path = $xml_path;
        if (file_exists($xml_path)) {
            $this->xml_string = file_get_contents($xml_path);
            $this->find = true;
        } else {
            $this->find = false;
            return false;
        }
    }

    public function byattr($attr) {
        if ($this->find) {
            $xml = simplexml_load_string($this->xml_string);
            $result = $xml->xpath($attr);
            return $result[0];
        } else {
            return false;
        }
    }

    public function array() {
        if ($this->find) {
            return parent::xmlfile_to_array($this->xml_path);
        } else {
            return false;
        }
    }

    public function obj() {
        if ($this->find) {
            return simplexml_load_file($this->xml_path);
        } else {
            return false;
        }
    }

}


?>