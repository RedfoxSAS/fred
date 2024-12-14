<?php
/* Auditoria.php
 * 
 * Clase utilizada para registrar la informacion de auditoria
 * @author: Raul Ramos
 * @date: 21/04/2023
 * 
*/
namespace Fred;

include_once "Model.php";

Class Auditoria extends Model
{
	public int $AuditoriaId = 0;
	public string $Accion = "";
	public string $Comentario = "";
	public string $Referencia = "";
		
	public function __construct()
	{
		parent::__construct();
		$this->table("admon_auditoria","AuditoriaId");
	}
}

