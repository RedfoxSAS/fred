<?php
/**
 * Controller.php version 1
 * 
 * Define las funcionalidades para los controladores
 * 
 * @Autor:	Raul E. Ramos Guzman
 * @Fecha:	05/05/2016
 * @Modificado: 30/01/2023
 * 
 * */

namespace Fred;

include_once "App.php";
include_once "MotorMySql.php";
include_once "Model.php";

abstract class WebService extends App
{		
	public static $Keys = array();
	public static $Father = false;
    public static $Data = false;
	
	public $Title;

	protected Model $Model;

	protected $PrimaryKeys = false;
	protected $ForeignKeys = array();
	protected $Controls = array();
	
		
	public function __construct()
	{		
        parent::__construct();
		$this->Db = App::$Database;
		$name = get_class($this);
		if(strpos($name,"\\")>0){
			list($space,$name) = explode("\\",$name);
		}
		Program::$Type = "json";
		$this->startComponents();
        
        
        if(WebService::$Data==false){
            // Configuración de encabezados para permitir JSON
            header('Content-Type: application/json');

            // Permitir solicitudes CORS (útil para desarrollo con JS en otro origen)
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');

            $method = $_SERVER['REQUEST_METHOD'];

            // Manejo de solicitudes
            if ($method === 'POST') {
                // Leer el cuerpo de la solicitud
                $inputJSON = file_get_contents('php://input');
                WebService::$Data = json_decode($inputJSON, true); // Decodificar JSON a array asociativo
            
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Validar datos de entrada

                } else {
                    Program::$Json->code = 400;
                    Program::$Json->message = "JSON inválido.";
                    Program::$Json->success = false;
                }
            } else {
                // Responder a métodos no soportados
                Program::$Json->code = 405;
                Program::$Json->message = "Método no permitido. Usa POST.";
                Program::$Json->success = false;
            }

        }

        //identificar el usuario
        if(!empty(WebService::$Data["username"])){
            $this->User = new User();
            $this->User->load(WebService::$Data["username"]);
            if(!empty($this->User->Db)){
                $this->Db->Database =  $this->User->Db;
            }
        }
    }
    
    public function getData()
    {
		return WebService::$Data;
	}

	
	public function setPrimaryKey($val)
	{
		$key = $this->Model->setting()->Key;
		Controller::$Keys[$key] = $val;
	}
	
	public function getPrimaryKey($key)
	{
		if(!empty(Controller::$Keys[$key])){
			return Controller::$Keys[$key];
		}
		return "";
	}
	
	public function applyKeys($model,$fkeys = false)
	{
		if($fkeys!=false){
			$keys = array_keys($fkeys);
			foreach($keys as $k){
				if(!empty(Controller::$Keys[$fkeys[$k]])){
					$fil = new ModelFilter($k,Controller::$Keys[$fkeys[$k]]);
					$model->filter($fil);
				}
			}
		}
		return $model;
	}
	
	public function ctrl(Control $c)
	{
		$this->Controls[] = $c;
	}

	
	public function main($data)
	{
		return $this->seek($data);
	}
		
	public function seek($data)
	{
		if($data["crud"]==true){
			Controller::$Cruds = $this->CrudList;
		}
		
		$this->Model = $this->applyKeys($this->Model,$this->ForeignKeys);
		$lista = $this->collection();
		//$title = $this->Title;
		//$str = ;
		//$str.= "<section>$lista</section>";

		return $lista;
	}
	
	
	private function setKeysToModel()
	{
		//asigna claves foraneas al objeto
		$fkeys = $this->ForeignKeys;
		if($fkeys!=false){
			$keys = array_keys($fkeys);
			foreach($keys as $k){
				if(!empty(Controller::$Keys[$fkeys[$k]])){
					$val = Controller::$Keys[$fkeys[$k]];
					$this->Model->value($k,$val);
				}
			}
		}

		//asigna claves primarias al objeto
		$keys = array_keys(Controller::$Keys);
		foreach($keys as $key){
			if(isset($this->Model->$key)){
				$this->Model->value($key,Controller::$Keys[$key]);
			}
		}		
	}

	
}

