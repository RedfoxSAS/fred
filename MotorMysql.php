<?php

/* MotorMySql.php
 * @Autor		Raul Ramos Guzman
 * @Fecha		1/01/2023
 * @Copyright 	Redfox 2023
 * 
 * Motor para acceder a una base de datos MySql
 * Para ser reconocido por el sistema requiere cumplir con la interface
 * MotorDbi.php
 * 
*/

namespace Fred;

include_once "Model.php";
include_once "MotorDbi.php";

Class MotorMySql implements MotorDbi
{
	
	public $Sql;
	public $Msg = true;
	
	public string $Database = "";
	public string $Server = "";
	public string $User = "";
	public string $Password = "";
	
	static private $_currentTable;
	static private $_currentFields;
	
	
	///REVISADO 
	/*
	private $_conn;
	
	
	private $_filters = array();
	private $_order = array();
	private $_limit = false;
	private $_sqlTotal = "";

	public $base;
	public $dataString = "";
	public $tableExists = false;
	public $position = false;
	public $length = false;
	* */
	
	
	public function __construct($base=false,$serv=false,$user=false,$pass=false)
	{
		if($base instanceof MotorMySql){
			$this->clonar($base);
			if($serv) { $this->Database = $serv; }
		}else if(is_string($base)){
			if($serv) { $this->Server = $serv; }
			if($user) { $this->User = $user; }
			if($pass) { $this->Password = $pass; }
			if($base) { $this->Database = $base; }
		}
	}
	
	public function createDataBase($nombre)
	{
		$this->_conn = mysqli_connect($this->Server,$this->User,$this->Password);
		$sql = "CREATE DATABASE $nombre";
		$rq = mysqli_query($this->_conn, $sql);
		$this->database = $nombre;
		mysqli_close($this->_conn); 
		return $rq;
	}
	
	#conecta con la base de datos ----- revisado
	private function _connect()
	{
		$this->_conn = mysqli_connect($this->Server,$this->User,$this->Password,$this->Database);
	}
	
	# desconecta la base de datos ----- revisado
	private function _disconn()   
	{
		mysqli_close($this->_conn); 
	}
	
	#clona la base de datos
	public function clonar(MotorMySql $db)
	{
		$this->Database = $db->Database;
		$this->User = $db->User;
		$this->Password = $db->Password;
		$this->Server = $db->Server;
	}
	
	#llena los datos de un modelo   ----- revisado
	private function set(Model $modelo, $row, $open=false)
	{
		//return $modelo->set($row);
		/*
		$fields = $modelo->expose();
		$keys = array_keys((array) $row);
		$keys = array_diff($keys, $fields);
		foreach($fields as $field){
			if($modelo->$field instanceof ModelStatic){
				$cl = get_class($modelo->$field);
				if(isset($row->$field) && !is_null($row->$field)){
					$modelo->$field = new $cl($row->$field);
				}
			}else if($modelo->$field instanceof Model){
				
				$clase = get_class($modelo->$field);
				$modelo->$field = new $clase();
				$k = $modelo->$field->Setting->Key;
				if(isset($row->$k) && !is_null($row->$k)){
					$modelo->$field->$k = $row->$k;
					$this->set($modelo->$field, $row);
				}
			}else if($modelo->$field instanceof Collection){
				if($open==true){
					$k = $modelo->Setting->Key;
					$modelo->$field->filter(new ModelFilter($k,$row->$k,"="));
					$this->query($modelo->$field);
				}
			}else{
				if(isset($row->$field) && !is_null($row->$field)){
					$modelo->$field = $row->$field;
				}
			}
			//unset($row->$field);
		}
		$keys = array_keys((array) $row);
		$keys = array_diff($keys, $fields);
		foreach($keys as $key){
			//$modelo->$key = $row->$key;
		}
		$modelo->Setting->Exists = true;
		return $modelo;
		*/	
	}
	
	#consulta los datos de un unico registro, regresa true exito ----- revisado	
	public function open(Model $model)
	{
		$sql = $this->createSqlOpen($model);
		$this->SqlOpen = $sql;
		if($sql){
			$this->Sql.=  $sql . " ; ";
			$this->_connect();
			$request = mysqli_query($this->_conn, $sql);
			$this->_disconn();
			if($request){
				$row = mysqli_fetch_object($request);
				if($row){
					//$modelo = $this->set($modelo,$row,true);
					$model->Id = 1;
					$model->set($row);
					$model->openFiles();
					$metodo = $model->Setting->Method;
					if($metodo!=false){
						$model->$metodo();
					}
					return true;
				}
			}
		}
		return false;		
	}
	
	#Funcion que busca un listado de registros en una tabla ----- revisado
	public function query(Model $model,$instr = false)
	{
		#realizando peticion al servidor
		//$this->dataString = "";
		$sql = $model->Setting->Sql;
		if($sql==false){
			$sql = $this->createSqlQuery($model);
			$sql = ($model->Setting->Group != false)? $sql." GROUP BY ".$model->Setting->Group : $sql;
			$sql = (count($model->Setting->Order)>0)? $sql.= " ORDER BY " . implode(",", $model->Setting->Order): $sql; 
			$sql = ($model->Setting->Limit > 0)? $sql." LIMIT ".$model->Setting->Limit : $sql;
		}

		$lista = ($instr==true)? "" : array();
		$summa = ($model->Setting->Summary!=false)? explode(",",$model->Setting->Summary):false;
		if($sql!=false){
			
			$this->Sql =  $sql;
			$this->_connect();
			$request = mysqli_query($this->_conn, $sql);
			$this->_disconn();
			#creando arreglo para devolver;

			$key = $model->Setting->Key;
			$class = get_class($model);
			if($request){
				$i = 0;
				$sumar = new $class();
				if($summa){
					foreach($summa as $s){
						$sumar->$s = 0;
					}
				}
				while ($row = mysqli_fetch_object($request)){
					$i++;
					$nuevo = new $class();
					//$nuevo = $this->set(new $class(), $row);
					$nuevo->Id = $i;
					$nuevo->set($row);
					$metodo = $model->Setting->Method;
					if($metodo!=false){
						$nuevo->$metodo();
					}
					if($instr==true){
						$lista.= (string) $nuevo;
					}else{
						$lista[(string) $nuevo->$key] = $nuevo;
					}
					if($summa){
						foreach($summa as $s){
							$sumar->$s = $sumar->$s + $nuevo->$s;
						}
					}
				}
				if($summa){
					$sumar->Setting->Summary = true;
					if($instr==true){
						$lista.= (string) $sumar;
					}else{
						$lista["summary"] = $sumar;
					}
				}
			}
			return $lista;
		}
		return false;
	}
	
	

	public function getDataString()
	{
		return $this->dataString;
	}
	
	//FUNCIONES DEFINIDAS POR LA INTREFAZ
	
	public function runMdl($sql)
	{
		$this->_connect();
		$this->Sql.=  $sql . "  ; ";
		$rq = mysqli_query($this->_conn, $sql);
		$id = mysqli_insert_id($this->_conn);
		if($id > 0){ $rq = $id; }
		
		$this->_disconn();
		return $rq;
	}
	
	public function runSql($sql)
	{
		$this->_connect();
		$this->Sql =   $sql . " ; ";
		$rq = mysqli_query($this->_conn, $sql);
		if($rq){	
			$data = array();
			while ($row = mysqli_fetch_array($rq, MYSQLI_ASSOC)){
				$data[] = $row; 
			}
			return $data;
		}
		return false;
	}
	
	public function getColumns($tablename)
	{
		if(self::$_currentTable==$this->Database.".".$tablename){
			$this->tableExists = true;
			return self::$_currentFields;
		}		
		$sql = "SHOW COLUMNS FROM $tablename";
		$this->_connect();
		$request = mysqli_query($this->_conn, $sql );
		$data = array();

		if($request){	
			while ($row = mysqli_fetch_array($request, MYSQLI_ASSOC)){
				$data[$row["Field"]]["Format"] = "'[Value]'";
				$data[$row["Field"]]["Value"] = $row["Default"];

				if(strpos($row["Type"],"int")!==false){
					$data[$row["Field"]]["Format"] = "'[Value]'";
					$data[$row["Field"]]["Value"] = "0";
				}else if($row["Type"]=="date"){
					$data[$row["Field"]]["Format"] = "'[Value]'";
					$data[$row["Field"]]["Value"] = date("Y-m-d");
				}else if($row["Type"]=="double" || $row["Type"]=="float"){
					$data[$row["Field"]]["Format"] = "'[Value]'";
					$data[$row["Field"]]["Value"] = "0";
				}else if($row["Type"]=="time"){
					$data[$row["Field"]]["Format"] = "'[Value]'";
					$data[$row["Field"]]["Value"] = "0";
				}else if($row["Type"]=="longtext"){
					$data[$row["Field"]]["Format"] = "'[Value]'";
					$data[$row["Field"]]["Value"] = "{}";
					
				}else{
					//echo $row["Type"];
				}
				if($row["Extra"]=="auto_increment"){
					$data[$row["Field"]]["Format"] = "[Value]";
					$data[$row["Field"]]["Value"] = "NULL";
				}
			}
			self::$_currentTable = $this->Database . "." . $tablename;
			self::$_currentFields = $data;
			$this->_disconn();
			$this->tableExists = true;
		}else{
			$this->tableExists = false;
		}
		return $data ;
	}
	
	
	
	public function save(Model $model, $msg = true)
	{
		$this->Msg = $msg;
		$sql = "";
		$r = false;
		if($model->Setting->Exists){
			$sql = $this->createMdlUpdate($model);
			
			$r = $this->runMdl($sql);
			$e = (empty($model->Estado))? "": ". Registro " . $model->Estado;
			if($r > 0){
				$this->msg("($r) Registro actualizado exitosamente $e",0);
				$model->saveFiles();
			}else{
				$this->msg("El registro no se pudo modificar",3);
			}
		}else{
			$sql = $this->createMdlInsert($model);
			$r = $this->runMdl($sql);
			$e = (empty($model->Estado))? "": ". Registro " . $model->Estado;
			$key = $model->Setting->Key;
			$val = $model->value($key);
			if($r > 0){
				if($r > $val){
					$model->value($key,$r);
				}
				$model->Setting->Exists = true;
				$this->msg("Nuevo registro creado exitosamente $e",0);
				$model->saveFiles();
			}else{
				$this->msg("Error al crear el registro, Verifique los datos",3);
			}
			
			
			
		}
		$this->Sql = $sql;
		return $r;
	}
	
	// borra un registro en una base de datos a partir de un modelo
	public function delete(Model $model)
	{
		
		$sql = $this->createMdlDelete($model);
		$r = $this->runMdl($sql);
		if($r){
			$this->msg("El registro se elimino correctamente");
			return true;
		}
		return false;
	}
	
	

	public function createSqlOpen(Model $model)
	{
		$tab = $model->Setting->Table;
		$key = $model->Setting->Key;
		
		$rel = $this->createRelations($model);
		
		$cols = $this->getColumns($tab);
		
		$sql = "SELECT *";
		if(isset($cols["Estado"])){
			$sql.= ", $tab.Estado as Estado";
		}
		$sql.= " FROM $tab ";
		$sql.= implode(" ",$rel);
		if(!empty ($model->$key)){
			$sql.= " WHERE $key = '" . $model->$key . "'";
		}else{
			$sql.= $model->filters();
		}

		return $sql;		

	}
	
	public function createMdlUpdate(Model $model)
	{
		$table = $model->Setting->Table;
		$key = $model->Setting->Key;
		$data = $model->data(true);
		
		$fields = $this->getColumns($table);
		$keys = array_keys($fields);
		foreach($keys as $field){
			if(empty($data[$field])){
				$str = "$table.$field = " . str_replace("[Value]",$fields[$field]["Value"],$fields[$field]["Format"]);
				$values[] = $str;
			}else{
				$str = "$table.$field = " . str_replace("[Value]",$data[$field],$fields[$field]["Format"]);
				$values[] = $str;
			}
		}

		$id =  $data[$key];
		$sql = "UPDATE $table ";
		$sql.= " SET " . implode(",",$values);	
		$sql.= " WHERE $table.$key ='$id'";	
		return $sql;
	}
	
	public function createMdlInsert(Model $model)
	{
		$table = $model->Setting->Table;
		$data = $model->data(true);
		
		$fields = $this->getColumns($table);
		$keys = array_keys($fields);
		
		$values = array();
		foreach($keys as $field){
			if(!empty($data[$field])){
				$values[] = str_replace("[Value]",$data[$field],$fields[$field]["Format"]);
			}else{
				$values[] = str_replace("[Value]",$fields[$field]["Value"],$fields[$field]["Format"]);
			}
		}
			
		$sql = "INSERT INTO $table (" . implode(",",$keys) . ") ";
		$sql.= "VALUES (" . implode(",",$values) . ")";
		return $sql;
	}
	
	//Crea el sql delete para borrar un registro a partir de un modelo
	public function createMdlDelete(Model $model)
	{		
		$table = $model->Setting->Table;
		$key =  $model->Setting->Key;
		$sql = "DELETE FROM " . $table;
		$sql.= " WHERE ";
		$sql.= $key . "=" . $model->$key;
		return $sql;
	}
	

	
	private function _getTotal()
	{
		if($this->_sqlTotal!=""){
			$this->_connect();
			$request = mysqli_query($this->_conn, $this->_sqlTotal);
			if($request){
				$row = mysqli_fetch_object($request);
				if($row){
					$this->length = $row->total;
				}
			}
			$this->_disconn();
		}
	}
	
	public function createRelations(Model $model, $i=0)
	{
		$i++;
		$relation = array();
		$fields = $model->expose();

		foreach($fields as $f){
			if($model->$f instanceof Model){
				$t1 = $model->Setting->Table;
				$t2 = $model->$f->Setting->Table;
				$k = $model->$f->Setting->Key;
				$rel = "LEFT JOIN $t2 ON $t1.$f = $t2.$k";
				$relation[] = $rel;
				$relation = array_merge($relation,$this->createRelations($model->$f,$i));
			}
		}
		if($model->Setting->Relations!=false){
			$relation[] = $model->Setting->Relations;
		}
		return $relation;	
	}
		
	public function createSqlQuery(Model $model)
	{
		$tab = $model->Setting->Table;
		$key = $model->Setting->Key;
		
		$rel = $this->createRelations($model);
		
		$cols = $this->getColumns($tab);
		
		$sql = "SELECT ";
		$sql.= ($model->Setting->Columns!=false)? $model->Setting->Columns:"*";
			
		if(isset($cols["Estado"])){
			$sql.= ", $tab.Estado as Estado";
		}
		$sql.= " FROM $tab ";
		$sql.= implode(" ",$rel);
		$sql.= $model->filters();
		return $sql;
	}
		
	public function test()
	{
		$this->_connect();
		$request = mysqli_query($this->_conn, "show tables");
		$this->Sql = "show tables";
		if($request){
			$this->_disconn();
			return true;
		}else{
			$this->_disconn();
			return false;
		}			
	}
	
	public function getConfig()
	{
		return $this->Database . "," . $this->Server . ","  . $this->User . "," . $this->Password;
	}
	

	
	public function msg($mensaje,$t=0)
	{
		if($this->Msg==true){
			Modal::msg($mensaje,$t);
		}
	}
	
	
	
}