<?php
/**
 * Program.php version 4
 * 
 * Modela el programa principal del sistema
 * 
 * @Autor:	Raul E. Ramos Guzman
 * @Fecha:	05/05/2024
 * 
 * */

namespace Fred;

include_once "App.php";
include_once "MotorMySql.php";
include_once "View.php";
include_once "Modal.php";
include_once "Panel.php";
include_once "Nav.php";
include_once "Controller.php";
 
abstract class Program extends App
{		
	public static $num = 0;
	public static $Panel = "";
	public static $View = "";
	
	private $Body = array();
	private $Menu ;

	protected $Logo = "/fred/assets/images/logo.png";
	protected $Icon = "/fred/assets/images/favicon.ico";
	protected $Look = "/fred/assets/fred.clasic.css?6";
	protected $Login = "views/login.htm";
	
	protected $Modal;
	protected $Ignore = array();
	
	protected function finalize()
	{
		$_SESSION["rframeDataFlag"] = false;
	}

	public function __construct()
	{
		session_start();
		$this->ignore("/login");
		
		Program::$View = "views/main.htm";
		$this->Modal = new Modal();
		Program::$Panel = new FrmPanel("Opciones de filtrado");
		Program::$Panel->Name = "PanelOptions";
		
		App::loadUser();
		App::backPage();
		parent::__construct();
		$this->startComponents();

		$this->authorize("program_wellcome",true);
		$this->authorize("program_login",true);
		$this->authorize("program_logoff",true);
		$this->authorize("program_index",true);
		
		$this->script("/fred/assets/jquery.slim.min.js");
		$this->script("/fred/assets/fred.js?2");
		$this->script("/fred/assets/bootstrap.min.js");

		$this->style("/fred/assets/awesome/all.min.css");
		$this->style($this->Look);
		
		App::loadCrud(App::$UserActive->Modulos);
		$this->_login();
		$this->_loadMenu();
	}
	
	public function ignore($ruta)
	{
		$this->Ignore[] = $ruta;
	}
	
	public function db(MotorDbi $db,$auto = false)
	{
		if($auto==true)
		{
			$db->Server = App::$Setting->Server;
			$db->User = App::$Setting->User;
			$db->Password = App::$Setting->Password;
			$db->Database = App::$Setting->Database;
			if(!empty(App::$UserActive->Db)){
				$db->Database = App::$UserActive->Db;
			}
		}
		App::$Database = $db;
		$this->Db = App::$Database;

		if($db->test()==false){ 
			Modal::msg("Ocurrio un error en la conexion con la base de datos");
		}
	}
	
	//abstract protected function finishComponents();
	public function run($route)
	{
		//$datos = $this->getVar("Data");

		$datos = (empty($route[0]))? array():$route[0];
		if(!isset($datos["confirm"])) { $datos["confirm"] = false;}
		if(!isset($datos["crud"])) { $datos["crud"] = true;}
		if(!isset($datos["filter"])) { $datos["filter"] = true;}
		
        $nme = "program";
        $app = $this;
        $met = "wellcome";
        $mod = false;
        $tot = count($route);
        for($i=1; $i<$tot; $i++){
			$item = $route[$i];
			$met = $item;
			if(is_numeric($item)){
				$datos[$nme] = $item;
				$met = (isset($route[$i+1]))? $route[$i+1]:"view";
				if($app instanceof Controller){
					$app->Db = App::$Database;
					$app->setPrimaryKey($item);
					if($met!="update"){
						$datos["current"] = $i;
						$datos["amount"] = $tot-1;
						$this->Body[] = $app->view($datos);
					}
					$met = false;
				}
			}else{
				if(file_exists(App::$Setting->Path."/".$item)){
					$mod = $item;
					$met = false;
				}else{
					if($mod!=false){
						$ctr = App::$Setting->Path."/".$mod."/".$item.".php";
						if(file_exists($ctr)){
							include_once $ctr;
							$met = "main";
							$nme = $item;
							$this->Body[] = $app;
						}else{
							$met = $item;
						}
					}
				}
			}
		}
		
		
		//corre los metodos del controlador
		if($met!=false){
			$this->ejecutar($app, $nme, $met,$datos);
		}else{
			$met = $mod;
			$this->ejecutar($this, $nme, $met,$datos);
		}
			
		Program::$Panel->crud(Controller::$Cruds,Controller::$Keys);
		
		//$this->finalize();
		//unset($_SESSION["GET"]["confirm"]);
		
	}
	
