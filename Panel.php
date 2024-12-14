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
		if(isset($model->Setting->Filters["Fecha"])){
			unset($model->Setting->Filters["Fecha"]);
		}
		foreach($controls as $control){
			if($control instanceof Control){
				if(isset($model->Setting->Filters[$control->Name])){
					unset($model->Setting->Filters[$control->Name]);
				}
			}
		}
		foreach($controls as $control){
			if($control instanceof Control){
				if($control->Source != false){				
					$signo = "LIKE";
					$campos = explode(",",$control->Source);
					$cdin = "%";
					if($control instanceof Datebox){
						//$signo = (strpos($control->Name,"Inicio")>0)? "<=" : ">=" ;
						$cdin = "";
						if($this->DSFlag == false){
							$this->DSDate = $control->text();
							$this->DSFlag = true;
						}else{
							if($this->DSFlag == true){
								foreach($campos as $c){
									if( strlen($control->Text) > 0) {
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
							if( strlen($control->Text) > 0) {
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
				if(!empty($_POST[$this->Name])){
					if(isset($_POST[$control->Name])){
						if(!is_null($_POST[$control->Name])){
							$control->text($_POST[$control->Name]);
						}
					}
				}else{
					$name = "Fred." . $this->Name . "." . $control->Name;
					if(!empty($_SESSION[$name])){
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
					$text = "";
					$control->Text = "";
					$_SESSION["Fred." . $this->Name . "." . $control->Name] = $text;
				}
			}
		}
	}
}

