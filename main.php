<?php
/*
 * main.php
 * Copyright 2024 User <User@RAUL>
 * 
 * Funciones de instalacion y configuracion de fred
 * 
 * 
 */
 
namespace Fred;

include_once "Program.php";

class Main extends Program
{
	
	protected function startComponents()
	{
		$this->authorize("install",true);
	}

	public function main($data)
	{
		return "<h1>Bievenido a Fred</h1>";
	}

	public function install($data)
	{

		// Definir las rutas de origen y destino
		$fred = App::$Setting->Fred;
		$web = App::$Setting->Web;

		// Llamar a la función para copiar la carpeta assets
		$this->copiarCarpeta($fred . "/assets", $web . "/fred/assets");
		echo "Carpeta copiada con éxito!";

	}

	private function copiarCarpeta($origen, $destino) 
	{
		// Si no existe la carpeta de destino, créala
		if (!file_exists($destino)) {
			mkdir($destino, 0777, true); // Crea la carpeta de destino con permisos adecuados
		}

		// Abre la carpeta de origen
		$archivos = scandir($origen);

		// Itera a través de los archivos en la carpeta origen
		foreach ($archivos as $archivo) {
			// Evita las carpetas especiales '.' y '..'
			if ($archivo != '.' && $archivo != '..') {
				$rutaOrigen = $origen . DIRECTORY_SEPARATOR . $archivo;
				$rutaDestino = $destino . DIRECTORY_SEPARATOR . $archivo;

				// Si el archivo es una carpeta, llama recursivamente a la función
				if (is_dir($rutaOrigen)) {
					$this->copiarCarpeta($rutaOrigen, $rutaDestino); // Copiar subcarpetas
				} else {
					// Si es un archivo, cópialo
					copy($rutaOrigen, $rutaDestino);
				}
			}
		}
	}
}
