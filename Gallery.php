<?php

/* Class Gallery: archvio de imagen png
 * Autor: Raul Ramos
 * Fecha: 12/10/2023
 * CopyRight: Redfox
 * 
*/
namespace Fred;

class Image 
{
    private string $path;
    private string $name;
	private string $url;
	private string $version;

    public function __construct(string $filePath, string $url) 
    {
        $this->path = $filePath;
		
        // Extrae solo el nombre del archivo con su extensión
        $this->name = basename($filePath);
		$this->url = $url . "/" . $this->name;
		$this->version = file_exists($this->path) ? filemtime($this->path) : time();
    }

    public function __toString(): string 
    {
        // Limpiamos el nombre para mostrarlo de forma amigable (opcional)
        $displayName = pathinfo($this->name, PATHINFO_FILENAME);
		$displayName = $this->name;
		$v = $this->version;

        return "
        <div class='gallery-item'>
            <img src='{$this->url}?v={$v}' alt='{$displayName}' loading='lazy'>
            <div class='gallery-item-name'>{$displayName}</div>
        </div>
        ";
    }
}


class Gallery 
{
    private string $folderPath;
	private string $url;
    /** @var Image[] */
    private array $images = [];

    public function __construct(string $folderPath, string $url) 
    {
        // Normalizamos la ruta asegurando que termine en barra diagonal
        $this->folderPath = rtrim($folderPath, '/') . '/';
		$this->url = $url;
        $this->scanDirectory();
    }

    private function scanDirectory(): void 
    {
        // Si la carpeta no existe, salimos silenciosamente
        if (!is_dir($this->folderPath)) {
            return;
        }

        // Buscamos extensiones comunes de imágenes (puedes agregar más si lo necesitas)
        $extensions = '{*.jpg,*.jpeg,*.png,*.gif,*.webp}';
        $files = glob($this->folderPath . $extensions, GLOB_BRACE);

        if ($files) {
            foreach ($files as $file) {
                // Instanciamos la clase Image por cada archivo encontrado
                $this->images[] = new Image($file,$this->url);
            }
        }
    }

    public function __toString(): string 
    {
        if (empty($this->images)) {
            return "<div class='gallery-empty'>No se encontraron imágenes en esta carpeta.</div>";
        }

        $html = "<div class='pinterest-gallery'>";
        foreach ($this->images as $image) {
            // PHP invoca automáticamente el __toString() de la clase Image aquí
            $html .= $image;
        }
        $html .= "</div>";

        return $html;
    }
}
