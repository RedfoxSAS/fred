<?php
/* Class FileBinary: manejo de archivos binarios (PDF, DOC, etc.)
 * Autor: Raul Ramos
 * Fecha: 17/07/2025
 * CopyRight: Redfox
 */
namespace Fred;

include_once "App.php";
include_once "ModelFile.php";

class FileBinary extends ModelFile
{
	protected $UploadedPath = "";
	protected $DownloadUrl = "";
    protected $IsUploaded = false;

	/**
	 * setText(): Aquí recibimos la ruta temporal del archivo subido (no el contenido)
	 */
	public function setText($sourcePath)
    {
        if (!empty($sourcePath) && file_exists($sourcePath)) {
            $this->UploadedPath = $sourcePath;

            // Si el archivo es una subida temporal de PHP
            if (is_uploaded_file($sourcePath)) {
                $this->IsUploaded = true;
            } else {
                $this->IsUploaded = false; // Ruta interna conocida, no es temporal
            }
        }
        $this->Text = $this->File;
       
    }


	/**
	 * save(): Mueve el archivo subido al destino definitivo
	 */
	public function save()
	{
		if (!empty($this->UploadedPath) && is_uploaded_file($this->UploadedPath)) {
			move_uploaded_file($this->UploadedPath, $this->File);
		}
	}

	/**
	 * open(): No abre como texto, pero prepara la URL pública para descarga
	 */
	public function open()
	{
		if (file_exists($this->File)) {
			// Crea la URL pública para exponer el archivo (solo si se desea usar __toString)
			$host = App::$Setting->Host;
            $user = App::$UserActive->Login;
            $extension = pathinfo($this->File, PATHINFO_EXTENSION);
            
            $url = "/$host/tmp/out.$user.$extension" ;
            $destino = "d:/xampp/htdocs". $url;
            
			copy($this->File, $destino );
            //$base = str_replace("d:/xampp/htdocs", "", $this->File); // ajusta si usas otra ruta base
			$this->DownloadUrl = $url;
		}
	}

	/**
	 * __toString(): Devuelve un enlace HTML para descargar el archivo
	 */
	public function __toString()
	{
		if (!empty($this->DownloadUrl)) {
			return $this->DownloadUrl;
		}
		return "Archivo no disponible";
	}
}
