<?php

/* Class App
 * 
 * @Autor		Raul Ramos Guzman
 * @Fecha		1/01/2023
 * @Copyright 	Redfox 2023
 * 
 * Clase abstracta para funcionalidad a toda la aplicacion
 * 
*/
namespace Fred;

include_once "User.php";
include_once "Crud.php";
include_once "MotorDbi.php";

class AppSetting
{
	public string $Name;
	public string $Title;
	public string $Server;
	public string $User;
	public string $Password;
	public string $Database;
	public string $Path;
	public string $Data;
	public string $Host;
	public string $Fred;
	public string $Web;
	
	public static function load($host,$default)
	{
		$file = "app/$host/config.json";
		if(!file_exists($file)){
			$file = "app/$default/config.json";
			$host = $default;
		}
		$json = file_get_contents($file, FILE_USE_INCLUDE_PATH);
		$data = json_decode($json,true);
		$keys = array_keys($data);
		$set = new AppSetting();
		foreach($keys as $k){
			$set->$k = $data[$k];
		}
		$set->Host = $host;
		return $set;
	}
}

abstract class App
{
	public static Crud $Crud;
	public static MotorDbi $Database;
	public static User $UserActive;

	public static AppSetting $Setting;
	
	protected static $script = array();
	protected static $styles = array();
	protected static $jsvars = array();
	protected static $Authorized = array();

	public $Db;
	public User $User;
	

	#funciones estaticas  =================================
	//setea la configuracion de la aplicacion
	public static function setting($host,$default)
	{
		App::$Setting = AppSetting::load($host,$default);
		$controlador =  App::$Setting->Path."/main.php";
		if(file_exists($controlador)){
			$inc_path = get_include_path();
			$inc_path.= PATH_SEPARATOR;
			$inc_path.= App::$Setting->Path; 
			set_include_path($inc_path);
			return $controlador;
		}else{
			return false;
		}
	}

	public static function loadCrud($modulos)
	{
		$n = new Crud(App::$Setting->Path);
		$n->load($modulos);
		App::$Crud = $n;
	}
	
	public static function loadUser()
	{
		$login = App::getVar("UserActive");
		$user = new User();
		$user->load($login);
		App::$UserActive = $user;	
	}
		
	//guarda una variable a nivel de session
	public static function setVar($var,$value)
    {
		$varname = "fred_" . $var;
		$_SESSION[$varname] = $value;
	}
    
    //devuelve una variabla almacenada en la sesion
    public static function getVar($var)
    {
		$varname = "fred_" . $var;
		if(!isset($_SESSION[$varname])){
			 $_SESSION[$varname] = false;
		}
		return $_SESSION[$varname];
	}
	
	//setea la pagina anterior en la pagi
	public static function backPage()
	{
		$actual = $_SERVER["REQUEST_URI"];
		$actual = '/' . trim($actual, '/');
		$partes = explode("/",$actual);
		array_pop($partes);
		$uri = implode("/",$partes);
		App::setVar("UriBack",$uri);
		App::$jsvars[] = "uriBack = \"$uri\";";
	}
	
	public static function dbname()
	{
		if(!empty(App::$UserActive->Db)){
			return App::$UserActive->Db;
		}else{
			return App::$Setting->Database;
		}
	}
	
	# Funciones abastractas que se deben implementar ===============
	abstract protected function startComponents();
	
	#funciones heredables ================================
	//constructor de la aplicacion
	public function __construct()
	{
		if(!empty(App::$UserActive)){
			$this->User = App::$UserActive;
		}else{
			$this->User = new User();
		}
		$this->authorize("back",true);
	}

	//devuelve las variables del objeto actual
	public function expose() {
        return get_object_vars($this);
    }
	
	//agrega un script a la lista de scrips a cargar
	public function script($path,$type="javascript")
	{
		App::$script[] = "<script type=\"text/$type\" src=\"$path\"></script>";	
	}
	
	//agrega un stilo a la lista de estilos cargar
	public function style($path,$type="css")
	{
		App::$styles[] = "<link rel=\"stylesheet\" type=\"text/$type\" href=\"$path\"/>";		
	}
	
	//agrega una variable javascript
	public function jsvar($name,$value)
	{
		App::$jsvars[] = "$name = $value;";
	}
	
	//autoriza o valida el permiso de una funcion a un usuario
	protected function authorize($method,$mode=false)
	{
		if($mode===false){
			$r = false;
			if(isset(App::$Authorized[$method])){
				if(App::$Authorized[$method] === true){
					$r = true;
				}
			}else{
				$r = App::$UserActive->authorize($method);
			}
			return $r;
		}else if($mode===true){
			App::$Authorized[$method] = true;
		}
	}
	
	//regresa a la pagina anterior

	
	//regresa a la pagina anterior
	public function back()
	{
		$this->location($this->getVar("UriBack"));
	}	
	
	####    NO REVISADOS    ########
	public function location($location)
	{
		$this->jsvar("window.location.href","'$location'");
	}
	
	protected function captureData()
	{
		if(!$this->getVar("DataFlag")){
			if(!isset($_SESSION["GET"])){$_SESSION["GET"] = $_GET;}
			if(is_array($_SESSION["GET"])){
				$_SESSION["GET"] = array_merge($_SESSION["GET"],$_GET);
			}else{
				$_SESSION["GET"] = $_GET;
			}
			$datos = array_merge($_SESSION["GET"],$_POST);
			if(isset($datos["rframeQueryBtn"])){
				$_SESSION["rframeQuery"] = $datos["rframeQuery"];
			}
			$this->setVar("Data",$datos);
			$this->setVar("DataFlag",true);
		}
	}

}

