<?php 

/* Model.php
 * @Autor		Raul Ramos Guzman
 * @Fecha		1/01/2023
 * @Copyright 	Redfox 2023
 * 
 * Dota de funcionalidad para conectarse a una base de datos.
 * 
*/

namespace Fred;

include_once "ModelFile.php";
include_once "FileImage.php";

class ModelStatic
{
	public string $Id = "";
	public string $Title = "";
	
	public function __construct($id=false)
	{
		$items = $this->Items();
		if($id!=false){
			$this->Id = $id;
			$ids = explode(",", $id);
			$t = array();
			foreach($ids as $id){
				if(isset($items[$id])){
					$t[] = $items[$id];
				}else{
					$t[] = "Desconocido";
				}
			}
			$this->Title = implode(",",$t);
		}
	}
	
	public function __toString()
	{
		$str = str_replace(",",", ",$this->Title);
		return $str;
	}
		
	public function Items(){}
}

class ModelJson
{
	
	public function __construct($text="{}")
	{
		$this->setText($text);
	}
	
	public function setText($text)
	{
		if($text!=""){
			$json = json_decode($text);
			$campos = array_keys(get_object_vars($json));
			foreach($campos as $c){
				$this->$c = $json->$c;
			}
		}
	}
	
	public function __toString()
	{
		$txt = json_encode($this);
		return $txt;
	}
	
	public function dataview()
	{
		return $this->extract($this);
	}
	
	public function extract($obj)
	{
		$r = array();
		$campos = array_keys(get_object_vars($obj));
		foreach($campos as $c){
			if($obj->$c instanceof \stdClass){
				$r = array_merge($r, $this->extract($obj->$c));
			}else{
				$r[$c] = $obj->$c;
			}
		}
		return $r;
	}
	
	public function value($field)
	{
		if(!empty($this->$field)){
			return $this->$field;
		}else{
			return "";
		}
	}
}

class ModelSetting
{
	static public $Num = 0;	
	static public array $Template = array();
	public $Count = 0;
	public $Table;
	public $Key;
	public $Sql = false;
	public $Name= false;

	public $Filters = array();
	public $Order = array();
	public $Limit = false;
	public $Summary = false;
	public $Exists = false;
	public $Cruds = array();
	public $Method = false;
	public $Validate = array();
	//public $Fathers = array();
	//public $Properties = array(); //usado para agregar los campos que son tipos model
	public $Relations = false;
	public $Group = false;
	public $Columns = false;
	
	//public $ActiveDb = false;
	
	
	public function __toString(){ return $this->Table;}
}

class ModelFilter
{
	public $Key;
	public $Sig;
	public $Val;
	public $Val2 = false;
	public $Sig2 = false;
	
	public function __construct($field,$value,$sign="=")
	{
		$this->Key = $field;
		$this->Val = $value;
		if(is_numeric($sign) || strtotime($sign)>0){
			$this->Val2 = $sign;
			$this->Sig  = ">=";
			$this->Sig2 = "<=";
			
		}else{
			$this->Sig = $sign;
		}
	}
	
	public function __toString()
	{
		$val = $this->Val;
		$val = (is_numeric($val))? $val : "'$val'";
		$r = $this->Key . " " . $this->Sig . " " . $val;
		
		if($this->Val2 != false){
			$val2 = $this->Val2;
			$val2 = (is_numeric($val2))? $val2 : "'$val2'";
			$r.= " AND " . $this->Key . " " . $this->Sig2 . " " . $val2;
			$r = "($r)";
		}

		return $r;
	}
}

class Model
{
	public string $Active = ""; //Agregado para identificar que es el registro activo en un list
	public string $Estado = "";
	public string $FechaRegistro = "";
	public string $FechaModifica = "";
	public string $UserRegistro = "";
	public string $UserModifica = "";
	
	public ModelSetting $Setting;

	public function __construct()
	{
		$this->Setting = new ModelSetting();
		$this->Setting->Sql = "";
		
		$this->FechaRegistro = date("Y-m-d");
		$this->FechaModifica = date("Y-m-d");
		if(!empty(App::$UserActive)){
			$this->UserRegistro = App::$UserActive->Login;
			$this->UserModifica = App::$UserActive->Login;
		}
	}
	
