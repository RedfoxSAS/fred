<?php

/* Map
 * 
 * @author: Raul Ramos
 * @date: 16/5/2024
 * 
 * Redfox 2024
 * Todo los derechos reservados
 * 
*/
namespace Fred;

class Map
{
	private $Width = "100%";
	private $Height = "200";
	private $Motor = "google";
	
	private $Ctr = false;
	
	private $Points = false;
	private $Auto = false;
	private $Icon = false;
	private $Select = false;
	
	//public $form = "formMapa";
	//public $Style = "";
	
	public function __construct($title,$ctr=false)
	{
		$this->Title = $title;
		
		if(!empty($_POST["mapMotor"])){
			$this->Motor = $_POST["mapMotor"];
			$_SESSION["FredMapMotor"] = $_POST["mapMotor"];
		}else{
			if(!empty($_SESSION["FredMapMotor"])){
				$this->Motor = $_SESSION["FredMapMotor"];
			}
		}
		$this->Ctr=$ctr;
	}
	
	function size($w,$h)
	{
		$this->Width = $w;
		$this->Height = $w;
	}
	
	public function points($points,$auto=false)
	{
		$this->Points = $points;
		$this->Auto = $auto;
	}
	
	public function icon($icon)
	{
		$this->Icon = $icon;
	}
	
	public function __toString()
	{
		$motor = $this->Motor;
		$prove = "";
		
		if($motor=="google"){
			$apikey[0] = "AIzaSyCABSe3WDQyaeHxrDwbAP52lfCeDBZ7Wss";  //wendy   02 - 12 -2018
			$apikey[1] = "AIzaSyCtPOpHgkPXUis01IZHMu_DYK8ShO0sODI";  //raul    01 - 06 -2018
			$apikey[2] = "AIzaSyC2L0zKlHeJTR9O6YdDb6JoH45yRXctxvw";  //andres  27 - 06 -2018
			$apikey[3] = "AIzaSyCvmQfpq5nr07Ir6ekOKmJsTVZgS-l3LZs";  //andres  27 - 06 -2018
			$apikey[4] = "AIzaSyCiGdBO-PTrfYMRMLYCi8-ubGQK5PWK0ps";  //spaniel.gerencia 02 - 06 -2018
			
			$api = $apikey[ rand(0, 4) ];
			$prove = "<script type=\"text/javascript\" src=\"https://maps.google.com/maps/api/js?key=$api\"></script>";
		
		}else if($motor=="mapbox"){
	
	    	$prove = "
				<script src=\"https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.js\"></script>
				<link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.51.0/mapbox-gl.css' rel='stylesheet' />
			";	
		
		}else if($motor=="arcgis"){
		
			$prove = "
				<link rel=\"stylesheet\" href=\"https://js.arcgis.com/4.24/esri/themes/light/main.css\">
				<script src=\"https://js.arcgis.com/4.24/\"></script>
			";
		
		}else if($motor=="osm"){
		
			$prove = "
				<link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet/dist/leaflet.css\" />
				<script src=\"https://unpkg.com/leaflet/dist/leaflet.js\"></script>
			";
		}		//$style = $this->Style;
		
		$w = $this->Width;
		$sc = $this->script();
		$fr = $this->controls();
		$tl = $this->Title;
		
		$htm = "

		$prove
		<script type=\"text/javascript\" src=\"/fred/assets/fred.map.js?2\"></script>
		<script type=\"text/javascript\" src=\"/fred/assets/fred.map-$motor.js?1\"></script>
		<link rel=\"stylesheet\" href=\"/fred/assets/fred.maps.css?1\">
		<div class=\"map-container\" style=\"width:$w;height:400px;\" id='map-container'>
			<div class='map-title'>
				<h1>$tl</h1>
				<span id='map-comment'></span>
				<form class=\"map-form\" method='POST' id='map-form'>$fr</form>	
			</div>
		
			<div class=\"map-body\" id=\"map-body\"></div>
	
		</div>

		$sc
		";	


		return $htm;
	}
	
	public function controls()
	{
		$htm = "";
		if($this->Motor=="Arcgis"){
			$htm.= "
			<button class='btn' type='button' name='' onclick='arcHyb();'><i class='fa fa-satellite' tilte='Vista de satelite'></i></button>
			<button class='btn' type='button' name='' onclick='arcNav();'><i class='fa fa-road' tilte='Vista navegacion'></i></button>
			";
		}
		
		$htm.= "
		<button class='btn' type='submit' name='mapMotor' value='arcgis' tilte='ArcGis'><img src='/fred/assets/images/arcgis.png'></button>
		<button class='btn' type='submit' name='mapMotor' value='osm' tilte='Open Street Map'><img src='/fred/assets/images/osmap.png'></button>
		<button class='btn' type='submit' name='mapMotor' value='mapbox' tilte='MapBox'><img src='/fred/assets/images/mapbox.png'></button>
		<button class='btn' type='submit' name='mapMotor' value='google' tilte='Google Maps'><img src='/fred/assets/images/mapggl.png'></button>
		";
		if($this->Select){
			$htm.= $this->Select;
		}
		if($this->Ctr){
			$htm.= "
			<button class='btn' type='button' onclick='mapPlay();' id='btnPlay'><i class='fa fa-play'></i></button>
			<button class='btn' type='button' onclick='mapStop();' id='btnStop'><i class='fa fa-stop'></i></button>
			<input type='range' min='1' max='10' value='5' onchange='mapVel(this)' id='' class='btn'>
			";
		}
		$htm.= "
		<button class='btn' type='button' onclick='mapFullScreen();' id='btnExpand'><i class='fa fa-expand'></i></button>
		";
		return $htm;
	}
	
	public function script()
	{
		$point = $this->Points;
		$auto = ($this->Auto)? 'true' : 'false';
		$icon = ($this->Icon)? "icono = \"".$this->Icon."\";" : "";
		$htm = "
		<script>
			$icon
			var autoShow = $auto;
			var points = [];
			$point
			function windowLoad()
			{
				mapResize();
				setTimeout(mapLoad,1000);
			}
			
			window.onload = windowLoad;
		
		</script>
		";
		return $htm;		
	}
	
	public function items($items,$field)
	{
		$view = "<option value='{{$field}}'>{{$field}}</option>";
		$item = reset($items);
		$item->view($view);
		$op = implode("",$items);
		$htm = "
		<select id='mapCtrCenter' class='btn' style='width:90px;' onchange=\"mapSearch(this);\">
			<option value='ALL'>TODOS</option>
			$op;
		</select>
		";
		$this->Select = $htm;
	}
	

}

