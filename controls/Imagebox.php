<?php

namespace Fred;

include_once "Control.php";

class Imagebox extends Control
{
	private $N = 0;
	private $W = 400;
	private $H = 300;
	
	public function __construct($label=false,$t=1,$s=false,$c="")
	{
		parent::__construct($label,$t,$s,$c);
		$this->N = Control::$Numero;
		$this->Name = "image_data" . $this->N;
		
	}
		
	public function size($w,$rx,$ry=false)
	{
		$this->W = $w;
		$this->H = ($ry==false)? $rx: round( ( $w * $ry) / $rx ) ;
	}

	
	public function control()
	{
		$atr = $this->attrib();
		$id = $this->N;
		$w = $this->W;
		$h = $this->H;
		$r = $w/$h;
		$txt = $this->text();

		$str = "<canvas ";
		$str.= "id='image_canvas$id'  ";
		$str.= "style='width:100%;cursor:pointer;border:1px solid silver;' ";
		$str.= "onmousedown='image_mover_iniciar(this,event)' ";
		$str.= "onmouseup='image_mover_parar()' ";
		$str.= "onmousemove='image_mover(this,$id,event)' ";
		$str.= "></canvas><table><tr><td>";
		$str.= "<button type='button' onclick='image_file_clic($id)' class='form-control' >";
		$str.= "<i class='fa fa-upload'></i>";
		$str.= "</button></td><td style='width:60%'>";
		$str.= "<input type='range' min='20' max='200' value='100' onchange='image_view($id)' id='image_scale$id' class='form-control'>";
		$str.= "</td><td ><button type='button' onclick='image_erase($id)' class='form-control' >";
		$str.= "<i class='fa fa-ban'></i>";
		$str.= "</button></td></tr></table>";
		$str.= "<div style='display:block'>";
		$str.= "<input accept='image/*' type='file' id='image_file$id' name='image_file$id' onchange='image_capture($id,event)' style='display:none'>";
		$str.= "<textarea name='image_data$id' id='image_data$id' style='display:none'>$txt</textarea>";
		$str.= "<script language='javascript'>";
		$str.= "image_maxw[$id] = $w;";
		$str.= "image_maxh[$id] = $h;";
		$str.= "image_ajustar_alto($id,$r);";
		$str.= "image_from_data($id);";
		$str.= "</script>";
		$str.= "</div>";
		//$str.= "<img src='".$this->prueba."'/ style='width:100%;'>";	
		return $str;
	}
	
	public function __toString()
	{
		$hlp = $this->Help;
		$hli = $this->Helpi;
		$lbl = $this->Label;
		$req = ($this->Type==1)? "<span class='control-required'>*</span>" : "";
		$hid = ($this->Type==3)? " style='display:none' ":"";
		$ctr = $this->control();
		$str = "<label$hid>";
		$str.= "<div>$lbl $req :</div>";
		$str.= "<small class='help-block text-$hli'>$hlp</small>";
		$str.= "</label>" . $ctr ; 
		return $str;
	}

	

}
