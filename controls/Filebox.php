<?php

namespace Fred;

include_once "Control.php";

class Filebox extends Control
{
	public $Accept = "";

	private $N = 0;
	private $file;
	private $url;
	private $ReturnPath = false; 

	public function __construct($label=false,$t=1,$s=false,$c="")
	{
		parent::__construct($label,$t,$s,$c);
		$this->N = Control::$Numero;
		$this->Name = "file_data" . $this->N;

		$host = App::$Setting->Host;
		$user = App::$UserActive->Login;
		$this->url = "/$host/tmp/out.$user."  . $this->Id ;
		$this->file = "d:/xampp/htdocs". $this->url;
		
	}

	public function accept($ext,$mode=false)
	{
		$this->Accept = $ext;
		$this->ReturnPath = $mode;
	}
		
	public function control()
	{
		$atr = $this->attrib();
		$id = $this->Id;
		$txt = $this->text();
		$acp = $this->Accept;
		$str = "";
		//$str.= "<div style='display:block'>";
		$str.= "<input class='form-control' type='file' id='$id' name='$id' accept='$acp'>";
		//$str.= "</div>";
		return $str;
	}
	
	public function __toString()
	{
		$this->help("No hay formato cargado");
		if(!empty($this->Text)){
			$url = $this->url;
			$extension = ".html";
			if ($this->ReturnPath === true) {
				if ($this->ReturnPath === true && file_exists($this->Text)) {
					$extension = "." . pathinfo($this->Text, PATHINFO_EXTENSION);
					copy($this->Text, $this->file . $extension);
				}
			}else{
				//$my = "/" . App::$Setting->Host . "/usr/" . App::dbname();
				//$Text = str_replace("{My}",$my,$this->Text);
				file_put_contents($this->file . $extension , $this->Text);
			}				
			$url.= $extension;
			$this->help("<a href=\"javascript:onclick:modal_print('$url')\">Ver documento</a>");
		}
		$hlp = $this->Help;
		$hli = $this->Helpi;
		$lbl = $this->Label;
		$req = ($this->Type==1)? "<span class='control-required'>*</span>" : "";
		$hid = ($this->Type==3)? " style='display:none' ":"";
		$ctr = $this->control();
		$str = "<label$hid>";
		$str.= "<div>$lbl $req :</div>$ctr</label>";
		$str.= "<small class='help-block text-$hli'>$hlp</small>";
		$str.= ""   ; 
		return $str;
	}

	public function text($text=false)
	{
		if($text===false){
			if(!empty($_POST)){
				$txt = $this->readFile();
				if($txt!=false){
					$this->Text = $txt;
					return $txt;
				}
			}
			return $this->Text;
		}else{
			parent::text($text);
		}
	}
	
	public function readFile()
	{
		if (!empty($_FILES[$this->Id]['tmp_name'])) {
			$name = $_FILES[$this->Id]['tmp_name'];

			if ($this->ReturnPath === true) {
				// Devuelve ruta temporal para archivos binarios
				return $name;
			}

			$text = @file_get_contents($name);
			return (!empty($text)) ? $text : false;

		} else {
			// No se subió archivo nuevo, revisa archivo temporal
			if ($this->ReturnPath === true) {
				return false;
			}
			$text = @file_get_contents($this->file);
			return (!empty($text)) ? $text : false;
		}
	}

}
