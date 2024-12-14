<?php
/**
 * 
 * rFileBox.php
 * 
 * @author		Raul E. Ramos Guzman
 * @package		Form
 * @copyright	15/05/2019 rframe
 * @version 	Release: 1.0
 * 
*/
include_once "rControl.php";

class rFileBox extends rControl
{
	
	public $flag_folder = false;
	public $accept = false;
	public $folder;
	public $sourcename = false;
	
	public $filetype = false;
	public $filename = false;
	public $filepath = false;
	public $directory;
	public $prefix = false;
	public $status = false;
	
	public $folios = 0;
	
	public function control()
	{
		$accept = "";
		if($this->accept){$accept = " accept='" . $this->accept . "'" ;}
		$atr = $this->_getAttrib();
		$cls = $this->_getClass();
		$txt = $this->text;

		if(file_exists($this->text)){
			$name = $this->text;
			$this->comment = "<a href=\"javascript:view_document('$name');\" >Ver archivo</a>";
		}
				
		//$html = "<input type='hidden' name='MAX_FILE_SIZE' value='8000000'>";
		$html = "<input type='file' class='form-control $cls' $atr $accept>";
		return $html;
	}
	
	public function setFolder($folder)
	{
		$c = substr($this->folder,-1,1);
		if($c == "/"){
			$folder = substr($this->folder,0,-1);
		}
		if(!file_exists($folder)){
			mkdir($folder,0777);
		}
		$this->folder = $folder . "/";
		$this->flag_folder = true;
	}
	
	public function setText($text=false)
	{
		if(file_exists($text)){
			$a = explode("/",$text);
			$u = count($a)-1;
			$name = $a[$u];
			unset($a[$u]);
			$this->filepath = implode("/",$a) . "/";
			$n = explode(".",$name);
			$this->filename = $n[0];
			$this->filetype = $n[1];
			parent::setText($text);
		}
	}
	
	public function saveFile($nombre)
	{
		
		if(!empty($_FILES[$this->name]['tmp_name'])){
			if($this->filename==false){
				if(!$this->flag_folder) { $this->setFolder($this->folder);}
				$name = $_FILES[$this->name]['name'];
				$items = explode(".",$name);
				$folder = $this->folder;
				$extend = $items[count($items)-1];
				if($this->prefix!=false){ $nombre = $this->prefix . $nombre;}
				$this->filepath = $folder;
				$this->filename = $nombre;
				
				$this->filetype = $extend;
			}else{
				$folder = $this->filepath;
				$nombre = $this->filename;
				$extend = $this->filetype;
			}
			if(!$nombre){$nombre = "F" . date("Ymdhms");}
			
			$fichero = $folder . $nombre . "." . $extend;
			if (copy($_FILES[$this->name]['tmp_name'], $fichero)) {
				$this->text($fichero);
				unlink($_FILES[$this->name]['tmp_name']);
				$this->text($fichero);
				$this->status = true;
				return true;
			}
			$this->text($fichero);
			return false;
		}
		return false;
	}
	
}
