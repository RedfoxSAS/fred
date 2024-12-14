<?php
/**
 * rWindow.php
 * 
 * @author		Raul E. Ramos Guzman
 * @copyright	2019 rframe
 * @version 	Release: 1.1
*/

namespace Fred;

class Modal
{

	public $Title;
	public $Icon;
	public $Type = "primary";
	public $Size = "";
	public $Body = "Aqui va el contenido";
	
	public $scroll = true;
	public $close;
	
	public static function msg($msg, $type=0)
	{
		$_SESSION["FredMsg"][] = new Msg($msg, $type);		
	}
	
	public function __toString()
	{
		$body = "";
		$titl = "";
		$scrp = "";
		if(!empty($_SESSION["FredMsg"])){
			$body = implode("",$_SESSION["FredMsg"]);
			$titl = "Atencion";
			$_SESSION["FredMsg"] = array();
			$scrp = "<script language='javascript'>modal_msg();</script>";
		}

		$str = "
		<!-- Modal -->
		<div class='modal fade' id='FredModal' tabindex='-1' aria-hidden='true'>
		  <div class='modal-dialog' id='FredModalDialog'>
			<div class='modal-content'>
			  <div class='modal-header'>
				<h5 class='modal-title' id='FredModalTitle'>$titl</h5>
				<button type='button' class='btn btn-light' aria-label='Close' onclick='modal_close();'>
				<i class='fa fa-times'></i>
				</button>
			  </div>
			  <div class='modal-body' id='FredModalBody'>
				$body
			  </div>
			  <!-- 
			  <div class='modal-footer'>
				<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cerrar</button>
				<button type='button' class='btn btn-primary'>Guardar cambios</button>
			  </div>
			  -->
			</div>
		  </div>
		</div>
		<!-- Fin Modal -->
		$scrp
		
		";
		return $str;		
	}
}

class Msg
{
	private $Body;
	private $Type;
	
	public function __construct($b, $t)
	{
		$this->Body = $b;
		$this->Type = $t;
	}
	
	public function __toString()
	{
		$msg = $this->Body;
		$typ = $this->getClass($this->Type);
		return "
		<div class='alert alert-$typ'>
			$msg
		</div>
		";		
	}
	
	public function getClass($type)
	{
		$tipos = array("primary","secondary","success","danger","warnign","info","light","dark","link");
		if(isset($tipos[$type])){
			return $tipos[$type];
		}
		return "light";
	}
}
	