	public function expose()
	{
		//$keys = get_class_vars(get_class($this));
		$keys = get_object_vars($this);
		unset($keys["Setting"]);
		return array_keys($keys);
	}

	#agrega las tablas donde se almacenan los datos.
	public function table($table,$key,$fkey=false)
	{
		$this->Setting->Table = $table;
		$this->Setting->Key = $key;
		$this->Setting->Count++;	
	}
	
	public function getReference()
	{
		$key = $this->Setting->Key;
		if($this->Setting->Name != false){
			return $this->Setting->Name . "-" . $this->$key;
		}else{
			return false;
			return $this->key;
		}
	}
	/*
	public function getRelations()
	{
		$relation = array();
		$fields = $this->expose();
		foreach($fields as $f){
			if($this->$f instanceof Model){
				$t1 = $this->Setting->Table;
				$t2 = $this->$f->Setting->Table;
				$k = $this->$f->Setting->Key;
				$rel = "LEFT JOIN $t2 ON $t1.$f = $t2.$k";
				$relation[] = $rel;
				$relation = array_merge($relation,$this->$f->getRelations());
			}
		}
		return $relation;	
	}
	*/
	

	
	#retorna los datos en forma de arreglo
	public function data($plano=false)
	{
		//$datos = get_object_vars($this);
		$keys = $this->expose();
		$datos = array();
		foreach($keys as $key){
			$dato = $this->$key;
			if($dato instanceof Model){
				if($plano==true){
					$datos = array_merge($datos, $dato->data($plano));
					$k = $dato->Setting->Key;
					$datos[$key] = $dato->$k;
				}else{
					$datos[$key] = $dato->data();
				}
			}else if($dato instanceof ModelStatic){
				$datos[$key] = (string) $dato->Id;
			}else if($dato instanceof ModelJson){
				$datos[$key] = $dato;
			}else if($dato instanceof ModelFile){
				$dato->setName($this,$key);
				$datos[$key] = (string) $dato->getText();
			}else{
				$datos[$key] = (string) $dato;
			}
		}
		
		return $datos;
	}
	
	public function dataview()
	{
		//$datos = get_object_vars($this);
		$keys = $this->expose();
		$datos = array();
		foreach($keys as $key){
			$dato = ($this->Setting->Summary==true)? "<b>".$this->$key."</b>":$this->$key;
			//$dato = $this->$key;
			if($dato instanceof Model){
				$datos = array_merge($datos, $dato->dataview());
				$datos[$key] = (string) $dato;
			}else if($dato instanceof ModelJson){
				$datos = array_merge($datos, $dato->dataview());
				$datos[$key] = (string) $dato;
				//$campos = array_keys(get_object_vars($dato));
				//foreach($campos as $c){
					//$datos[$c] = $dato->$c;
				//}
			}else if(is_array($dato)){
				$datos[$key] = implode("", $dato);
			}else if($dato instanceof FileImage){
				$datos[$key] = "<img src='" . (string) $dato . "' style='width:100%;'>";
			}else{
				$datos[$key] = (string) $dato;
				if(strpos($datos[$key],"\n")>0){
					$datos[$key] = str_replace("\n","<p>",$datos[$key]);
				}
			}
		}
		return $datos;		
	}
	/*
	#agrega un filtro personalizado
	public function filter2(ModelFilter $f,$idx=false)
	{
		$idx = ($idx===false)? count($this->Setting->Filters) + 1: $idx;
		$key = $f->Key;
		if(strpos($f->Key,".")===false){
			if(isset($this->$key)){
				$f->Key = $this->Setting->Table . "." . $f->Key;
				$this->Setting->Filters[$idx][] = $f; 
			}else{
				$keys = $this->expose();
				
				foreach($keys as $key){
					$dato = $this->$key ;
					
					if($dato instanceof Model){
							echo $key . "<br>";		
						if(isset($dato->$key)){
							$f->Key = $dato->Setting->Table . "." . $f->Key;
							$this->Setting->Filters[$idx][] = $f; 
						}
					}
				}
			}
		}else{
			$this->Setting->Filters[$idx][] = $f;
		} 
	}
	*/
	public function filter(ModelFilter $f,$idx=false,$model=false)
	{
		$model = ($model==false)? $this: $model;
		$idx = ($idx===false)? count($this->Setting->Filters) + 1: $idx;
		$key = $f->Key;
		if(strpos($f->Key,".")===false){
			if(isset($model->$key)){
				$f->Key = $model->Setting->Table . "." . $f->Key;
				$this->Setting->Filters[$idx][] = $f; 
			}else{
				$keys = $model->expose();
				foreach($keys as $key){
					$dato = $model->$key ;
					if($dato instanceof Model){
						$this->filter($f,$idx,$dato);
					}
				}
			}
		}else{
			$this->Setting->Filters[$idx][] = $f;
		} 
	}
	
