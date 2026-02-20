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
	public static $Type = "html";
	public static $Json;
	
	private $Body = array();
	private $Menu ;
	protected $Route = "";

	public $Titles = array();
	protected $Logo = "/fred/assets/images/logo.png";
	protected $Icon = "/fred/assets/images/favicon.ico";
	protected $Look = "/fred/assets/fred.clasic.css?22";
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
		$this->ignore("/welcome");
		Program::$Json = new \stdClass();
		Program::$Json->success = true;
		Program::$Json->message = false;
		Program::$Json->code = 200;

		Program::$View = "views/main.htm";
		$this->Modal = new Modal();
		Program::$Panel = new FrmPanel("Opciones de filtrado");
		Program::$Panel->Name = "PanelOptions";
		
		App::loadUser();
		App::backPage();
		parent::__construct();
		$this->startComponents();

		$this->authorize("program_welcome",true);
		$this->authorize("program_login",true);
		$this->authorize("program_logoff",true);
		$this->authorize("program_suspended",true);
		
		$this->script("/fred/assets/jquery.slim.min.js");
		$this->script("/fred/assets/fred.js?3");
		$this->script("/fred/assets/bootstrap.min.js");

		$this->style("/fred/assets/awesome/all.min.css?3");
		
		
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
			$s = App::$Setting->Server;
			$u = App::$Setting->User;
			$p = App::$Setting->Password;
			$d = App::$Setting->Database;
			$db->setCredentials($d,$s,$u,$p);
			if(!empty(App::$UserActive->Db)){
				$db->setDatabase(App::$UserActive->Db);
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
		$links = array();
		$datos = (empty($route[0]))? array():$route[0];
		if(!isset($datos["confirm"])) { $datos["confirm"] = false;}
		if(!isset($datos["crud"])) { $datos["crud"] = true;}
		if(!isset($datos["filter"])) { $datos["filter"] = true;}
		
        $nme = "program";
        $app = $this;
        $met = "dashboard";
        $mod = false;
        $tot = count($route);
		$href="";
        for($i=1; $i<$tot; $i++){
			$item = $route[$i];
			$href.="/".$item;
			$title = $item;
			
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
							if(!empty($app->Title)) { 
								$this->Titles[] = $app->Title;
								$title = $app->Title;
							}
						}else{
							$met = $item;
						}
					}
				}
			}
			$links[] = "<a href='$href'> " . ucfirst($title) . " </a>";
		}
		unset($links[0]);
		$this->Route=implode("/",$links);
		//corre los metodos del controlador
		if($met!=false){
			$this->ejecutar($app, $nme, $met,$datos);
		}else if($mod!=false){
			$met = $mod;
			//$this->ejecutar($this, $nme, $met,$datos);
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
		$this->style($this->Look);
		if(Program::$Type=="html"){
			$body = (string) implode("",$this->Body);
			$body = View::clean($body);
			//$body = "<section class='AppTitle'><h1>".implode(" / ",$this->Titles)."</h1></section>" . $body;
			$body = "<div class='app-title'>".$this->Route."</div>" . $body;
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
			$view->setVar("Route", $this->Route);
			$view->setVar("Body", $body);
			$view->setVar("Logo", $this->Logo);
			$view->setVar("Icon", $this->Icon);
			$view->setVar("Window", $this->Modal);
			$view->setVar("Panel", Program::$Panel);
			$text = (string) $view;
			
			return $text;

		}else if(Program::$Type=="json"){
			// Aquí agregamos los encabezados CORS antes de devolver JSON
			header("Access-Control-Allow-Origin: *");  // Puedes poner el dominio específico en lugar de '*'
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
			header("Access-Control-Allow-Headers: Content-Type, Authorization");
			header("Content-Type: application/json");

			// Responder a la petición OPTIONS para preflight CORS
			if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
				http_response_code(200);
				exit();
			}

			$body = (count($this->Body) > 1) ? $this->Body : reset($this->Body);
			$return = Program::$Json;
			$return->data = $body;
			if(Program::$Json->code!=200){
				http_response_code(Program::$Json->code);
			}
			return json_encode($return);
		}
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
		$data = (!empty($this->Db->getDatabase()))? $this->Db->getDatabase(): "fred";
		
		//generar el html
		$salida = Program::bodyToHtml($body,$data);

		//genera salida
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
		if(!$this->User->setting()->Exists){
			$ruta = $_SERVER["REQUEST_URI"];
			
			// parse_url para analizar la URL
			$parsedUrl = parse_url($ruta);
			// La parte de la ruta sin el query string
			$rutaLimpia = $parsedUrl['path'];

			if( !in_array($rutaLimpia, $this->Ignore)){
				header("location:/welcome");
			}
		}else{
			if($this->User->Estado == "SUSPENDIDO"){
				if($_SERVER["REQUEST_URI"]!= "/suspended"){
					header("location:/suspended");
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
			if($user->setting()->Exists){
				if($user->password($_POST["password"])){
					$this->setVar("UserActive",$_POST["login"]);
					header("location:/dashboard");
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
		header("location:/welcome");
	}

	public function welcome($data)
	{
		return new View("views/welcome.htm");
	}
	
	public function dashboard($data)
	{
		return new View("views/dashboard.htm");
	}
	
	public function deny($data)
	{
		if(Program::$Type=="json"){
			Program::$Json->success = false;
			Program::$Json->message = "Acceso denegado";
			return "Accceso denegado";
		}else{
			return new View("views/deny.htm");
		}
	}
	
	public function main($data)
	{
		return new View("views/main.htm");
	}

	public function suspended($data)
	{
		return new View("views/suspend.htm");
	}

	public function error($data)
	{
		if(Program::$Type=="json"){
			Program::$Json->success = false;
			Program::$Json->message = "Recurso no existe";
			return "Recurso solicitado no existe";
		}else{
			$view = new View("views/error.htm");
			$view->setVar($data);
			return $view;
		}
	}

	public function help($data)
	{
		return "Ayuda del sistema";
	}
	
	static public function bodyToHtml($body, $dbName)
	{
		$vnam = App::$Setting->Data . "/$dbName/FORMATOS/FPMG.htm";
		$salida = $body;
		if(file_exists($vnam)){
			$view = new View($vnam);
			$view->setVar("Body", $body);
			$salida = (string) $view;
			if(strpos($salida,"<head>")!==false){
				$salida = str_replace("<head>","<head><link rel=\"stylesheet\" type=\"text/css\" href=\"/fred/assets/fred.print.css?7\"/>",$salida);
			}
			
		}else{
			/*
			$salida = "<html><head><meta charset='UTF-8'>";
			$salida.= "<link rel='stylesheet' type='text/css' href='/fred/assets/fred.print.css?4'/></head>";
			$salida.= "<body><div class='document'><table>";
			$salida.= "<thead><tr><td><header></header></td></tr></thead>";
			$salida.= "<tbody><tr><td>$body</td></tr></tbody>";
			$salida.= "<tfoot><tr><td><footer></footer></td></tr></tfoot>";
			$salida.= "</table></div></body></html>";
			*/
			$salida = "
			<html>
			<head>
			<meta charset=\"UTF-8\">
			<link rel=\"stylesheet\" type=\"text/css\" href=\"/fred/assets/fred.print.css?4\"/>
			</head>
			<body>
			<div class=\"page\">
				<div class=\"background\">
					<img src=\"fondo.jpg\">
				</div>
				<div class=\"document\">
					$body
				</div>
			</div>
			</body></html>
			";
		}
		return $salida;
	}

}
