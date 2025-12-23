<?php

/* Class Datetime
 * 
 * Clase para el manejo de fecha y el calculo de fechas 
 * @Autor		Raul Ramos Guzman
 * @Fecha		1/01/2023
 * @Copyright 	Redfox 2023
 * 
 * 
*/

namespace Fred;

class Datetime
{ 
    public $Text;
    public $Date;
    public $Time;
    public $d;
    public $m;
    public $y;

	public static function months()
	{
		 $meses = [
            "01" => "Enero",
            "02" => "Febrero",
            "03" => "Marzo",
            "04" => "Abril",
            "05" => "Mayo",
            "06" => "Junio",
            "07" => "Julio",
            "08" => "Agosto",
            "09" => "Septiembre",
            "10" => "Octubre",
            "11" => "Noviembre",
            "12" => "Diciembre"
        ];
		return $meses;
	}

	public static function days()
	{
		$dias = array();
		$dias[1] = "Lunes";
		$dias[2] = "Martes";
		$dias[3] = "Miercoles";
		$dias[4] = "Jueves";
		$dias[5] = "Viernes";
		$dias[6] = "Sabado";
		$dias[7] = "Domingo";
		return $dias;
	}
   
    public function __construct($date=false)
    {
		if(!$date) { $date = date("Y-m-d H:i:s");}
		$tiempo = strtotime($date);
		$this->Text = $date;
		$this->Date = date("Y-m-d",$tiempo);
		$this->Time = date("H:i:s",$tiempo);
		$this->d = date("d",$tiempo);
		$this->m = date("m",$tiempo);
		$this->Y = date("Y",$tiempo);
	}
	
	public function __toString()
	{
		return $this->Text;
	}
    
    public function day()
    {
		$dia = date("N",strtotime($this->Text));
		$dias = Datetime::days();
		return $dias[$dia];
	}
	
    public function month($mes=false)
    {
		if(!$mes){
			$mes = date("m",strtotime($this->Text));
		}
		$meses = Datetime::months();
		return $meses[$mes];
	}
	
	public function diff($date,$unidad="s")
	{
		$fecha1 = strtotime($this->Text);
		
		if ($date instanceof Datetime) {
			$fecha2 = strtotime($date->Text);
		}else if ( is_string($date) ) {
			if($date=="hoy"){
				$fecha2 = strtotime(date("Y-m-d"));
			}else{
				$fecha2 = strtotime($date);
			}
		}else{
			$fecha2 = strtotime(date("Y-m-d"));
		}

		$diff = $fecha2 - $fecha1;
		if($unidad == "s"){
			$diff = $diff ;
		}else if ($unidad == "i"){
			$diff = $diff / ( 60);
		}else if ($unidad == "h"){
			$diff = $diff / ( 60 * 60);
		}else if ($unidad == "d"){
			$diff = $diff / ( 60 * 60 * 24);
		}else if ($unidad == "m"){
			$diff = $diff / ( 60 * 60 * 24 * 30.4375);
		}else if ($unidad == "y"){
			$diff = $diff / ( 60 * 60 * 24 * 365.25);
		}else{
			$diff = $diff;
		}
		return $diff;
	}
	
	public function age($unidad="d")
	{
		$hoy = date("Y-m-d H:i:s");
		$dif = $this->fecha_diff($hoy,"y");
		$mul = array(12,30.4375,24,60,60,1);
		$str = array("años","meses","dias","horas","minutos","segundos");
		$key = array_search($unidad,$str);
		if($key===false) {$key = 2;}
		$res = array();
		for($i=0;$i<=$key;$i++){
			$val[$i] = floor($dif);
			$dif = ($dif - $val[$i]) * $mul[$i] ;
			if ($val[$i]>0){
				$res[] = $val[$i] . " " . $str[$i];
			}
		}
		return implode(", ",$res);	
	}
	
	public function sum($cantidad,$unidad = "s",$formato = "Y-m-d H:i:s")
	{
		$tim = strtotime($this->Text);
		if($cantidad < 0) { $signo = ""; } else { $signo = "+ ";}
		if($unidad == "s"){
			$tim = $tim + $cantidad;
		}else if ($unidad == "i"){
			$tim = strtotime("$signo $cantidad min",$tim);
		}else if ($unidad == "h"){
			$tim = strtotime("$signo $cantidad hour",$tim);
		}else if ($unidad == "d"){
			$tim = strtotime("$signo $cantidad day",$tim);
		}else if ($unidad == "m"){
			$tim = strtotime("$signo $cantidad months",$tim);
		}else if ($unidad == "y"){
			$tim = strtotime("$signo $cantidad years",$tim);
		}else{
			
		}
		$this->Text = date($formato,$tim);
		$this->Date = date("Y-m-d",$tim);
		$this->Time = date("H:i:s",$tim);
		$this->d = date("d",$tim);
		$this->m = date("m",$tim);
		$this->Y = date("Y",$tim);
		return date($formato,$tim);
	}

}
