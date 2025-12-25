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
include_once "View.php";
include_once "Form.php";
include_once "Collection.php";

abstract class Controller extends App
{		
	public static $Keys = array();
	public static $Father = false;
	public static $Cruds = false;
	
	protected $CrudList = false;
	protected $CrudView = false;
	
	public $Title;
	protected Form $Form;
	protected Model $Model;
	//protected $ModelCrud;
	protected $ListTitle = "";
	protected $ListField = "";
	protected $ListUrl = false;
	protected $ListView = false;

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
		$name.= "_printer";
		$this->authorize($name,true); 
		$this->startComponents();
	}

	public function setModel(Model $model)
	{
		$this->Model = $model;
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

	public function crud($cruds)
	{
		Controller::$Cruds = $cruds;
	}

	public function link($name, $link, $icon=false,$title = false)
	{
		App::$Crud->link($name, $link, $icon, $title);
	}

	public function linkFather($name, $father)
	{
		App::$Crud->linkFather($name, $father);
	}
		
	protected function loadFormFilter()
	{
		if(count($this->Controls) > 0){
			$i=0;
			foreach($this->Controls as $f){
				$nam = "b" . $i;
				Program::$Panel->$nam = $f;
				Program::$Panel->add($f,20);
				$i++;
			}
			$b1 = new Button("Filtrar",8);
			$b1->command("AplicarFiltro","filter");
			Program::$Panel->add($b1,10);
			$b2 = new Button("Borrar",8);
			$b2->command("BorrarFiltros","eraser");
			Program::$Panel->add($b2,10);
			Program::$Panel->run();
		}
	}
	
	protected function applyFilters($model)
	{
		return Program::$Panel->filters($model);
	}
	
	protected function collection($model=false)
	{
		$model = ($model==false)? $this->Model: $model;	
		$model = $this->applyFilters($model);
		
		$lista = new Collection($model);
		$lista->Url = $this->ListUrl;
		$lista->fields($this->ListField);
		$lista->titles($this->ListTitle);
		$lista->view($this->ListView);
		$lista->Db = $this->Db;
		
		return $lista;
	}
	
	public function main($data)
	{
		return $this->seek($data);
	}
		
	public function seek($data)
	{
		if($data["filter"]==true){
			$this->loadFormFilter();
		}
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
	
	public function view($data)
	{
		//regresa a la URL anterio 
		if($this->Model->view()==false && $data["amount"]==$data["current"]){
			header("location:".$this->getVar("UriBack"));
		}
		//cargar el crud para la vista
		if($this->CrudView != false){
			Controller::$Cruds = $this->CrudView;
		}
		
		//asigna claves primarias al objeto
		$keys = array_keys(Controller::$Keys);
		foreach($keys as $key){
			$this->Model->value($key,Controller::$Keys[$key]);
		}
		
		//abre el modelo desde la base de datos, si no existe se sale al main
		$this->Db->open($this->Model);
		
		//regresa a la viata anterior si no existe el objeto
		if($this->Model->setting()->Exists==false){
			header("location:".$this->getVar("UriBack"));
		}
		
		//promociona claves primarias adicionales que tiene el objeto
		if($this->PrimaryKeys != false){
			$keys = explode(",",$this->PrimaryKeys);
			foreach($keys as $k){
				if(!empty($this->Model->$k)){
					Controller::$Keys[$k] = $this->Model->$k;
				}
			}
		}
		$ref = $this->Model->getReference();
		if($ref!=false) { Controller::$Keys["Referencia"] = $ref;}
		
		#######no se que hace JAJAJAJAJA 
		//(creo que es para combinar con el ID para los documentos)
		/*
		if($this->Reference!==false){
			$k = $this->Model->setting()->Key;
			$val =  $this->Reference . "-" . $this->Model->$k;
			Controller::$Filters[] = new ModelFilter("Referencia",$val); //Filters fue eliminado
		}*/
		
		//retorna la vista del modelo
		Controller::$Father = $this->Model;
		//if($this->Model->view()!=false){
			return $this->Model;
		//}else{
			//return "";
		//}
		
	}
	
	public function create($data)
	{
		$this->setKeysToModel();
		
		$f = $this->Form;
		$f->Keys = Controller::$Keys;
		$f->Model = $this->Model;
		$f->Db = $this->Db;
		$f->Create = true;
		$f->Title = "Agregar " . $f->Title;
		$f->Father = Controller::$Father;
		$f->run();
		return $f;
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
	
	public function update($data)
	{
		$this->setKeysToModel();

		$f = $this->Form;
		$f->Title = "Modificar " . $f->Title;
		$f->Keys = Controller::$Keys;
		$f->Model = $this->Model;
		$f->Db = $this->Db;
		$f->Father = Controller::$Father;
		$f->run();
		return $f;
	}
	
	public function printer($data)
	{
		$key = $this->Model->setting()->Key;
		$name = get_class($this->Model) . "_";
		$name = str_replace("\\","",$name);
		$name.= $this->Model->$key;
		$host = App::$Setting->Host;
		$file = "d:/xampp/htdocs/$host/$name.html";
		file_put_contents($file, (string) $this->Model);
		$src = "/$host/$name.html";
		$str = "<div>";
		$str.= "<button type='button' id='btnImprimir' class='btn'>";
		$str.= "<i class='fa fa-print'></i> Imprimir</button>";
		$str.= "</div>";
		$str.= "<iframe src='$src' class='data-sheet-print' id='FredFrame'></iframe>";
		return $str;
	}

	public function openModel()
	{
		$this->setKeysToModel();
		$this->Db->open($this->Model);
	}


}

