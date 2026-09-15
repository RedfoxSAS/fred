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

include_once "Control.php";

class Table extends Control
{
	public Model $Model;	
	public $Items = array();
	public $Cruds = array();

	private $view_td = false;
	private $view_th = false;
	
	public function __construct(Model $m)
	{
		$this->Model = $m;
	}

	public function crud($crud)
	{
		$this->Cruds[] = $crud;
	}

	#setea los campos a mostrar en formato de tabla
	public function fields(string $fields)
	{
		$str = "<tr>";
		$fields = explode(",",$fields);
		foreach($fields as $f){
			if(strpos($f, "}")){
				$str.= "<td>$f</td> ";
			}else{
				$str.= "<td>{{$f}}</td> ";
			}
		}
		$a = "";
		foreach($this->Cruds as $crud){
			$a.= (string) $crud;
		}
		$str = ($a!="")? $str."<td>$a</td>" : $str;
		$str.= "</tr>";
		$this->view_td = $str;
	}
	
	public function titles(string $titles,$w=false)
	{
		$str = "<thead><tr>";
		$titles = explode(",",$titles);
		$i=0;
		$h = ($w!=false)? explode(",",$w): array();
		foreach($titles as $f){
			$css = ($w!=false)? " style='width:" . $h[$i] . "%;'" : "";
			$str.= "<th $css>$f</th> ";
			$i++;
		}
		$str.= "</tr></thead>";
		$this->view_th = $str;		
	}
	
	public function control()
	{
		$this->Model->view($this->view_td);
		$str = "<table class=\"table-control table table-hover table-sm\">";
		$str.= $this->view_th;
		$str.= implode("",$this->Items);
		$str.= "</table>";
		return $str;
	}

	public function group($field)
	{
		$items = array();
		$grupo = new TableGroup();
		$anterior = null; // Cambiado a null para un control más preciso

		foreach($this->Items as $item){
			$actual = $item->$field;

			// Si cambia el valor (y no es la primerísima iteración)
			if($anterior !== null && $actual != $anterior){
				$items[] = $grupo; // Guardamos el grupo que acabamos de completar
				$grupo = new TableGroup(); // Creamos uno nuevo para el siguiente bloque
			}
			
			$grupo->add($item);
			$anterior = $actual;
		}

		// ¡IMPORTANTE! Guardar el último grupo generado tras salir del bucle
		if (!$grupo->isEmpty()) { // O simplemente $items[] = $grupo; si siempre hay elementos
			$items[] = $grupo;
		}

		$this->Items = $items;
	}

}

class TableGroup 
{
	public $Items = array();

	public function add($item)
	{
		$this->Items[] = $item;
	}

	public function isEmpty()
	{
		if(count($this->Items)>0) return false;
		return true;
	}

	public function __toString()
	{
		$str = "<tbody>";
		$str.= implode("",$this->Items);
		$str.= "</tbody>";
		return $str;
	}
}
