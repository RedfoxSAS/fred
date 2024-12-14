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
	}
	
	public function control(){}
	
	public function __toString()
	{
		$typ = ($this->Type>0)? "h" . $this->Type : "span";
		$lbl = $this->Label;
		$txt = $this->Text;
		$str = "<div class='Control-label'>";
		$str.= "<$typ>$lbl</$typ>";
		$str.= "<div>$txt</div>";
		$str.= "<div>";
		return $str;	
	}
}
