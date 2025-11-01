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
	protected $Field = false;

	protected $Key = false;
	
	private $vars = array();
	
	public $Text = "";
	public $Path = "";
	public $Base = "";
	public $Name = "";
	
	public function __construct($ext="txt",$folder=false,$db=false,$key=true,$field=true,$type=true)
	{
		$this->Db = $db;
		$this->Folder = $folder;
		$this->Ext = ".$ext";
		$this->Field = $field;
		$this->Key = $key;
		$this->Type = $type;
	}
	
	public function setName($model,$field=false)
	{
		
		$this->Base = App::$Setting->Data   ;
		if($this->Db===true){
			$this->Base.= "/" . App::dbname();
		}else if($this->Db===false){
			$this->Base.= "";
		}else{
			$this->Base.= "/" . $this->Db;
		}

		if($this->Folder!=false){
			$w = new View();
			$w->Text = $this->Folder;
			$data = $model->dataview();
			$w->setVar($data);
			$this->Path = (string) $w;
		}

		$this->Type = ($this->Type)? str_replace("\\","",get_class($model)):"";
		$key = $model->setting()->Key;
		$name = $this->Type;
		$name.= ($this->Key)? ".".$model->$key: "";
		$nf =  $this->Field;
		if ($nf === true) {
			$nf = $field;
		} elseif ($nf != false) {
			$nf = $model->$nf;
		}
		$name.= (!empty($name))? "." . $nf : $nf;
		$name.= $this->Ext;
		$this->Name = $name;

		$this->File = $this->Base . "/" . $this->Path . "/" . $name;
		$dir = $this->Base . "/" . $this->Path;
		if (!is_dir($dir)) {
			// Intentar crear el directorio con permisos (por ejemplo, 0755)
			if (mkdir($dir, 0777, true)) {
			}
		}
		//echo "<br>" .  $this->File;
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
		return (string) $this->Text;	
	}

	public function getPath()
	{
		return $this->Base . "/" . $this->Path;
	}
	
}
