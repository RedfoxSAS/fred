<?php
/**
 * Form.php
 * 
 * @Autor		Raul Ramos Guzman
 * @Fecha		1/01/2023
 * @Copyright 	Redfox 2023
 * 
 * Formulario html para la captura de datos
 * 
 * 
*/

namespace Fred;

include_once "App.php";
include_once "View.php";

include_once "controls/Button.php";
include_once "controls/Panel.php";
include_once "controls/Label.php";
include_once "controls/Textbox.php";
include_once "controls/Mailbox.php";
include_once "controls/Numbox.php";
include_once "controls/Datebox.php";
include_once "controls/Listbox.php";
include_once "controls/Imagebox.php";
include_once "controls/Table.php";
include_once "controls/Checklist.php";
include_once "controls/Filebox.php";

include_once "Auditoria.php";
/*
include_once "controls/rCheckList.php";

include_once "controls/rFileBox.php";
include_once "controls/rFrameBox.php";
include_once "controls/rIndexBox.php";

*/

abstract class Form extends App
{
	//Variables del objeto
	protected static $Number = 0;
	
	protected $View = false;
	protected $Body;
	protected $UrlSave = false;
	protected $UrlDel = false;
	protected $Script = "";
	protected $Style = "";
	
	public $Buttons = array();
	//protected $Buttint = array();
	
	public $Name = ""; 
	public $Title = ""; 
	public Model $Model;
	public $Create = false;
	public $Keys = array();
	public $Btnprint = "";
	public $Father = false; //asignado por el modulo para editar cuando hay un padre;
	
	public $Status = 0;
	# funciones del formulario ==============================
   
    //constructor del formulario
	public function __construct($title=false)
	{
		parent::__construct();
		Form::$Number++;
		if($title != false){
			$this->Name = "Form" . str_replace(" ","",$title);
			$this->Title = $title;
		}else{
			$this->Name = "Form" . Form::$Number;
		}
		if(!empty(App::$Database)){
			$this->Db = App::$Database;
		}
		$this->Body = new Panel();
		$this->startComponents();
	}
	
	public function body()
	{
		return $this->Body;
	}

	public function clear()
	{
		$this->Body->clear();
		$this->Buttons = array();
	}
	
	//adiciona controles al cuerpo del formulario
	public function add(Control $control, $w = false)
	{
		if($w != false && is_numeric($w)){
			$control->Width = $w;
		}
		$this->Body->add($control);
	}
	
	//adicionar un boton al panel de botones
	public function button(Control $control)
	{
		$pos = count($this->Buttons) + 3;
		$this->Buttons[$pos] = $control;
	}
	
	//activar boton close / back
	public function ActiveButtonSave($url=false)
	{
		$this->UrlSave = $url;
		$this->btnSave = new Button("Guardar",8);
		$this->btnSave->command("save","save");
		$this->Buttons[1] = $this->btnSave;
	}
	
	//activar boton close / back
	public function ActiveButtonClose($url=false)
	{
		if($url!=false) {$url = "'$url'";}
		$this->btnClose = new Button("Cerrar",8);
		$this->btnClose->event("click","back($url)");
		$this->btnClose->Icon = "angle-left";
		$this->Buttons[0] = $this->btnClose;
	}
	
	//activar boton eliminar
	public function ActiveButtonDelete()
	{
		$nam = $this->Name;
		$tit = "Eliminar Registro";
		$msg = "Un registro eliminado no se puede recuperar, ";
		$msg.= "es posible que se eliminen registros de otras listas relacionadas.";
		$msg.= "<br>Desea continuar con el proceso?";
		$this->btnDel = new Button("Eliminar",8);
		$this->btnDel->Name = "delete";
		$id = $this->btnDel->Id;		
		$this->btnDel->event("click","modal_confirm('$tit','$msg','CmdYes$id();$nam.submit()','CmdNo$id()')");
		$this->btnDel->Icon = "times";
		$this->Buttons[2] = $this->btnDel;
	}
	
