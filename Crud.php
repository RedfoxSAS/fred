<?php
/* Class Crud
 * 
 * Carga los cruds de los modulos
 * 
 * Autor: Raul Ramos
 * Fecha: 8/4/2019
 * 
 * */
 
namespace Fred;

class Crud
{
	private $path;
	public $Items = array();
	public $Menu = array();
	public $Modulos = array();
	
	public function __construct($path)
	{
		$this->path = $path;
	}
	
	public function load($modulos="")
	{
		$path =  $this->path;
		$dirs = scandir($path);
		//echo $modulos;
		foreach($dirs as $dir){
			$mod = $path."/".$dir;
			
			if(is_dir($mod)){
				$file = $mod . "/crud.json";
				@$text = file_get_contents($file,FILE_USE_INCLUDE_PATH);
				if($text!=false){
					$obj = json_decode($text);
					$grupos = get_object_vars($obj);
					if(isset($grupos["menu"])){
						$menus = (array) $grupos["menu"];
						$keys = array_keys($menus);
						foreach($keys as $key){	
							
							if(!empty($this->Menu[$key])){
								$this->Menu[$key] = array_merge($this->Menu[$key], $grupos["menu"]->$key);
							}else{
								$this->Menu[$key] = $grupos["menu"]->$key;
							}	
						}
						unset($grupos["menu"]); 
					}
					if(isset($grupos["title"])){
						$this->Modulos[$dir] = $grupos["title"];
						$this->Items[] = $grupos["title"];
						unset($grupos["title"]); 
					}
					$keys = array_keys($grupos);

					foreach($keys as $key){
						
						$sub = (array) $grupos[$key];
						if(isset($sub["title"])){
							array_unshift($sub, $sub["title"]);
							unset($sub["title"]); 
						}
						if(strlen($modulos)<1 || strpos($modulos,$key)!==false){
							$this->Items = array_merge($this->Items,  $sub);
						}
					}
				}
			}
		}
	}
	
	public function loadModules()
	{
		$path =  $this->path;
		$dirs = scandir($path);
		$modulos = array();
		foreach($dirs as $dir){
			$mod = $path."/".$dir;

			if(is_dir($mod)){
				$file = $mod . "/crud.json";
				@$text = file_get_contents($file,FILE_USE_INCLUDE_PATH);
				if($text!=false){
					$obj = json_decode($text);
					$grupos = get_object_vars($obj);
					if(isset($grupos["menu"])){
						unset($grupos["menu"]); 
					}
					if(isset($grupos["title"])){
						$modulos[] = $grupos["title"];
						unset($grupos["title"]); 
					}else{
						$modulos[] = $mod;
					}
					$keys = array_keys($grupos);
					foreach($keys as $key){
						$sub = (array) $grupos[$key];
						if(isset($sub["title"])){
							$modulos[$key] = $sub["title"];
						}else{
							$modulos[$key] = $key;
						}
					}
				}
			}
		}
		return $modulos;		
	}

	
	public function get($name)
	{
		$crud = (isset($this->Items[$name]))? $this->Items[$name]:false;
		return $crud;
	}
	
	public function link($name,$link,$icon = false,$title = false)
	{
		$this->Items[$name][1] = (string) $link;
		if($icon!=false){
			$this->Items[$name][2] = $icon;
		}
		if($title!=false){
			$this->Items[$name][0] = $title;
		}
	}

	public function linkFather($name,$father)
	{
		if(!empty($this->Items[$father])){
			$coincidencias = preg_grep("/" . preg_quote($name, "/") . "/i", array_keys($this->Items));
			$lista = array_values($coincidencias); 
			foreach($lista as $key){
				$this->Items[$key][1] = $this->Items[$father][1].$this->Items[$key][1];
			}
		}
	}
	
}

