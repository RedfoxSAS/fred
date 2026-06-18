<?php

/* Form Cliente
 * 
 * Author: Raul Ramos
 * Date: 24/03/2023
 * 
 */

namespace Fred;

include_once "Form.php";

class FrmPanel extends Form
{
	
	private $DSFlag = false;
	private $DSDate = false;
	
	public function startComponents()
	{
		$this->authorize("AplicarFiltro",true);
		$this->authorize("BorrarFiltros",true);
	}
	
	public function filters(Model $model)
	{
		$controls = $this->expose();
		if(isset($model->setting()->Filters["Fecha"])){
			unset($model->setting()->Filters["Fecha"]);
		}
		foreach($controls as $control){
			if($control instanceof Control){
				if(isset($model->setting()->Filters[$control->Name])){
					unset($model->setting()->Filters[$control->Name]);
				}
			}
		}
		foreach($controls as $control){
			if($control instanceof Control){
				if($control->Source != false){				
					$signo = "LIKE";
					$campos = explode(",",$control->Source);
					
					$cdin = ($control->Comment===false)? "": "%";
					if($control instanceof Datebox){
						//$signo = (strpos($control->Name,"Inicio")>0)? "<=" : ">=" ;
						$cdin = "";
						if($this->DSFlag == false){
							$this->DSDate = $control->text();
							$this->DSFlag = true;
						}else{
							if($this->DSFlag == true){
								foreach($campos as $c){
									if( strlen((string)$control->text()) > 0) {
										if($this->DSDate!=$control->text()){
											$f = new ModelFilter($c, $this->DSDate, $control->text());
											$model->filter($f, "PanelFiltroFecha");
										}
									}
								}
								$this->DSFlag = false;
							}
						}
					}else{
						if($control->Source == "ALL"){
							$campos = $model->expose();
							
						}
						foreach($campos as $c){
							if( strlen((string)$control->Text) > 0 ) {
								
								$f = new ModelFilter($c, $cdin . $control->text() . $cdin , $signo);
								$model->filter($f, $control->Name);
								
							}
						}
					}
				}
			}
		}
		return $model;
	}
	

	protected function fillControls()
	{
		$controls = $this->expose();
		foreach($controls as $control){
			if($control instanceof Control){
				if(!empty($_POST) && $_POST["fred_form_name"] == $this->Name){
					if(isset($_POST[$control->Name])){
						if(!is_null($_POST[$control->Name])){
							$control->text($_POST[$control->Name]);
						}
					}
				}else{
					$name = "Fred_" . $this->Name . "_" . $control->Name;
					if(!empty($_SESSION[$name])){
						$_SESSION[$name];
						$control->text($_SESSION[$name]);
					}
				}
			}
		}
	}
	
	protected function AplicarFiltro()
	{
		parent::store();
	}
	
	protected function BorrarFiltros()
	{
		if(!empty($_POST)){
			$controls = $this->expose();
			foreach($controls as $control){
				if($control instanceof Control){
					$control->Text = $control->TextDefault;
					$_SESSION["Fred_" . $this->Name . "_" . $control->Name] = false;
				}
			}
		}
	}

	public function __toString()
	{
		$this->Style = "
		/* Estilo opcional para que el usuario sepa que el título es cliqueable */
		.tool-collapse {
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}
		.tool-collapse:after {
			content: '\f078'; /* Flecha hacia abajo de FontAwesome */
			font-family: 'Font Awesome 6 Free', 'FontAwesome';
			font-size: 14px;
		}
		#contenedorFiltros > h1 {display:none;}
		";
	
		$form = parent::__toString();
		$r = "
		<!-- Convertimos el H1 en el disparador del colapso -->
		<h1 class=\"tool-collapse\" 
			data-toggle=\"collapse\" 
			data-target=\"#panel-tools\" 
			aria-expanded=\"true\" 
			aria-controls=\"panel-tools\">
			Panel de Herramientas
		</h1>	

		<!-- Envolvemos el formulario en este nuevo div con la clase collapse y show (para que inicie abierto) -->
		<div class=\"collapse panel-tools\" id=\"panel-tools\">
			$form
		</div>
		";
		return $r;
	}
}

