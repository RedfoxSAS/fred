<?php
/**Button.php
 * 
 * @author		Raul E. Ramos Guzman
 * @package		Form
 * @copyright	15/04/2019 Fred
 * @version 	Release: 1.0
*/

namespace Fred;

include_once "Control.php";

class Button extends Control
{
	private $Command;
	private $Lang = "java";
	
	private $_classExtra = "";
	
	public $Icon = false;
	public $Target = "_self";	
	public $ignore = true;
	
	public function control(){}
	
	protected function attrib()
	{
		$str = " id='" . $this->Id . "'";
		//$str.= " name='" . $this->Name . "'";		
		if(count($this->Events)>0){
			$keys = array_keys($this->Events);
			foreach($keys as $event){
				$str.= " on$event=\"javascript:" . $this->Events[$event] . "\" ";
			}
		}
		return $str;
	}	
	
	public function link($link,$icon=false,$target="_blank")
	{
		$this->Command = $link;
		$this->Lang = "link";
		if($icon!=false) {$this->Icon = $icon;}
		$this->Target = $target;
	}
	
	public function command($comando,$icon = false) 
	{
		$this->Command = $comando;
		$this->Lang = "php";	
		if($icon!=false) {$this->Icon = $icon;}
	}
	
	public function __toString()
	{
		$name = ($this->Lang=="php")? $this->Command : $this->Name;
		$com = $this->Comment;
		$cla = $this->getClass($this->Type);
		$val = $this->text();
		$atr = $this->attrib();
		$atr.= " class='btn btn-$cla' ";
		$atr.= " title='$com' ";
		$atr.= " style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;'";
		$icon = ($this->Icon!=false)?  "<i class='fa fa-" . $this->Icon . "'></i>":"";
		$id = $this->Id;
				
		if($this->Lang=="php"){
			$str = "<button type='submit' name='$name' value='$val' $atr ";
			$str.= ">$icon " . $this->Label . "</button>";		
		}else if($this->Lang=="link"){
			$target = $this->Target;
			$str = "<a value='$val' $atr target='$target' ";
			$str.= " href='" . $this->Command ."'";
			$str.= ">$icon " . $this->Label . "</a>" ;
		}else{
			$lbl = $this->Label ;
			$str = "<button type='button' name='' value='' $atr >$icon $lbl</button>";
			$str.= "<input id='Cmd$id' type='hidden' name='' value='$val'>";
			$str.= "<script>function CmdYes$id(){_id('Cmd$id').name = '$name';}</script>";
			$str.= "<script>function CmdNo$id(){_id('Cmd$id').name = '';}</script>";
		}
		return $str;			
	}


	public function getClass($type)
	{
		$tipos = array("primary","secondary","success","danger","warnign","info","light","dark","link");
		if(isset($tipos[$type])){
			return $tipos[$type];
		}
		return "light";
	}
	
	public function small()
	{
		$this->_classExtra = "btn-sm";
	}
}
		
