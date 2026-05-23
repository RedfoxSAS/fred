<?php

namespace Fred;
use Fred;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fred = "/var/www/fred";
$public = "/var/www/html";

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
	
    $c =  (string) $controlador;   
    include_once $c;

	$app = new ProgramMain();
	$app->run($route);
    
	echo $app;
	
}else{
	echo "No se encontro el archivo";
}
				
		
					
					
