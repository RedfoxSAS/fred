<?php

/* List.php
 * @Autor		Raul Ramos Guzman
 * @Fecha		1/01/2023
 * @Copyright 	Redfox 2023
 * 
 * Representa una lista de objetos de un tipo
 * 
 * */

namespace Fred;

include_once "MotorDbi.php";
include_once "Model.php";

class Collection
{
	public MotorDbi $Db;
	public Model $Model;	
	public $Items = array();
	public int $Length = 0;
	public int $Limit = 0;
	
	public $Url = false;

	private $view_td = false;
	private $view_th = false;
	private $view_item = false;
	private $clases;
	
	public function __construct(Model $m)
	{
		$this->Model = $m;
		$this->clases = array();
	}
	
	public function filter(ModelFilter $filter,$index=1)
	{
		$this->Model->filter($filter,$index);
	}
	
	public function crud($crud)
	{
		$this->Model->crud($crud);
	}
	
	public function view($view)
	{
		if($view instanceof View){
			$this->view_item = $view->Text;
		}else{
			$this->view_item = $view;
		}
	}

	public function styles(string $styles)
	{
		$this->clases = explode(",",$styles);
	}

	#setea los campos a mostrar en formato de tabla
	public function fields(string $fields)
	{
		$str = "<tr>";
		if($this->Url!=false){
			$str = "<tr onclick=\"location.href='" . $this->Url . "'\" style=\"cursor:pointer;\">";
		}
		if(strpos($fields,";")>0){
			$fields = explode(";",$fields);
		}else{
			$fields = explode(",",$fields);
		}
		$i = 0;
		foreach($fields as $f){
			$clase = (!empty($this->clases[$i]))? $this->clases[$i]:"";
			if(strpos($f, "}")){
				$str.= "<td class='$clase'>$f</td>";
			}else{
				$str.= "<td class='$clase'>{{$f}}</td>";
			}
			$i++;
		}
		$a = "";
		foreach($this->Model->setting()->Cruds as $name){
			$c = App::$Crud->get($name);
			@$a = ($c!=false)? $a." <a href=\"".$c[1]."\" title='".$c[0]."'><i class=\"" . $c[2] . "\"></i></a>":$a;
		}
		$str = ($a!="")? $str."<td class='collection-crud'>$a</td>" : $str;
		$str.= "</tr>";
		$this->view_td = $str;
	}
	
	public function titles(string $titles)
	{
		$str = "<thead><tr>";
		$titles = explode(",",$titles);
		$i = 0;
		foreach($titles as $f){
			$clase = (!empty($this->clases[$i]))? $this->clases[$i]:"";
			$str.= "<th class='$clase'>$f</th>";
			$i++;
		}
		$str.= "</tr></thead>";
		$this->view_th = $str;		
	}
	
	public function loadItems()
	{
		$this->Items = $this->Db->query($this->Model);
	}
	
	public function __toString()
	{
		$items = "";
		if($this->view_item==false){
			$this->Model->view($this->view_td);
		}else{
			$this->Model->view($this->view_item);
		}		
		if(empty($this->Items)){
			if(empty($this->Db)){
				return "No hay elementos para mostrar";
			}
			$this->Db->setDataStringEnabled(true);
			$this->loadItems(true);
			$items = $this->Db->getDataString();
		}else{
			$items = implode("",$this->Items);
		}
		
		if($this->view_item==false){
			$str = "<table class=\"collection table table-hover table-sm\">";
			$str.= $this->view_th;
			$str.= "<tbody>$items</tbody>";
			$str.= "</table>";
			return $str;
		}else{
			return $items;
		}
	}
}
