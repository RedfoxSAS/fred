<?php
/**
 * 
 * Number.php
 * 
 * @author		Raul E. Ramos Guzman
 * @copyright	April 14 of 2023 
 * @version 	Release: 1.0
 * 
*/

namespace Fred;

include_once "Control.php";

class Numbox extends Control
{
	public $Max = 999999999999;
	public $Min = 0;
	public $Step = 1;
	public $Text = 0;
	
	public function range($min,$max,$step=1)
	{
		$this->Min = $min;
		$this->Max = $max;
		$this->Step = $step;
		if(empty($this->Text)){
			$this->Text = $min;
		}
	}
	
	public function text($text=false)
	{
		if($text===false){
			return (int) $this->Text;
		}else{
			$this->Text = ($text < $this->Min)? $this->Min : $text;
			$this->Text = ($text > $this->Max)? $this->Max : $text;
		}
	}	
	
	public function control()
	{
		$val = $this->text();
		$atr = $this->attrib();
		$atr.= " min='" . $this->Min . "'";
		$atr.= " max='" . $this->Max . "'";
		$atr.= " step='" . $this->Step . "'";
		$str = "<input class='form-control'  type='number' value='$val' $atr>";			
		return $str;		
	}
}