	public function crud($crud,$vars=false)
	{
		$cruds = explode(",",$crud);
		foreach($cruds as $name){
			if($this->authorize($name)){
				$c = App::$Crud->get($name);
				if($vars!=false && $c!=false){
					$c0 = $c[1];
					$c0 = str_replace("'","\'",$c0);
					$c0 = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $c0);
					reset($vars);
					while( list($key, $val) = each($vars)){
						$$key = $val;
					}
					@eval ("\$c0 = '$c0';");
					while( list($key, $val) = each($vars)){
						unset($$key);
					}
					$c0 = str_replace("\'","'",$c0);
					$c[1] = $c0;				
				}
				if($c != false){
					@$b = new Button($c[0],8);
					@$b->link($c[1],$c[2],"_self");
					$this->Buttons[$name] = $b;
				}
			}
		}
	}
		
	//imprime el formulario en forma de texto
	public function __toString()
	{	
		/*
		$ctrid = "";
		if(isset($this->Model)){
			$nam = $this->Model->setting()->Key;
			$val = $this->Model->$nam;
			$ctrid = "<input type='hidden' name='$nam' value='$val'>";
		}
		*/
		 
		if($this->View!=false){
			$w = $this->View;
			if(!($w instanceof View)){
				$w = new View($w);
			}
			//$data = $this->data(true);
			$data = $this->getData(true);
			$w->setVar($data);
			//$w.= $ctrid;
			return (string) $w;
		}else{
			$name = $this->Name;
			$title =  $this->Title;
			$body = $this->Body;
			$buttons = implode(" ",$this->Buttons);
			$print = $this->Btnprint;
			$srp = $this->Script;
			$stl = $this->Style;
			$str = "
			<style>$stl</style>
			<script language='javascript'>$srp</script>
			<h1>$title</h1>	
			<form name='$name' id='$name' method='POST' enctype='multipart/form-data' style='width:100%;'>
				<input type='hidden' name='fred_form_name' value='$name'>
				<section>
					$body
				</section>
				<section class='form-buttons' id='Buttons$name'>
					$buttons
				</section>
				$print
			</form>
			
			";
			return $str;
		}
	}	
	
	//pasar datos a los controles
	protected function fillControls()
	{
		if(!empty($_POST) && $_POST["fred_form_name"] == $this->Name){
			$this->fillControlsForm();
		}else if(!empty($this->Model)){
			$this->fillControlsModel();
		}else{
			$this->fillControlsStore();
		}
	}
	
	protected function fillControlsForm()
	{
		$controls = $this->expose();
		foreach($controls as $control){
			if(($control instanceof Control) && !($control instanceof Panel)){
				if(isset($_POST[$control->Name])){
					if(!is_null($_POST[$control->Name])){
						$control->text($_POST[$control->Name]);
					}
				}else{
					$field = $control->Source;
					if(isset($this->Model->$field)){
						//$control->text($this->Model->value($field));
					}
				}
				if($control instanceof Label){
					$field = $control->Source;
					if(!empty($this->Model->$field)){
						$control->text($this->Model->value($field));
					}
				}
			}
		}		
	}
	
	protected function fillControlsModel()
	{
		$controls = $this->expose();
		foreach($controls as $control){
			if($control instanceof Control){
				if($control->Source != false){
					$field = $control->Source;
					if(!empty($this->Model->$field)){
						$control->text($this->Model->value($field));
					}else if(!is_null($control->TextDefault)){
						$control->Text = $control->TextDefault;
					}
				}
			}
		}		
	}
	
	protected function fillControlsStore()
	{
		$controls = $this->expose();
		foreach($controls as $control){
			if($control instanceof Control){
				$idx = "Fred_" . $this->Name . "_" . $control->Name;
				if(!empty($_SESSION[$idx] )){
					$control->text($_SESSION[$idx]);
				}
			}
		}		
	}

	//llena el modelo con los datos del formulario
	public function fillModel()
    {
		$controls = $this->expose();
		foreach($controls as $control){
			if($control instanceof Control){
				if($control->Source != false && $control->Active ==true && isset($_POST[$control->Name]) ){
					$field = $control->Source;
					if(isset($this->Model->$field)){
						$this->Model->value($field,$control->text());
					}
				}
			}
		}
	} 
	
	//corre el fomulario
	public function run()
	{
		if(!empty($this->Model)){
			if($this->Create == false){
				$this->Db->open($this->Model);
			}
		}
		
		$this->fillControls();
		
		if(!empty($_POST) && $_POST["fred_form_name"] == $this->Name){
			$metodos = get_class_methods(get_class($this));
			$claves = array_keys($_POST);
			$llamadas = array_intersect($metodos,$claves);
		
			if(count($llamadas)>0){
				foreach($llamadas as $metodo){
					if($this->authorize($metodo)){
						$this->$metodo();
					}else{
						Modal::msg("Accion no autorizada",3);
					}
				}
			}
		}
		$this->finish();
	}

	public function finish()
	{
		if(!empty($this->Model)){
			if($this->Model->setting()->Exists && $this->Create==true){
				$key = $this->Model->setting()->Key;
				$val = $this->Model->$key;
				$url = ($this->UrlSave!=false)? $this->UrlSave : "$val/update";
				header("location:$url");
				exit;
			}
		}	
	}

	
	public function save()
	{
		$this->fillModel();
		$r = $this->Db->save($this->Model);
		//echo $this->Db->Sql;
		if($r!=false){
			$this->Status = 1;
			//$this->saveFiles();
			//$this->setDataSource($this->dataSource);
		}
		return $r;		
	}
	
	public function delete()
	{
		if($this->Model->setting()->Exists){
			$this->Db->delete($this->Model);
			$this->Status = 2;
			$url = ($this->UrlDel!=false)? $this->UrlDel : $this->getVar("UriBack");
			$url = preg_replace('/\/\d+$/', '', $url);
			header("location:$url");
			exit;
		}
	}
	

	public function store()
	{
		if(!empty($_POST)){
			$controls = $this->expose();
			foreach($controls as $control){
				if($control instanceof Control){
					$text = $control->text();
					$idx = "Fred_" . $this->Name . "_" . $control->Name;
					$_SESSION[$idx] = $text;
				}
			}
		}
	}
	
	public function audit($action,$text,$ref=false)
	{
		$ref = (!$ref)? $this->Model->getReference() : $ref;
		$audi = new Auditoria();
		$audi->Accion = $action;
		$audi->Comentario = $text;
		$audi->Referencia = $ref;
		$this->Db->save($audi,false);
	}	
	
	// --- revisado hasta aaqui  ==============================================00
	

	//pendiente revisar esta funcion de subida de archivos
	private function saveFiles()
	{
		if(count($this->_fileControls) > 0){
			$folios = 0;
			$foliar = false;
			foreach($this->_fileControls as $control){
				if(!empty($_FILES[$control->name])){
					if($control->sourcename){
						$source = $control->sourcename;
						$name = $this->dataSource->data->$source;
					}
					$foliar = $control->saveFile($name);
					$source = $control->source;
					if($control->source){
						$source = $control->source;
						$this->dataSource->data->$source = $control->text();
					}
				}
				if($foliar) {
					$f = $this->getFolios($control->text);
					$control->folios = $f;
					$folios+= $f; 
				}		
			}
			if($foliar) {
				$this->dataSource->data->Folios = $folios;
			}
			$this->dataSource->exists = true;
			$this->Db->save($this->dataSource);
			//echo $this->db->sql;
		}	
	}

	
	/*
	public function openForm()
	{
		$datos = $_SESSION["rframeForm" . $this->name];
		if(is_array($datos)){
			$controles = $this->expose();
			foreach($controles as $control){
				if($control instanceof rControl){
					if(array_key_exists($control->name,$datos)){
						$control->text = $datos[$control->name];
					}
				}
			}
		}
	}
	*/
	public function deny($data)
	{
		$this->message("Acceso dendegado");	
	}
	

	

}