	#Construye los filtros SQL desde las variables publicas del modelo
	public function filters()
	{
		$keys = get_object_vars($this);
		unset($keys["FechaRegistro"]);
		unset($keys["FechaModifica"]);
		unset($keys["UserRegistro"]);
		unset($keys["UserModifica"]);
		unset($keys["Setting"]);
		unset($keys["Active"]);
		
		$vars = array_keys($keys);
		foreach($vars as $var){
			if(isset($this->Setting->Filters[$var])){
				unset($this->Setting->Filters[$var]);
			}
			if($this->$var instanceof Model){
					$key = $this->$var->Setting->Key;
					$val = $this->$var->$key;
			}else if($this->$var instanceof ModelStatic){
					$val = $this->$var->Id;
			}else if($this->$var instanceof ModelFile){
			}else{
				$val = $this->$var;
				
			}
			if(!empty($val)){
				if(is_string($val)) {
					if(strpos($val,"%")===false){
						$this->filter(new ModelFilter($var,$val,"LIKE"),$var);
					}else{
						$this->filter(new ModelFilter($var,$val),$var);
					}
				}else if(is_numeric($val)){
					$this->filter(new ModelFilter($var,$val),$var);
				}
			}
		}
	
		if(count($this->Setting->Filters)>0){
			$where = array();
			foreach($this->Setting->Filters as $filters){
				$where[] = "(". implode(" OR ", $filters) .")";
			}
			if(count($where)>0){
				$str = " WHERE (" . implode(" AND ", $where) . ")";
				return $str;
			}
		}
		
		return "";
	}
	
	#setea los campos a mostrar en formato de tabla
	public function view($view=false)
	{
		if($view==false){
			if(empty(ModelSetting::$Template[get_class($this)])){
				return false;
			}else{
				return ModelSetting::$Template[get_class($this)];
			}
		}
		//if($view instanceof View){
			ModelSetting::$Template[get_class($this)] = $view; 
		//}else{
			//ModelSetting::$Template[get_class($this)] = $view; 
		//}
	}
	
	
	#convirte el objeto en estring
	public function __toString()
	{
		if(!empty(ModelSetting::$Template[get_class($this)])){
			$data = $this->dataview();
			$v = $this->view();
			$w = new View();
			if($v instanceof View){
				$w = $v;
			}else{
				$w->Text = $v;
			}
			$w->setVar($data);
			return (string) $w;
		}else{
			$key = $this->Setting->Key;
			$val = $this->$key;
			return  "$val";
		}
	}
	
	#setea los cruds para el registro
	public function crud($cruds)
	{
		if($cruds == false){
			$this->Setting->Cruds = array();
		}else{
			$this->Setting->Cruds = explode(",",$cruds);
		}
	}
	
	public function key($value)
	{
		$k = $this->Setting->Key;
		$this->$k = $value;
	}
	
