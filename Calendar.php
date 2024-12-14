<?php

/* Class Calendar
 * Autor: Raul Ramos
 * Fecha: 1/04/2023
 * CopyRight: RedFox 2020
*/

namespace Fred;

class Calendar
{
	public $Db;
	public $Model;
	public $Method;
	public $Items = array();
	public $Field = "Fecha";
	
	private $Days = array();
	
	/*
	private $time;
	private $star;
	private $stop;
	private $dayi;

	
	public $year;
 	public $month = 1;
 	public $day = 1;
  
	public $view = "month";
	
	public $cants = array();
	public $dateStart;
	public $dateEnd;
	public $callback = "calendar";
	public $col_group = "Grupo";
	public $col_data = "Actividad";
	*/
	
	public function __construct($year=false)
	{
		$year = (!$year)? date("Y"): $year ;
		$year = (!empty($_GET["year"]))? $_GET["year"] : $year;
		$this->year = $year;
		
		$mes1 = (!empty($_GET["month"]))? $_GET["month"] : 1;
		$mes2 = (!empty($_GET["month"]))? $_GET["month"] : 12;
		
		$this->start = strtotime("$year-$mes1-01");
		$this->end = strtotime("$year-$mes2-01");
		
		$dias = date("t",$this->end); 
		$this->end = $this->end + (60*60*24*$dias);

	}
	
	public function loadItems()
	{
		$this->Model->filter(new ModelFilter($this->Field,date("Y-m-d",$this->start),">="));
		$this->Model->filter(new ModelFilter($this->Field,date("Y-m-d",$this->end),"<="));
		$this->Items = $this->Db->query($this->Model,$this->Method);
	}
	
	public function read()
	{
		$f = $this->Field;
		foreach($this->Items as $item)
		{
			$key = str_replace("-","",$item->$f);
			$this->Days[$key][] = $item;
		}
		
	}	
	
	public function __toString()
	{
		$year = $this->year;
		$ybac = $year - 1;
		$ynex = $year + 1;
		$controls = "<div class='calendar-controls'>";
		$controls.= "<a class='btn' href='?year=$ybac' title='Anterior'><i class='fa fa-backward'></i></a>";
		$controls.= "<a class='btn' href='?year=$year' title='Actual'><i class='fa fa-calendar-minus'></i></a>";
		$controls.= "<a class='btn' href='?year=$ynex' title='Siguiente'><i class='fa fa-forward'></i></a>";
		$controls.= "</div>";

		$vista = "<div class='calendar-head'>Calendario ".$this->year." $controls</div>";

		if(empty($this->Items)){
			$this->loadItems();
		}
		
		$this->read();

		if(empty($_GET["month"])){
			$vista.= $this->getYearView();
		}else{
			$mes = $_GET["month"];
			$vista.= $this->getMonthView($mes,false);
		}
		return $vista;	
		
		$str = $this->getDayView($day,true);

	}
	
	public function getYearView()
	{
		$str = "";	
		for($i=1;$i<=12;$i++){
			$str.= $this->getMonthView($i,true);
		}
		return $str;
	}


