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
 * alert
 * - Zur Ausgabe von Meldungen
 */
class alert extends helper {

    public $code = null;
    public $overwrite_color = null;
    public $overwrite_text = null;
    public $overwrite_icon = null;
    public $overwrite_title = null;
    public $overwrite_style = 3;
    public $overwrite_mb = 3;
    public $overwrite_ml = -1;
    public $overwrite_mt = -1;
    public $overwrite_mr = -1;

    private $tpl;
    private $from;
    private $to;
    
    /**
     * __construct 
     * - Keine Ausgabe
     * - Keine Eingabe
     * @return void
     */
    public function __construct()
    {
        $this->from = array();
        $this->to = array();
    }
    
    /**
     * Prüft ob ein Code gesetzt ist
     *
     * @return bool
     */
    private function check_code() {
        if ($this->code != null && is_int($this->code)) return true;
        return false;
    }
    
    /**
     * Inizalisiert die ausgabe von einem Allert
     *
     * @return void
     */
    private function inz() {
        $this->tpl = new Template("tpl.htm", "app/template/universally/alert/");
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
        $this->tpl->r("mb", (($this->overwrite_mb >= 0) ? 'mb-'.$this->overwrite_mb : 'mb-2'));
        $this->tpl->r("mr", (($this->overwrite_mr >= 0) ? 'mr-'.$this->overwrite_mr : 'mr-0'));
        $this->tpl->r("mt", (($this->overwrite_mt >= 0) ? 'mt-'.$this->overwrite_mt : 'mt-0'));
        $this->tpl->r("ml", (($this->overwrite_ml >= 0) ? 'ml-'.$this->overwrite_ml : 'ml-0'));

        $s1 = ($this->overwrite_style == 1) ? true : false;
        $s2 = ($this->overwrite_style == 2) ? true : false;
        $s3 = ($this->overwrite_style == 3) ? true : false;

        $this->tpl->rif ("style1", $s1);
        $this->tpl->rif ("style2", $s2);
        $this->tpl->rif ("style3", $s3);
    }
    
    /**
     * Setzte alle werte auf den Standartwert
     *
     * @return void
     */
    private function default() {
        $this->from = null;
        $this->to = null;
        $this->tpl = null;
        $this->overwrite_color = null;
        $this->overwrite_icon = null;
        $this->overwrite_text = null;
        $this->overwrite_title = null;
        $this->overwrite_style = 3;
        $this->overwrite_mb = 3;
        $this->overwrite_mr = -1;
        $this->overwrite_mt = -1;
        $this->overwrite_ml = -1;
    }
    
    /**
     * Ersetzte ein Wert ({...}) im String des Alerts
     *
     * @param  string $from - [Pflicht] Wert der Ersetzt ersetzt werden soll
     * @param  string $to - [Pflicht] Wert der gesetzt werden soll
     * @return bool
     */
    public function r(String $from, String $to) {
        array_push($this->from, '{'.$from.'}');
        array_push($this->to, $to);
        return true;
    }
    
    /**
     * Gibt direkt ein Alert zurück (Code muss gesetzt sein)
     *
     * @param  int $code - [Pflicht] Alert nummer (alert.htm)
     * @param  int $style - [Optional] Style nummer (1-3)
     * @param  int $mt - [Optional] margin-top in Pixel
     * @param  int $mr - [Optional] margin-right in Pixel
     * @param  int $mb - [Optional] margin-bottom in Pixel
     * @param  int $ml - [Optional] margin-left in Pixel
     * @param  string $icon - [Optional] überschreibe das Icon
     * @param  string $color - [Optional] überschreibe die Farbe (alle boostrap classe ...-XXX)
     * @return mixed
     */
    public function rd(Int $code, Int $style = 3, Int $mt = -1, Int $mr = -1, Int $mb = 3, Int $ml = -1, String $icon = null, String $color = null) {
        $this->overwrite_style = $style;
        $this->overwrite_mb = $mb;
        $this->overwrite_mr = $mr;
        $this->overwrite_mt = $mt;
        $this->overwrite_ml = $ml;
        $this->overwrite_color = $color;
        $this->overwrite_icon = $icon;
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
    
    /**
     * Gibt den Alert zurück wenn der Code gesetzt wurde
     *
     * @return mixed
     */
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