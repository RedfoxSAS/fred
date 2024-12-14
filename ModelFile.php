<?php

/* Class ModelFile
 * Autor: Raul Ramos
 * Fecha: 12/10/2023
 * CopyRight: Redfox
 * 
*/
namespace Fred;

include_once "App.php";

class ModelFile
{
	protected $Db = false;
	protected $Type = "";
	protected $File = "";
	protected $Folder = false;
	protected $Ext = "txt";
	public $Text = "";
	
	public function __construct($ext="txt",$folder=false,$db=false)
	{
		$this->Db = $db;
		$this->Folder = $folder;
		$this->Ext = ".$ext";
	}
	
	public function setName($model,$field=false)
	{
		$this->Type = str_replace("\\","",get_class($model));
		$this->File = App::$Setting->Data   ;
		$this->File.= ($this->Db==true)?  "/" . App::dbname(): "";	
		
		$key = $model->Setting->Key;
		$this->File.= ($this->Folder!=false)? "/".$this->Folder."/" : "" ;		
		$this->File.= $this->Type . ".";
		$this->File.= $model->$key ;
		$this->File.= (!empty($field))? ".$field".$this->Ext : $this->Ext ;		
		//echo $this->File."<br>";
	}

	public function save()
	{
		file_put_contents($this->File, $this->Text);
	}
	
	public function open()
	{
		if(file_exists($this->File)){
			@$this->Text = file_get_contents($this->File);
		}		
	}
	
	public function getText()
	{
		return $this->Text;		
	}
	
	public function setText($text)
	{
		$this->Text = $text;
	}

	public function __toString()
	{
		return $this->Text;	
	}
}