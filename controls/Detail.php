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
	public $Currency = false;
	private $boton = 1;
	public $BtnAdd;
	public $ColWidth = false;

	public function __construct($w,$b=1)
	{
		parent::__construct();
		$this->width($w,$b);
		$name = $this->Name;
		$this->BtnAdd  = new Button("Agregar");
		$this->BtnAdd->Icon = "download";
		$this->BtnAdd->event("click","addDetail$name()");
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

		$grupos = "";
		if($this->ColWidth){
			$anchos = explode(",",$this->ColWidth);
			$grupos = "<colgroup>";
				for($i=0; $i<count($this->Controls); $i++){
					$ancho = (!empty( $anchos[$i]))? $anchos[$i]: " auto";
					$grupos .= "<col style=\"width: $ancho;\">  ";
				}
			$grupos .= "<col style=\"width: 20px;\">";
			$grupos .= "</colgroup>";
		}
		
		$w = 100/ count($this->Controls);
		$tab = "
		<table id='DetailTable$name' class='table table-detail' style='width:100%;'>
			$grupos
			<thead><thead>
			<tbody><tbody>
		</table>";
		$tab.= "<div class='table-detail-total' id='DetailTotal$name'></div>";
		$tab.= (empty($grupos))? "<style>.table-detail tr th{width:$w%}</style>": "";
		
		$this->add($this->BtnAdd,$this->boton);

		$c = $this->Wc;
		$t = $this->Wt;
		$ctr = parent::__toString();
		$str = "<div class='form-panel' style='display: flex; flex-wrap: wrap; align-items: flex-start;'>";
		$str.= "<div style='width:$c%;'>$ctr</div>";
		$str.= "<div style='width:$t%;'>$tab</div>";
		
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
		$name = $this->Id;
		$items = ($this->Text!=""&&$this->Text!="{}")? $this->Text : "[]";
		$moneda = ($this->Currency)? "'".$this->Currency."'" : 0;

		$txt = "
		<script language='javascript'>
			
			let flagTotal$name = $flagTotal;
			let vars$name = {};

			let items$name = $items;
			let views$name = $items;
			let suma$keyTotal = 0;
		";

		foreach ($this->Controls as $control) {
			$txt .= "vars" . $name . "['" . $control->Source . "'] = '" . $control->Id . "';\n";
		}

		$txt .= "

			function addDetail$name() {
				
				let item = {};
				let view = {};
				for (const [key, value] of Object.entries(vars$name)) {
					const vistaName = \"View\" + value;
					const element = document.getElementById(value);
					const vista = document.getElementById(vistaName);
					if (element) {
						item[key] = element.value;
						if(vista){
							view[key] = vista.value;
							vista.value = \"\";
						}else{
							view[key] = element.value;
						}
						element.value = \"\";
					}
				}
				items$name.push(item);
				views$name.push(view);
				updateTotal$name();
				fillTable$name();
				prepareToSend$name();
				document.dispatchEvent(new CustomEvent(\"itemAgregated\", { }));
			}

			function updateTotal$name() {
				total = 0;
				if(flagTotal$name!=false){
					items$name.forEach(item => {
						console.log(item);
						total += parseFloat(item.$keyTotal || 0);
					});
					suma$keyTotal = total;
					const campo = document.getElementById('DetailTotal$name');
					if (campo) {
						campo.valor = total;
						let formatoCO = formatoNumeroCO(total,$moneda);
						campo.innerHTML = '<b>Suma $keyTotal: </b>' + formatoCO;
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
				const table = document.getElementById('DetailTable$name');
				const thead = table.querySelector(\"thead\");
				const tbody = table.querySelector(\"tbody\");

				if (!Object.keys(vars$name).length || !items$name.length) {
					console.warn('No hay datos para llenar la tabla.');
					if (table) {
						tbody.innerHTML = \"\";
					} else {
						console.error('No se encontró la tabla con el ID DetailTable$name');
					}
					return;
				}

				let str = '';
				str += '<tr>';
					for (const [key, value] of Object.entries(vars$name)) {
						str += '<th>' + key + '</th>';
					}
					//str += '<th></th>';
				str += '</tr>';
				
				thead.innerHTML = str;

				var i = 0;
				str = \"\";
				views$name.forEach(item => {
					str += '<tr>';
					for (const [key, value] of Object.entries(vars$name)) {
						// Accede dinámicamente a las propiedades de 'item'
						str += '<td>' + (item[key] || '') + '</td>';
					}
					str += '<td><i class=\"fa fa-times\" onclick=\"delItem$name(' + i + ')\"></i></td>';
					str += '</tr>';
					i++;
				});
				
				tbody.innerHTML = str;

				// Verifica si la tabla existe y actualiza su contenido
				//const table = document.getElementById('DetailTable$name');
				if (table) {
					//table.innerHTML = str;
				} else {
					console.error('No se encontró la tabla con el ID DetailTable$name');
				}
			}

			function delItem$name(id)
			{
				items$name.splice(id, 1);
				views$name.splice(id, 1);
				prepareToSend$name();
				fillTable$name();
				updateTotal$name();

				document.dispatchEvent(new CustomEvent(\"itemDeleted\", { detail: { id } }));
			}

			function formatoNumeroCO(valor, useCurrency = false) {
				let opciones = {
					minimumFractionDigits: 0,
					maximumFractionDigits: 0
				};

				if (useCurrency) {
					opciones.style = 'currency';
					opciones.currency = useCurrency;
				}

				return new Intl.NumberFormat('es-CO', opciones).format(valor);
			}

			
			fillTable$name();
			updateTotal$name();
		</script>
		";

		return $txt;
	}

	
}
