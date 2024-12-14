<?php
/* User.php
 * 
 * Clase utilizada para modelar los usuarios del sistema
 * @author: Raul Ramos
 * @date: 21/04/2023
 * 
*/
namespace Fred;

include_once "Model.php";

class Profile extends Model
{
	public int $PerfilId = 0;
	public string $Nombre = "";
	public string $Titulo = "";
	public string $Permisos = "";
	public string $Tipo = "";

	public function __construct()
	{
		parent::__construct();
		$this->table("acc_perfiles","PerfilId");
	}	
	
}


Class User extends Model
{
	
	public string $UsuarioId = "";
	public string $Nombre = "";
	public string $Login = "";
	public string $Password = "";
	public string $Perfil = "";
	public string $Referencia = "";
	public string $Db = "";
	public string $Modulos="";
		
	public function __construct()
	{
		parent::__construct();
		$this->table("acc_usuarios","UsuarioId");
	}
	
	public function load($login)
	{
		$file = App::$Setting->Fred . "/app/";
		$file.= App::$Setting->Host . "/users/";
		$file.= $login . ".json";

		@$json = file_get_contents($file);
		if(!empty($json)){
			$data = json_decode($json,true);
			$keys = array_keys($data);
			foreach($keys as $k){
				$this->$k = $data[$k];
			}
			$this->Setting->Exists = true;
			$this->permission();
		}
		$this->Login = $login;
	}

			
	#valida la contraseña del usuario
	public function password($pw)
	{
		$p1 = $pw;
		$p2 = md5($pw);
		$p = $this->Password;
		if( $p==$p1 || $p == $p2 )
			return true;
		return false;
	}
	
	#asignar los permisos del suario
	public function permission()
	{
		if(!empty($_SESSION[$this->Login])){
			$this->authorized = $_SESSION[$this->Login];
			return true;
		}
		$this->authorized = array();
		$perfiles = explode(",",$this->Perfil);
		foreach($perfiles as $perfil){

			$file = App::$Setting->Fred . "/app/";
			$file.= App::$Setting->Host . "/profiles/";
			$file.= $perfil . ".json";
			if(file_exists($file)){
				
				@$json = file_get_contents($file);
				$data = json_decode($json,true);
				foreach($data as $d){
					//echo $d."<br>";
					$this->authorize($d,true);
				}
			}
		}
		//carga perfil especifico de permisos para el cliente.
		/*
		$file = App::$Setting->Fred . "/app/";
		$file.= App::$Setting->Host . "/profiles/";
		$file.= $this->Login . ".json";
		if(file_exists($file)){
			@$json = file_get_contents($file);
			$data = json_decode($json,true);
			foreach($data as $d){
				
				$this->authorize($d,true);
			}
		}*/
		$_SESSION[$this->Login] = $this->authorized;
	}
	
	#autorizar el modulo de un usurio
	public function authorize($method,$mode=false)
	{
		if($mode===false){
			if (!empty($this->Perfil)) {
				if(strpos("godmode",$this->Perfil)!==false){
					return true;
				}
			}
			if(isset($this->authorized[$method])){
				if($this->authorized[$method] === true){
					return true;		
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else if($mode===true){
			$this->authorized[$method] = true;
		}
	}
	
	public function headView()
	{
		$str = "<div>";
		$str.= "<a href='/logoff' style='color:white;margin.right:8px;'>";
		$str.= "<i class='fa fa-door-open'></i>";
		$str.= "</a> ";
		$str.= $this->Nombre;
		$str.= "</div>";
		return $str;
	}
		
}

