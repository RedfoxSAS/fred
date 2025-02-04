<?php
/**
 * Detail.php
 * 
 * @author		Raul E. Ramos Guzman
 * @package		Form
 * @copyright	14/04/2023
 * @version 	Release: 1.0
 * 
*/

namespace Fred;

include_once "Panel.php";

class Detail extends Panel
{
	
	private $Wc = 100;
	private $Wt = 100;
	public $TotalKey = false;
	private $boton = 3;

	public function __construct($w,$b)
	{
		parent::__construct();
		$this->width($w,$b);
	}
	
	public function width($w,$b){
		if($w<100){
			$this->Wc = $w;
			$this->Wt = 100 - $w;
		}
		$this->boton = $b;
	}

	public function __toString()
	{
		$name = $this->Name;
		$id = $this->Id;
		$scr = $this->script();
		$add = new Button("Agregar");
		$add->Icon = "plus";
		$add->event("click","addDetail$name()");
		
		$tab = "<table id='DetailTable$name'></table>";
		$tab.= "<div class='DetailTotal' id='DetailTotal$name'></div>";
		$this->add($add,$this->boton);

		$c = $this->Wc;
		$t = $this->Wt;
		$ctr = parent::__toString();
		$str = "<div class='form-panel' style='display: flex; flex-wrap: wrap; align-items: flex-start;'>";
		$str.= "<div style='width:$c%;box-sizing: border-box;'>$ctr</div>";
		$str.= "<div style='width:$t%;box-sizing: border-box;padding-left:18px;'>$tab</div>";
		
		$text = $this->text();
		$str.= "<input type='hidden' value='$text' name='$name' id='$id'>";
		$str.= $scr;
		$str.= "</div>";

		return $str;
	
	}
	
	public function add(Control $control, $col = false, $row= false)
	{
		parent::add($control,$col,$row);
	}
	
	public function text($text=false)
	{
		if($text===false){
			return (string) $this->Text;
		}else{
			if($text instanceof ModelStatic){
				$text = $text->Id;
			}else if($text instanceof Model){
				$key = $text->setting()->Key;
				$text = $text->$key;
			}
			if(is_array($text)){
				$text = implode(",",$text);
			}
			if($this->Capital){
				$text = strtoupper($text);
			}
			$this->Text = $text;
		}
	}

	private function script()
	{
		$flagTotal = ($this->TotalKey)? "true": "false";
		$keyTotal =  ($this->TotalKey)? $this->TotalKey: "total";
		$name = $this->Name;
		$items = ($this->Text!=""&&$this->Text!="{}")? $this->Text : "[]";

		$txt = "
		<script language='javascript'>
			
			let flagTotal$name = $flagTotal;
			let vars$name = {};
			let items$name = $items;
		";

		foreach ($this->Controls as $control) {
			$txt .= "vars" . $name . "['" . $control->Source . "'] = '" . $control->Id . "';";
		}

		$txt .= "

			function addDetail$name() {
				
				let item = {};
				for (const [key, value] of Object.entries(vars$name)) {
					const element = document.getElementById(value);
					if (element) {
						item[key] = element.value;
					}
				}
				items$name.push(item);
				updateTotal$name();
				fillTable$name();
				prepareToSend$name();

			}

			function updateTotal$name() {
				total = 0;
				if(flagTotal$name!=false){
					items$name.forEach(item => {
						console.log(item);
						total += parseFloat(item.$keyTotal || 0);
					});
					const campo = document.getElementById('DetailTotal$name');
					if (campo) {
						campo.innerHTML = '<b>$keyTotal Total: </b>' + total.toFixed(2);
					}
				}
			}

			function prepareToSend$name() {
				const send = document.getElementById('$name');
				if (send) {
					send.value = JSON.stringify(items$name);
				}
			}

			function fillTable$name() {
				// Verifica si hay elementos en 'vars' y 'items'
				if (!Object.keys(vars$name).length || !items$name.length) {
					console.warn('No hay datos para llenar la tabla.');
					return;
				}

				let str = '';
				str += '<tr>';
					for (const [key, value] of Object.entries(vars$name)) {
						str += '<th>' + key + '</td>';
					}
				str += '</tr>';
				var i = 0;
				items$name.forEach(item => {
					str += '<tr>';
					for (const [key, value] of Object.entries(vars$name)) {
						// Accede dinámicamente a las propiedades de 'item'
						str += '<td>' + (item[key] || '') + '</td>';
					}
					str += '<td><i class=\"fa fa-times\" onclick=\"delItem$name('+ i +')\"></i>';
					str += '</tr>';
					i++;
				});

				// Verifica si la tabla existe y actualiza su contenido
				const table = document.getElementById('DetailTable$name');
				if (table) {
					table.innerHTML = str;
				} else {
					console.error('No se encontró la tabla con el ID DetailTable$name');
				}
			}

			function delItem$name(id)
			{
				items$name.splice(id, 1);	
				 prepareToSend$name();
				 fillTable$name();
				 updateTotal$name();
			}

			
			fillTable$name();
			 updateTotal$name();
		</script>
		";

		return $txt;
	}

	
}
