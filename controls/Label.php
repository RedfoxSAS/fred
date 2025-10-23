<?php
/**
 * 
 * TextBox.php
 * 
 * @author		Raul E. Ramos Guzman
 * @package		Form
 * @copyright	2023
 * @version 	Release: 1.0
 * 
*/

namespace Fred;

include_once "Control.php";

class Label extends Control
{
	
	public function __construct($label=false,$t=1,$s=false,$c="")
	{
		parent::__construct($label,$t,$s,$c);
		$this->Active=false;
		$this->Separator = "";
	}
	
	public function control(){}
	
	public function __toString()
	{
		$hid = ($this->Type==3)? " style='display:none' ":"";
		$typ = ($this->Type>0)? "h" . $this->Type : "span";
		$lbl = $this->Label;
		$txt = $this->text();
		$str = "<div class='Control-label' $hid>";
		$str.= "<$typ>$lbl</$typ>";
		$str.= "<div>$txt</div>";
		$str.= "<div>";
		return $str;	
	}
}
