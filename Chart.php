<?php
/**
 * Char.php
 * utiliza la libreria char js para crear graficos usando canvas
 * @author		Raul E. Ramos Guzman
 * @copyright	Spaniel 2019
 * @version 	Release: 1.2
 * @date		06/06/2019
 * 
*/
namespace Fred;
include_once "App.php";

class Chart 
{
	
	static public $Count = 0;
	
	public $W;
	public $H;
	public $Name;
	public $Title;
	public $Model;
	public $Items = array();
	public $Labels = "";
	
	private $Datasets = array();	
	private $Graph;
	private $Type = "bar";
	
	
	public function __construct($title)
	{
		Chart::$Count++;
		$this->Name = "Chart" . str_replace(" ","",$title);
		$this->Title = $title;
		$this->W = "100%";
	}
	
	public function size($w,$h)
	{
		$this->W = $w;
		$this->H = $h;
	}
	
	public function dataset($field,$lbl)
	{
		$this->Model->view("{{$field}}");
		$data = implode(",",$this->Items);
		$this->Datasets[] = new Dataset($lbl,$data);
	}
	
	public function label($field)
	{
		$this->Model->view("'{{$field}}'");
		$this->Labels = implode(",",$this->Items);
	}
		
	private function chartHead()
	{
		$h = ($this->H)? "height:" . $this->H: "";
		$w = "width:" . $this->W;
		$htm = "
		<script src=\"/fred/assets/chartjs.min.js\"></script>
		<script src=\"/fred/assets/chartjs.utils.js\"></script>
		<div id=\"chart-container\" style=\"$w;$h\">
			<canvas id=\"chart-canvas\" style=\"$w;$h\"></canvas>
		</div>
		";
		
		return $htm;		
	}
	
	private function chartData()
	{
		$lbls = $this->Labels;
		$data = implode(",",$this->Datasets);
		$gdata = "		
		var color = Chart.helpers.color;
		var ChartData = {
			labels: [$lbls],
			datasets: [
			$data
			]
		};
		";	
		
		return $gdata;
	}
	
	public function graph()
	{
		$ttle = $this->Title;
		$data = $this->chartData();
		$head = $this->chartHead();
		$type = $this->Type;
		$htm = "
		$head
		<script language=\"javascript\">
		$data
		window.onload = function() {
			var ctx = document.getElementById('chart-canvas').getContext('2d');
			window.myBar = new Chart(ctx, {
				type: '$type',
				data: ChartData,
				options: {
					responsive: false,
					legend: {
						position: 'top',
					},
					title: {
						display: true,
						text: '$ttle'
					}
				}
			});
		};
		</script>
		";
		return $htm;
	}
	
	public function __toString()
	{
		return $this->graph();
	}
	
	public function bar()
	{
		$this->Type = "bar";
	}
	
	public function line($fill=false)
	{
		$this->Type = "line";
		foreach($this->Datasets as $d)
		{
			$d->Fill = $fill;
		}
	}	
}

class Dataset 
{
	public static $N = 0;
	public $Color;
	public $Label;
	public $Data;
	public $Fill = true;
	
	public function __construct($lbl,$dat)
	{
		Dataset::$N++;
		$this->Label = $lbl;
		$this->Data = $dat;
		$this->Color = Dataset::colors();
	}
	
	public function __toString()
	{
		$lbl = $this->Label;
		$dat = $this->Data;
		$col = $this->Color;
		$fil = $this->Fill? 'true' : 'false'; 
		$str = "
		{
			label: '$lbl',
			data: [$dat],
			fill: $fil,
			borderWidth: 1,
			borderColor: window.chartColors.$col,
			backgroundColor: color(window.chartColors.$col).alpha(0.5).rgbString()
		}
		";
		return $str;
	}
	
	public static function colors($i=false)
	{
		$i = (!$i)?  Dataset::$N : $i; 
		$cols = [
		  1=>"blue",
		  2=>"red",
		  3=>"yellow",
		  4=>"balck",
		  5=>"brown",
		  6=>"pink",
		  7=>"gree"
		];
		return $cols[$i];
	}
}
	
