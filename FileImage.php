<?php

/* Class FileImage: archvio de imagen png
 * Autor: Raul Ramos
 * Fecha: 12/10/2023
 * CopyRight: Redfox
 * 
*/
namespace Fred;

include_once "App.php";
include_once "ModelFile.php";

class FileImage extends ModelFile
{
	public $Default;
	
	public function setText($text)
	{
		if(strlen($text)>20){
			$this->Text = $text;
		}
	}
	
	public function save()
	{
		//if(!is_null($this->Text)){
			@list(, $Base64Img) = explode(';', $this->Text);
			@list(, $Base64Img) = explode(',', $Base64Img);
			$Base64Img = base64_decode($Base64Img);
			file_put_contents($this->File, $Base64Img);
		//}
	}
	
	public function open()
	{
		if(file_exists($this->File)){
			@$b64 = file_get_contents($this->File);
			if(!empty($b64)){
				$str = "data:image/png;base64,";
				$str.= base64_encode($b64);
				$this->Text = $str;
			}
		}
	}
}
