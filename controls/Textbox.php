<?php
/**
 * TextBox.php
 * @Autor		Jimena Duarte Quiceno
 * @Fecha		13/04/2023 
 * @Copyright 	Redfox 2023
*/

namespace Fred;

include_once "Control.php";

class Textbox extends Control
{
	public $Rows = 1;
	public $Length = false;
	
	public function control()
	{
		$val = $this->text();
		$atr = $this->attrib();
		$atr.= " placeholder='" . $this->Comment . "'";
		if($this->Length != false && is_numeric($this->Length)){
			$atr.= " maxlength='" . $this->Length . "'";
		}
		$str = "<input class='form-control'  type='text' value='$val' $atr>";
		if($this->Rows > 1){
			$atr.= " rows='" . $this->Rows . "'";
			$str = "<textarea class='form-control' $atr>$val</textarea>";
		}			
		return $str;	
	}
}
