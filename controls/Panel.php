<?php
/**
 * Panel.php
 * 
 * @author		Raul E. Ramos Guzman
 * @package		Form
 * @copyright	14/04/2023
 * @version 	Release: 1.0
 * 
*/

namespace Fred;

include_once "Control.php";

class Panel extends Control
{
	
	protected $Controls = array();
	
	public function __construct()
	{
		parent::__construct(false, 0);
	}
	
	public function control(){}

	public function __toString()
	{
		$str = "<div class='form-panel'>";
		$str.= "<table><tr>";
		for($i=0;$i<20;$i++){
			$str.= "<th></th>";
		}
		$str.= "</tr><tr>";
		$cols = 0;
		$colm = array();
		$i = 1;
		foreach($this->Controls  as $control){
			$w = $control->Width;
			$h = $control->Height;
			$cols+=$w;
			if($cols > 20){
				$i=$i+1;
				for($n = $cols - $w; $n<20; $n++){
					$str.= "<td></td>";
				}
				$cols = $w;
				if(isset($colm[$i])) { $cols+= $colm[$i];}
				$str.="</tr><tr>";
			}
			$str.= "<td rowspan='$h' colspan='$w'>$control</td>";
			
			if($h > 1 ){
				for($j=$i ; $j < ( $i + $h ) ; $j++){
					if(!isset($colm[$j])){
						$colm[$j] = $w;
					}else{
						$colm[$j]+= $w;
					}
				}
			}
		}
		for($n = $cols ; $n<20; $n++){
			$str.= "<td></td>";
		}
		$str.= "</tr></table></div>";
		return $str;
	}
	
	public function add(Control $control, $col = false, $row= false)
	{
		
		if($col != false && is_numeric($col)){
			$control->Width = $col;
		}
		if($row != false && is_numeric($row)){
			$control->Height = $row;
		}
		if($control instanceof Panel){
			$this->Controls[] = $control;
			return true;
		}

		if(empty($this->Controls[$control->Name])){
			$this->Controls[$control->Name] = $control;
		}else{
			if(strlen($control->Source)>1){
				$this->Controls[$control->Name]->Source.= "," . $control->Source;
				//echo $this->Controls[$control->Name]->Source . "<br>";
			}
		}
	}
	
	public function text($data=false)
	{
		if($data!=false ){
			$controls = $this->Controls;
			foreach($controls as $control){
				if($control instanceof Control){
					if(!empty($data)){
						if($control->Source != false){
							$field = $control->Source;
							if(isset($data->$field)){
								if(!is_null($data->$field)){
									$control->text($data->$field);
								}
							}else if(!is_null($control->TextDefault)){
								$control->Text = $control->TextDefault;
							}
						}
					}
				}
			}
		}else{
			$clas = new ModelJson();
			$controls = $this->Controls;
			foreach($controls as $control){
				if($control->Source != false){
					$field = $control->Source;
					$clas->$field = $control->text();
				}
			}
			//$json = json_encode($clas);
			return (string) $clas;
		}
	}

	public function clear()
	{
		$this->Controls = array();
	}
	
}
