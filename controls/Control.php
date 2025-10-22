<?php
/**
 * 
 * Control.php
 * 
 * @author		Raul E. Ramos Guzman
 * @package		Form
 * @copyright	15/04/2023
 * @version 	Release: 1.0
 * 
*/

namespace Fred;

abstract class Control //extends rRoot
{
	public static $Numero = 0;
	
	public $Id;
	public $Label;
	public $Type;
	protected $Events = array();
	protected $Help = "";
	protected $Helpi = "black";
	
	public $Name;
	public $Source = false;
	public $Width = 3;
	public $Height= 1;
	public $Text;
	public $Comment = "";
	public $Capital = false;
	public $TextDefault = "";
	public $Active = true;

	//public $ignore = false;

	abstract public function control();
	
	/*Tipos de controles
	 * 0: Dato No requerido
	 * 1: Dato Requerido
	 * 2: Solo lectura
	 * 3: Oculto
	 */
	 
	public function __construct($label=false,$t=1,$s=false,$c="")
	{
		Control::$Numero++;
		$this->Id = get_class($this) . Control::$Numero;
		$this->Id = str_replace("Fred\\","",$this->Id);
		$this->Label = $label;
		$this->Name = ($label)? str_replace(" ","",$label) : $this->Id;
		$this->Type = $t;
		$this->Source = $s ; 
		$this->Comment = $c ;
	}
	
	//obtiene o setea el text del control
	public function text($text=false)
	{
		if($text===false){
			return (string) $this->Text;
		}else{
			if($text instanceof ModelStatic){
				$text = $text->Id;
			}else if($text instanceof Model){
				$key = $text->setting()->Key;
				$text = $text->$key;
			}
			if(is_array($text)){
				$text = implode(",",$text);
			}
			if($this->Capital){
				$text = strtoupper($text);
			}
			$this->Text = $text;
		}
		//echo $this->Text;
	}
	
	//agrega eventos js al control
	public function event($event,$function)
	{
		$this->Events[$event] = $function;
	}
	
	/* Estilos de la ayuda
	 * primary, secondary, success, danger, warning, muted
	 * info, light, dark, body, white, white-50, black-50
	 */
	public function help($text,$class="warning")
	{
		$this->Help = $text;
		$this->Helpi= $class;
	}
	
	//devuelve los atributos del control
	protected function attrib()
	{
		$str = " id='" . $this->Id . "'";
		$str.= " name='" . $this->Name . "'";		
		$str.= ($this->Type==1)? " required ":"";
		$str.= ($this->Type==2)? " readonly ":"";
		if(count($this->Events)>0){
			$keys = array_keys($this->Events);
			foreach($keys as $event){
				$str.= " on$event=\"javascript:" . $this->Events[$event] . "\" ";
			}
		}
		return $str;
	}	
	
	//muestra el control en forma de texto
	public function __toString()
	{
		$id = $this->Id;
		$hlp = $this->Help;
		$hli = $this->Helpi;
		$lbl = $this->Label;
		$req = ($this->Type==1)? "<span class='control-required'>*</span>" : "";
		$hid = ($this->Type==3)? " style='display:none' ":"";
		$ctr = $this->control();
		$str = "<label$hid>";
		$str.= "<div>$lbl $req </div> $ctr ";
		$str.= "<small class='help-block text-$hli' id='Small$id'>$hlp</small>";
		$str.= "</label>";
		return $str;
	}

}
