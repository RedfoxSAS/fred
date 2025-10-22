<?php

/* Class Number
 * 
 * Clase para el manejo numero enteros
 * Autor: Raul Ramos
 * Fecha: 1/04/2019
 * CopyRight: RedFox 2020
 * 
 * 
*/
namespace Fred;

class Number
{
    
    public $value;
  
    public function __construct($num=0)
    {
		$this->value = $num;
	}
	
	public function __toString()
	{
		return $this->text;
	}
    
    public function basico($n)
    {
		$valor = array("","uno","dos","tres","cuatro","cinco","seis","siete","ocho","nueve","diez",
		"once","doce","trece","catorce","quince","dieciseis","diecisiete","dieciocho","diecinueve","veinte",
		"veiniuno","veintidos","veintitres","veinticuatro","veinticinco","veintiseis","veintisiete","veintiocho",
		"veintinueve");
		if(isset($valor[$n])){
			return $valor[$n];
		}else{
			return "";
		}
	}
	
	public function decena($n)
	{
		$decenas = array(30=>"treinta", 40=>"cuarenta", 50=>"cincuenta" , 
		60=>"sesenta" ,	70=>"setenta",80=>"ochenta",90=>"noventa");
		if($n <=29) { return $this->basico($n);}
		$x = $n % 10;
		if($x == 0) {
			return $decenas[$n];
		}else{
			return $decenas[$n - $x] . " y " . $this->basico($x);
		}
	}
	
	public function centena($n)
	{
		$centenas = array(100=>"ciento", 200=>"docientos", 300=>"trecientos" , 
		400=>"cuatrocientos" ,	500=>"quinientos", 600=>"seiscientos",
		700=>"setecientos", 800=>"ochocientos", 900=>"novecientos");
		if($n<100) {return $this->decena($n);}
		if($n==100) { 
			return "cien"; 
		}else{
			$u = substr($n,0,1);
			$d = substr($n,1,2);
			return $centenas[$u*100] . " " . $this->decena($d);
		}		
	}
	
	public function miles($n)
	{
		if($n<1000) {return $this->centena($n);}
		if($n==1000){
			return "mil";
		}else{
			$l = strlen($n);
			$c = (int) substr($n,0,$l-3);
			$x = (int) substr($n,-3);
			if($c==1) { 
				$cadena = "mil " . $this->centena($x);
			}else if($x != 0) {
				$cadena = $this->centena($c) . " mil " . $this->centena($x);
			}else{
				$cadena = $this->centena($c) . " mil";
			}
			return $cadena;
		}
	}
	
	public function millones($n)
	{
		if($n<1000000) { return $this->miles($n);}
		if($n==1000000){
			return "un millon";
		}else{
			$l = strlen($n);
			$c = (int) substr($n,0,$l-6);
			$x = (int) substr($n,-6);
			if($c==1) { 
				$cadena = " millon ";
			}else{
				$cadena = " millones ";
			}
			return $this->miles($c).$cadena.( ($x>0)? $this->miles($x):"");		
		}
	}
	
	public function text($currency=false)
	{
		$str = "";
		if($this->value >=1 && $this->value <=29) { 
			$str =  $this->basico($this->value);
		}else if ($this->value >=30 && $this->value <100){
			$str = $this->decena($this->value);
		}else if ($this->value >=100 && $this->value <1000){
			$str = $this->centena($this->value);
		}else if ($this->value >=1000 && $this->value <1000000){
			$str =  $this->miles($this->value);
		}else if ($this->value >=1000000){
			$str =  $this->millones($this->value);
		}
		if($currency!=false)
		{
			$str.= $currency;
			$str = strtoupper($str); 
		}
		return $str;
	}
}
