<?php

namespace Fred;
use Fred;

$fred = "D:\\XAMPP\\fred";
$public = "D:\\XAMPP\\htdocs";

$inc_path = get_include_path();
$inc_path.= PATH_SEPARATOR;
$inc_path.= $fred; 

set_include_path($inc_path);
date_default_timezone_set('America/Bogota');

include "Route.php";
$routes = new Route(true);
$route = $routes->getRoutes();

$host = str_replace(".","_", $_SERVER["HTTP_HOST"]);

include "App.php";

$controlador = App::setting($host,"redfox_com_co",$fred,$public);

if($controlador!=false){
	
	include $controlador;
	$app = new Main();
	$app->run($route);
	echo $app;
	
}else{
	echo "No se encontro el archivo";
}
				
		
					
					
