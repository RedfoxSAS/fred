<?php
/**
 * 
 * datebox.php
 * 
 * @author		Raul E. Ramos Guzman
 * @date		14/04/2023
 * @version 	Release: 1.0
 * 
*/

namespace Fred;

include_once "Control.php";

class Datebox extends Control
{
	private $F = array();
	private $T = array();
	public $Format = "date";
	
	public function __construct($label=false,$t=1,$w=3,$h=1)
	{
		parent::__construct($label, $t, $w, $h);
		$this->F["date"] = "Y-m-d";
		$this->F["time"] = "H:i";
		$this->F["datetime"] = "Y-m-d H:i";
		$this->T["date"] = "date";
		$this->T["time"] = "time";
		$this->T["datetime"] = "datetime-local";
	}
	
	public function text($text=false)
	{
		$f = $this->Format;
		$format = (isset($this->F[$f]))? $this->F[$f] : "Y-m-d" ;
		if($text===false){
			if(!$this->Text){
				$this->Text = date($format);
			}
		}else{
			$this->Text = $text;
		}
		$fecha = strtotime($this->Text);
		if($fecha < 0){
			$this->Text = date($format);
		}else{
			$this->Text = date($format,$fecha);
		}
		return $this->Text;
	}

	public function control()
	{
		$f = $this->Format;
		$typ = (isset($this->T[$f]))? $this->T[$f] : "date" ;
		$val = $this->text();
		$atr = $this->attrib();
		$atr.= " placeholder='" . date("Y-m-d") . "'";
		$str = "<input class='form-control'  type='$typ' value='$val' $atr>";			
		return $str;
	}
}
