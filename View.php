<?php

/* View .php
 * 
 * Carga una plantilla para imprimier en pantalla
 * 
 * @Autor: Raul Ramos
 * @Fecha: 05/05/2016
 * @Actualizada: 07/04/2019
 * 
 * */
 
namespace Fred;

include_once "App.php";

Class View
{
	public $Text;
	private $vars = array();
	private $_path ;	
	private $_objects = array();
	public $exists = false;
	
	#contructor, carga un archivo de texto con la vista
	public function __construct($str=false)
	{
		if($str!=false){
			if(@$text = file_get_contents($str,FILE_USE_INCLUDE_PATH)){
				$this->Text = $text;
			}else{
				$this->Text = $str;
			}
		}
		
	}
	
	public function setVar($var, $val=false)
	{
		if(is_array($var)){
			$keys = array_keys($var);
			foreach($keys as $k){
				$this->setVar($k, $var[$k]);
			}
		}else{
			$val = (is_array($val))? implode("",$val): $val;
			$this->vars[$var] = $val;
		}
	}
	
	public function proccess(){
		$this->vars["My"] = "/" . App::$Setting->Host . "/usr/" . App::dbname();
		$this->Text = str_replace("'","\'",$this->Text);
		$this->Text = str_replace("\r","",$this->Text);
		$this->Text = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $this->Text);
		reset($this->vars);
		while( list($key, $val) = each($this->vars)){
			$$key = $val;
		}
		eval ("\$this->Text = '$this->Text';");
		while( list($key, $val) = each($this->vars)){
			unset($$key);
		}
		$this->Text = str_replace("\'","'",$this->Text);
		return $this->Text;
	}
	
	public static function clean($text)
	{
		$text = str_replace("<html>","",$text);
		$text = str_replace("</html>","",$text);
		$text = str_replace("<body>","",$text);
		$text = str_replace("</body>","",$text);
		$text = str_replace("<head>","",$text);
		$text = str_replace("</head>","",$text);
		$text = str_replace("<!DOCTYPE html>","",$text);
		return $text;
	}
	
	public function __toString()
	{	
		return $this->proccess();
	}
	
}
