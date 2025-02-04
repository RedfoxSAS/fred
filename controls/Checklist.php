<?php
/**
 * CheckList.php
 * @author		Raul E. Ramos Guzman
 * @copyright	15/04/2023
 * @version 	Release: 1.0
 * 
*/
namespace Fred;

include_once "Listbox.php";

class Checklist extends Listbox
{
	
	public $Multiple = false;
	public $Columns = 1;
	
	public function view($view, $key = false)
	{
		$tipo = ($this->Multiple)? "checkbox": "radio";
		$w = 100 / $this->Columns;
		$name = $this->Name;
		$name.= ($this->Multiple==true)? "[]" : "";
		$key = ($key===false)? $this->Model->setting()->Key : $key;
		$str = "<div class='check-label' style='width:$w%;display:inline-block;'>";
		$str.= "<input type='$tipo' value='{{$key}}' {Active} name='$name'> $view";
		$str.= "</div>";
		$this->View = $str;
		$this->Model->setting("Key", $key);
	}	
	
	public function control()
	{
		$this->loadItems();
		$val = (string) $this->text();
		$vals = explode(",",$val);
		foreach($vals as $val){
			if($this->FlagGroup==true){
				foreach($this->Items as $item){
					$item->active($val);
				}
			}else{
				if(isset($this->Items[$val])){
					$this->Items[$val]->Active = "checked";
				}
			}
		}
		if(!empty($this->Model)){
			$this->Model->view($this->View);
		}
		$col = $this->Columns;
		$atr = $this->attrib();
		$atr.= " placeholder='" . $this->Comment . "'";
		$nme = $this->Name;
		$str = "<div class='form-control2' list='List$nme' $atr style='border:1px solid #ccc; min-height:28px;display:block;border-radius:4px;padding:3px;'>";
		$str.= implode("",$this->Items);
		$str.= "</div>";
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
		$str = "<label $hid style='margin:0px;padding:2px'><div>$lbl";
		$str.= "$req</div> </label>$ctr ";
		$str.= "<small class='help-block text-$hli'>$hlp</small>";
		$str.= "";
		return $str;
	}	
	
}
