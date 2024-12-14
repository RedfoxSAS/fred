<?php

/* Class Numerator
 * Autor: Raul Ramos
 * Fecha: 12/10/2023
 * CopyRight: Redfox
 * 
*/
namespace Fred;

include_once "App.php";
include_once "ModelFile.php";

class Numerator extends ModelFile
{
  
	private $Last = 0;
  
	public function setName($model,$field="")
	{
		parent::setName($model);
		$this->File.= "/" . $this->Type . ".json";
		$file = $this->File;
		if(file_exists($file)){
			@$json = file_get_contents($file);
			$data = json_decode($json);
			$this->Last = $data->Text;
		}
	}

	public function getText()
	{
		if(empty($this->Text)){
			$this->Text = $this->Last + 1;
		}
		return (string) $this->Text;
	}
	
	public function save($name=false)
	{
		if($this->Text > $this->Last){
			$file = $this->File;
			$json = json_encode($this);
			file_put_contents($file,$json);
		}
	}
}
