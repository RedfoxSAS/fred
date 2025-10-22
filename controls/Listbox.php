<?php
/**
 * Listbox.php
 * @author		Raul E. Ramos Guzman
 * @copyright	15/04/2023
 * @version 	Release: 1.0
 * 
*/

namespace Fred;

include_once "Control.php";

class Listbox extends Control
{
	protected $View = false;
	
	public MotorDbi $Db;
	public $Items = array();
	public Model $Model;
	public $Editable = false;
	public $Select = false;
	protected $FlagGroup = false;
	public $ActiveNone = false;
	
	public function __construct($label=false,$t=1,$s=false,$c="")
	{
		parent::__construct($label,$t,$s,$c);
		$this->Model = new ListItem(0,"");
	}
	
	public function items($items,$view = false)
	{
		if($view!=false) {$this->view($view, "Id");}
		if($items instanceof ModelStatic){
			$item = $items->Items();
			$this->addItems($item,$view);
		}else if(is_array($items)){
			$this->Items = $items;
			$item = reset($items);
			if($item instanceof ListGroup){
				$this->FlagGroup=true;
			}
		}else if(is_string($items)){
			$this->view("{Text}", "Id");
			$lista = explode(",",$items);
			foreach($lista as $item){
				$this->addItem($item, $item);
			}
		}
	}
	
	public function addItems($array,$view=false)
	{
		if($view!=false){ $this->view($view, "Id");}
		$keys = array_keys($array);
		foreach($keys as $key){
			$this->addItem($key, $array[$key]);
		}
	}
	
	public function addItem($key, $value)
	{
		$this->Items[$key] = new ListItem($key, $value);
	}
			
	public function view($view, $key = false, $comment="", $dataset="")
	{
		$key = ($key===false)? $this->Model->setting()->Key : $key;
		if($this->Select==true){
			$this->View = "<option value='{{$key}}' {Active} >$view</option>";
		}else{
			$this->View = "<option value='$view' data-code='{{$key}}' $dataset {Active} >$comment</option>";
		}
		$this->Model->setting("Key", $key);
	}	
	
	public function control()
	{
		if(!$this->View){
			$this->view("({Id}) {Text}", "Id");
		}
		$this->loadItems();
		$val = (string) $this->text();
		$val = (empty($val))? $this->TextDefault : $val;
		if(isset($this->Items[$val])){
			$this->Items[$val]->Active = "selected";
		}
		if(!empty($this->Model)){
			$this->Model->view($this->View);
		}
		$atr = $this->attrib();
		$atr.= " placeholder='" . $this->Comment . "'";
		$atrv = ($this->Type==1)? " required ":"";
		$atrv.= ($this->Type==2)? " readonly ":"";
		$nme = $this->Name;
		$id = $this->Id;
		if($this->Select==false){
			$str = "<input type='hidden' value='$val' $atr>";
			$str.= "<input class='form-control' list='Items$id' value='$val' id='View$id' $atrv>";
			$str.= "<datalist id='Items$id'>";
			$str.= implode("",$this->Items);
			$str.= "</datalist>";
			$str.= $this->scriptEditable($id,$this->Editable);
			return $str;				
		}else{
			$str = "<select class='form-control' value='$val' $atr>";
			if($this->ActiveNone){
				$str.= "<option value='0'>NINGUNO</option>";
			}
			$str.= implode("",$this->Items);
			$str.= "</select>";
			return $str;
		}
	}
	
	protected function loadItems()
	{
		if(!empty($this->Model)){
			if(count($this->Items)==0 ){
				if(!($this->Model instanceof ListItem)){
					if($this->Db instanceof MotorDbi){
						$this->Items = $this->Db->query($this->Model);
					}
				}
			}
		}		
	}

	protected function scriptEditable($id,$edit)
	{
		$edit = $edit? 1:0;
		$r = "
		<script>
		let dataset$id = false;

		document.addEventListener(\"DOMContentLoaded\", function() {
			const input$id = document.getElementById(\"View$id\");
			const datalist$id = document.getElementById(\"Items$id\");
			const dataSend$id = document.getElementById(\"$id\");
			const edit$id = $edit;
			
			// Inicializar el input visible si ya hay un código cargado
			if (dataSend$id.value) {
				const option = [...datalist$id.options].find(opt => opt.dataset.code === dataSend$id.value);
				if (option) {
					input$id.value = option.value; // aquí va el código + nombre
				}
			}

			 // Manejar cambios en el input
			input$id.addEventListener(\"change\", function() {
				const value = input$id.value;
				const option = [...datalist$id.options].find(opt => opt.value === value);
				if (option) {
					// Mostramos la descripción debajo del input
					dataSend$id.value = option.dataset.code;
					dataset$id = option.dataset;
				} else {
					// Limpia si no coincide
					if(edit$id){
						dataSend$id.value = input$id.value;
					}else{
						dataSend$id.value = \"\";
						input$id.value = \"\";
						alert(\"Debe seleccionar un valor válido de la lista.\");
					}
				}
			});
		});
		</script>
		";
		return $r;
	}
}

class ListItem extends Model
{
	public string $Id = "";
	public string $Text = "";
	
	public function __construct($id, $text)
	{
		$this->Id = $id;
		$this->Text = $text;
		parent::__construct();
		$this->table("","Id");
	}
}

class ListGroup 
{
	public string $Title = "";
	public $Items = array();
	public $type = 1;
	public $Columns = 1;
	
	public function __construct($title,$type=1,$cols=1)
	{
		$this->Title = $title;
		$this->type = $type;
		$this->Columns = $cols;
	}
	
	public function add($item)
	{
		$this->Items[$item->Id] = $item;
	}
	
	public function active($val)
	{
		if(isset($this->Items[$val])){
			$this->Items[$val]->Active = "checked";
		}
	}
	
	public function __toString()
	{
		if($this->type == 1){
			$str = "<opgroup label='".$this->Title."'>";
			$str.= implode($this->Items);
			$str.= "</opgroup>";
		}else{
			$col = $this->Columns;
			$t = $this->Title;
			if(count($this->Items)>0){
				$str = "<div class='group-title'>$t</div><div class='check-group' style='column-count:$col;'>";
				$str.= implode($this->Items);
				$str.= "</div>";
			}else{
				$str = "<div class='group-group-title'>$t</div>";
			}
		}
		return $str;
	}
}