	#seteado de datos a partir de un objeto
	public function set($row,$keys=array(),$ini=true)
	{
		$fields = $this->expose();
		$keys = ($ini==true)? array_keys((array) $row): $keys;
		$keys = array_diff($keys, $fields);

		foreach($fields as $field){
			
			if($this->$field instanceof ModelStatic){
				$cl = get_class($this->$field);
				if(isset($row->$field) && !is_null($row->$field)){
					$this->$field = new $cl($row->$field);
				}
			}else if($this->$field instanceof ModelJson){
				$clase = get_class($this->$field);
				if(isset($row->$field)){
					$this->$field = new $clase($row->$field);
				}
			}else if($this->$field instanceof Model){
				
				$clase = get_class($this->$field);
				$this->$field = new $clase();
				$k = $this->$field->Setting->Key;
				if(isset($row->$field) && !is_null($row->$field)){
					//echo $field.":".$row->$field."-".$k;
					//$this->$field->$k = $row->$field;
					$keys = $this->$field->set($row,$keys,false);
				}
			}else if($this->$field instanceof ModelFile){
				$k = $this->Setting->Key;
				if(isset($row->$k) && !is_null($row->$k)){
					$this->$field->setName($this,$field);
					$this->$field->setText($row->$k);
				}
			}else{
				if(isset($row->$field) && !is_null($row->$field)){
					$this->$field = $row->$field;
				}
			}
		}
		$this->Setting->Exists = true;
		$this->FechaModifica = date("Y-m-d");
		$this->UserModifica = App::$UserActive->Login;
		
		if($ini==true){
			foreach($keys as $key){
				$this->$key = $row->$key;
			}
		}else{
			return $keys;
		}			
	}

	#setea o devuelve el valor de un campo
	public function value($field, $value=false)
	{
		if($value===false){
			if($this->$field instanceof ModelStatic){
				return $this->$field->Id;
			}else if($this->$field instanceof Model){
				$k = $this->$field->Setting->Key;
				return $this->$field->$k;
			}else if($this->$field instanceof ModelFile){
				//$this->$field->setName($this,$field);
				return $this->$field->getText();
			}else{
				return $this->$field;
			}			
		}else{
			if(isset($this->$field)){
				if($this->$field instanceof ModelStatic){
					$cl = get_class($this->$field);
					$this->$field = new $cl($value); 
				}else if($this->$field instanceof ModelJson){
					//$this->$field->setName($this,$field);
					$this->$field->setText($value);
				}else if($this->$field instanceof ModelFile){
					//$this->$field->setName($this,$field);
					$this->$field->setText($value);
				}else if($this->$field instanceof Model){
					$clase = get_class($this->$field);
					if(empty($this->$field)){
						$this->$field = new $clase();
					}
					$k = $this->$field->Setting->Key;
					$this->$field->$k = $value;
				}else{
					$this->$field = $value;
				}
			}else{
				$this->$field = $value;
			}
		}
	}
	
	#copia en el objeto actual las propiedades del que se pasa
	public function copy(Model $model)
	{
		$keys = $this->expose();
		foreach($keys as $key){
			$value = $model->value($key);
			$this->value($key, $value);
		}
	}
	
	public function tables()
	{
		$t = array();
		$t[] = $this->Setting->Table;
		$keys = $this->expose();
		foreach($keys as $key){
			$dato = $this->$key;
			if($dato instanceof Model){
				$t[] = $dato->Setting->Table;
			}
		}
		return $t;		
	}
	
	public function validate()
	{
		if(count($this->Setting->Validate)>0){
			$keys = array_keys($this->Setting->Validate);
			foreach($keys as $k){
				if(empty($this->$k)){
					return false;
				}
			}
		}
		return true;
	}
	
	public function saveFiles()
	{
		$keys = $this->expose();
		foreach($keys as $key){
			$value = $this->$key;
			if($value instanceof ModelFile){
				$value->save();
			}
		}		
	}
	
	public function openFiles()
	{
		$keys = $this->expose();
		foreach($keys as $key){
			$value = $this->$key;
			if($value instanceof ModelFile){
				$value->open();
			}
		}	
	}
	
	public function order($field,$order=true)
	{
		$field.= ($order==true)? " ASC": " DESC ";
		$this->Setting->Order[] = $field;
	}
	
	public function method($name)
	{
		$this->Setting->Method = $name;
	}
	
	public function limit($n)
	{
		$this->Setting->Limit = $n;
	}
	
	public function columns($cols)
	{
		$this->Setting->Columns = $cols;
	}
	
	public function group($groups)
	{
		$this->Setting->Group = $groups;
	}
	
}