	private function ejecutar($app, $nme, $met, $datos)
	{
		$metodos = get_class_methods(get_class($app));

		if(in_array($met,$metodos)){
			if($this->authorize($nme."_".$met)){
				$this->Body[] = $app->$met($datos);
			}else{
				$this->Body[] = $this->deny($datos);
			}
		}else if($met!=""){
			$datos["Metodo"] = "$nme.$met";
			$this->Body[] = $this->error($datos);
		}
	}
	
	
	public function __toString()
	{
		$body = (string) implode("",$this->Body);
		
		$this->export($body);
		
		if(Program::$View instanceof View){
			$view = Program::$View;
		}else{
			$view = new View(Program::$View);
		}
		$view->setVar((array) App::$Setting);
		$view->setVar("Scripts",App::$script);
		$view->setVar("Styles",App::$styles);
		$view->setVar("Jsvars",App::$jsvars);
		$view->setVar("Menu", $this->Menu);
		$view->setVar("User", $this->User->headView());
		$view->setVar("Body", $body);
		$view->setVar("Logo", $this->Logo);
		$view->setVar("Icon", $this->Icon);
		$view->setVar("Window", $this->Modal);
		$view->setVar("Panel", Program::$Panel);
		$text = (string) $view;
		
		return $text;
	}
	
	protected function export($body)
	{
		//crer el nombre del archivo a exportar
		$host = App::$Setting->Host;
		$user = (empty($this->User->Login))? "anonimo": $this->User->Login;
		$name = uniqid() . ".html";
		$path = "/$host/doc/$user";
		$ruta = App::$Setting->Web . $path;
		if (!file_exists($ruta)) {
			mkdir($ruta, 0777, true); // Crea la carpeta de destino con permisos adecuados
		}
		$this->_clearfiles($ruta);	

		//buscar la existencia de una plantilla
		$data = (!empty($this->Db->Database))? $this->Db->Database: "fred";
		$vnam = App::$Setting->Data . "/$data/FORMATOS/Header.html";
		
		if(file_exists($vnam)){
			$view = new View($vnam);
			$view->setVar("Body", $body);
			
			if(strpos($body,"header")===false){
				$body = (string) $view . $body;
			}
		}
		
		//genera salida
		$salida = "<html>";
		$salida.= "<head><link rel='stylesheet' type='text/css' href='/fred/assets/fred.print.css?1'/></head>";
		$salida.= "<body><table><thead><tr><td>" . $body;
		$salida = str_replace("</header>","</header></td></tr></thead><tbody><tr><td>",$salida);
		$salida.= "</td></tr></tbody><tfoot><tr><td></td></tr></tfoot></table></body></html>";
		file_put_contents($ruta."/".$name, $salida);
		
		//crea icono para imprimer y lo agrega al panel
		$url = "$path/$name";
		$b1 = new Button("Imprimir",8);
		$b1->Icon = "print";
		$b1->event("click","modal_print('$url')");
		Program::$Panel->Btnprint = $b1;
			
	}

	private function _clearfiles($folder)
	{
		$archivos = array_diff(scandir($folder), array('.', '..'));

		foreach ($archivos as $archivo) {
			$rutaArchivo = $folder ."/". $archivo;
			if (is_file($rutaArchivo)) {
				$ultimoAcceso = filemtime($rutaArchivo);
				$tiempoActual = time();
				$tiempoExpiracion = 60; // un minuto
				
				if (($tiempoActual - $ultimoAcceso) > $tiempoExpiracion) {
					unlink($rutaArchivo); 
				}
			}
		}
	}

	#carga los modulos de la barra de menu
	private function _loadMenu()
	{
		$n = new Nav(App::$Crud->Menu,$this->User);
		//$n->load();
		$this->Menu = $n;
	}
	

	private function _login()
	{	
		if(!$this->User->Setting->Exists){
			$ruta = $_SERVER["REQUEST_URI"];
			$rutas = $this->Ignore;
			if( !in_array($ruta, $rutas)){
				header("location:/login");
				//header("location:/spaniel/rastreo.php?login");
			}
		}else{
			if($this->User->Estado == "SUSPENDIDO"){
				if($_SERVER["REQUEST_URI"]!= "/suspend"){
					header("location:/suspend");
				}				
			}
		}
	}

	public function login()
	{
		$view = new View($this->Login);
		$user = new User();
		if(!empty($_POST["login"])){
			$user->load($_POST["login"]);
			if($user->Setting->Exists){
				if($user->password($_POST["password"])){
					$this->setVar("UserActive",$_POST["login"]);
					header("location:/wellcome");
				}else{
					Modal::msg("Contraseña incorrecta");
				}
			}else{
				Modal::msg("Usuario no encontrado");
			}
			
		}
		$view->setVar("login",$user->Login);
		$view->setVar("password","");
		Program::$View = $view;
		//return $view;
	}
	
	public function logoff($data)
	{
		$keys = array_keys($_SESSION);
		foreach($keys as $key){
			unset ($_SESSION[$key]);
		}
		header("location:/wellcome");
	}

	public function wellcome($data)
	{
		return new View("views/wellcome.htm");
	}	
	
	public function deny($data)
	{
		return new View("views/deny.htm");
	}
	
	public function main($data)
	{
		return new View("views/main.htm");
	}

	public function suspended($data)
	{
		return new View("views/suspended.htm");
	}

	public function error($data)
	{
		$view = new View("views/error.htm");
		$view->setVar($data);
		return $view;
	}
	
	

}
