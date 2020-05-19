<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

class alert extends helper {

    public $code = null;
    public $overwrite_color = null;
    public $overwrite_text = null;
    public $overwrite_icon = null;
    public $overwrite_title = null;
    public $overwrite_style = 1;

    private $tpl;
    private $json;
    private $from = array();
    private $to = array();

    public function __construct()
    {
        #empty
    }

    private function check_code() {
        if ($this->code != null && is_int($this->code)) return true;
        return false;
    }

    private function inz() {
        $this->tpl = new Template("tpl.htm", "app/template/alert/");
        $this->tpl->load();

        $key = 'c_'.$this->code;

        if ($this->code < 100) { // Danger
            $color = ($this->overwrite_color == null) ? "danger" : $this->overwrite_color;
            $icon = ($this->overwrite_icon == null) ? "fas fa-exclamation-triangle" : $this->overwrite_icon;
        }
        elseif ($this->code >= 100 && $this->code < 200) { // Success
            $color = ($this->overwrite_color == null) ? "success" : $this->overwrite_color;
            $icon = ($this->overwrite_icon == null) ? "fas fa-check" : $this->overwrite_icon;
        }
        elseif ($this->code >= 200 && $this->code < 300) { // Warning
            $color = ($this->overwrite_color == null) ? "warning" : $this->overwrite_color;
            $icon = ($this->overwrite_icon == null) ? "fas fa-exclamation-circle" : $this->overwrite_icon;
        } else { // Info
            $color = ($this->overwrite_color == null) ? "info" : $this->overwrite_color;
            $icon = ($this->overwrite_icon == null) ? "fas fa-info" : $this->overwrite_icon;
        }
        
        $text = ($this->overwrite_text == null) ? "{::lang::alert::$key::text}" : $this->overwrite_text;
        $title = ($this->overwrite_title == null) ? "{::lang::alert::$key::title}" : $this->overwrite_title;

        $this->tpl->r("color", $color);
        $this->tpl->r("icon", $icon);
        $this->tpl->r("title", $title);
        $this->tpl->r("text", $text);
        $this->tpl->r("rnd", rndbit(50));

        $s1 = ($this->overwrite_style == 1) ? true : false;
        $s2 = ($this->overwrite_style == 2) ? true : false;
        $s3 = ($this->overwrite_style == 3) ? true : false;

        $this->tpl->rif ("style1", $s1);
        $this->tpl->rif ("style2", $s2);
        $this->tpl->rif ("style3", $s3);
    }

    private function default() {
        $this->from = null;
        $this->to = null;
        $this->tpl = null;
        $this->overwrite_color = null;
        $this->overwrite_icon = null;
        $this->overwrite_text = null;
        $this->overwrite_title = null;
        $this->overwrite_style = false;
    }

    public function r($from, $to) {
        array_push($this->from, '{'.$from.'}');
        array_push($this->to, $to);
        return true;
    }

    public function rd($code, $style = 1) {
        $this->overwrite_style = $style;
        $this->code = $code;
        if ($this->check_code()) {
            $this->inz();
            $re = $this->tpl->load_var();
            $re = str_replace($this->from, $this->to, $re);
            $this->default();
            return $re;
        } else {
            return false;
        }
    }

    public function re() {
        if ($this->check_code()) {
            $this->inz();
            $re = $this->tpl->load_var();
            $re = str_replace($this->from, $this->to, $re);
            $this->default();
            return $re;
        } else {
            return false;
        }
    }
}

?>