<?php
/**
 * 
 * rMailBox.php
 * 
 * @author		Raul E. Ramos Guzman
 * @package		Form
 * @copyright	15/04/2019 rframe
 * @version 	Release: 1.0
 * 
*/
namespace Fred;

include_once "Control.php";

class Mailbox extends Control
{
	public $Multiple = true;
	
	public function control()
	{
		$val = $this->text();
		$atr = $this->attrib();
		$atr.= " placeholder='nombre@dominio.com'";
		$atr.= ($this->Multiple==true)? " multiple ": "";
		$str = "<input class='form-control'  type='email' value='$val' $atr>";			
		return $str;	
	
	}	
}