	public function getMonthView($mes,$mini=false)
	{
		//$callback = $this->callback;
		$mes = (int) $mes;
		$year = $this->year;
		$ynex = $year;
		$ybac = $year;
		$time = strtotime($this->year . "-" . $mes . "-01");

		$controls = "";
		if($mini==false){
			$next = $mes+1;if($next>12){$next=1;$ynex++;}
			$back = $mes-1;if($back<1){$back=12;$ybac--;}
			$controls.= "<div class='calendar-controls'>";
			//$controls.= "<a class='btn' href='?$callback&v=year' title='Todo el año'><span class='fa fa-calendar'></span></a>";
			$controls.= "<a class='btn' href='?year=$ybac&month=$back' title='Anterior'><i class='fa fa-backward'></i></a>";
			$controls.= "<a class='btn' href='?year=$ynex&month=$next' title='Siguiente'><i class='fa fa-forward'></i></a>";
			$controls.= "</div>";
		}
		//$ini = date("z",$time); //devuelve el numero del dia del año
		$d1 = "<th width='14%'>D</th><th width='14%'>L</th><th width='14%'>M</th><th width='14%'>M</th><th width='14%'>J</th><th width='14%'>V</th><th width='14%'>S</th>";
		$d2 = "<th width='14%'>DOMINGO</th><th width='14%'>LUNES</th><th width='14%'>MARTES</th><th width='14%'>MIERCOES</th><th width='14%'>JUEVES</th><th width='14%'>VIERNES</th><th width='14%'>SABADO</th>";
		$v = ($mini==false)? " calendar-month" : " calendar-mini";
		$dnam = ($mini==false)? $d2 : $d1;
		$name = $this->getMonthName($mes);
		$nds = date("N",$time) ; 
		$dia = $time - (60*60*24*$nds);
		$txt = "<div class='calendar$v'>";
		$txt.= "<table>";
		$txt.= "<tr><td colspan=7 class='head'><a href='?year=$year&month=$mes'>$name</a>$controls</td></tr>";
		$txt.= "<tr>$dnam</tr>";
		
		for($i=1;$i<=6;$i++){
			$txt.= "<tr>";
			for($j=1;$j<=7;$j++){
				
				$nummes = date("n",$dia);
				if($nummes==$mes){
					$numday = date("j",$dia);
					$date = date("Ymd",$dia);
					$txt.="<td class='td-body'>";
					$txt.= "<div class='day-head'>$numday</div>";
					if(!empty($this->Days[$date])){
						$cnt = ($mini==true)? count($this->Days[$date]): implode("",$this->Days[$date]);//aqui contenido
						$txt.= "<div class='day-body'>$cnt</div>";
					}else{
						$txt.= "<div></div>";
					}

				}else{
					$txt.="<td class='td-body calendar-inactive'><div></div>";
				}
				$dia+= (60*60*24);
				$txt.= "</td>";
			}
			$txt.= "</tr>";
		}
		$txt.= "</table></div>";		
		return $txt;
	}
	

	
	public function getDayView($day,$fcontrol = false)
	{
		$callback = $this->callback;
		$fecha = $this->days[$day];
		$mes = $this->getMonthName((int) date("m",$fecha));
		$name = $this->getDayName((int) date("N",$fecha));
		$dia = (int) date("d",$fecha);
		$next = $day+1;if($next>370){$next=370;}
		$back = $day-1;if($back<1){$back=1;}
		$str = "<div class='calendar'>";
		$controls = "";
		if($fcontrol){
			$controls.= "<div class='calendar-controls'>";
			$controls.= "<a class='btn' href='?$callback&v=mini' title='Todo el Calendario'><span class='fa fa-calendar-minus'></span></a>";
			$controls.= "<a class='btn' href='?$callback&v=day' title='Dias con Actividad'><span class='fa fa-calendar'></span></a>";
			$controls.= "<a class='btn' href='?$callback&v=day&day=$back' title='Dia Anterior'><span class='fa fa-backward'></span></a>";
			$controls.= "<a class='btn' href='?$callback&v=day&day=$next' title='Dia Siguiente'><span class='fa fa-forward'></span></a>";
			$controls.= "</div>";
		}
		$str.= "<div class='calendar-title'><h1>$mes $dia, $name</h1>$controls</div>";
		$str.= "<div class='calendar-body'>";
		$str.= "<table>";
		$str.= "<tr><th>".$this->col_group."</th><td><b>".$this->col_data."</b></td>";
		if(!empty($this->items[$day])){
			$grupos = $this->items[$day];
			$grukey = array_keys($grupos);
			foreach($grukey as $grupo){
				$str.= "<tr><th>$grupo</th><td>";
				$detalle = $grupos[$grupo];
				foreach($detalle as $det){
					$str.= "<div>$det</div>";
				}
				$str.= "</td></tr>";
			}
		}
		$str.= "</table></div></div>";
		return $str;
	}
	
	public function getDays()
	{
		$callback = $this->callback;
		$str = "
		<div style='position:relative'>
		<style>
			.calendar-title h1 {font-size:18pt;}
			.calendar-controls {position:absolute;top:-10px;}
		</style>";
		$str.= "<div class='calendar-controls'>";
		$str.= "<a class='btn' href='?$callback&v=mini'><span class='fa fa-calendar-minus'></span></a>";
		$str.= "</div>";
		
		$days = array_keys($this->items);
		foreach($days as $d){
			$str.= $this->getDayView($d);
		}
		$str.= "</div>";
		return $str;
	}
	
	public function getMonthName($id)
	{
		$day = array();
		$day[1] = "Enero";
		$day[2] = "Febrero";
		$day[3] = "Marzo";
		$day[4] = "Abril";
		$day[5] = "Mayo";
		$day[6] = "Junio";
		$day[7] = "Julio";
		$day[8] = "Agosto";
		$day[9] = "Septiembre";
		$day[10] = "Octubre";
		$day[11] = "Noviembre";
		$day[12] = "Diciembre";
		return $day[$id];
	}

	public function getDayName($id)
	{
		$day = array();
		$day[1] = "Lunes";
		$day[2] = "Martes";
		$day[3] = "Miercoles";
		$day[4] = "Jueves";
		$day[5] = "Viernes";
		$day[6] = "Sabado";
		$day[7] = "Domingo";
		return $day[$id];
	}
	
	
	public function addItem($date,$grupo,$item,$detalle)
	{
		$tim = strtotime($date);
		$dia = date("z",$tim);
		$dia+= $this->dayi + 1;
		$this->items[$dia][$grupo][$item] = $detalle;
	}
}
