<?php

/*
 * 
 * Number.php
 * 
 * @author		Raul E. Ramos Guzman
 * @copyright	April 14 of 2023 
 * @version 	Release: 1.0
 * 


namespace Fred;

include_once "Control.php";

class Numbox extends Control
{
	public $Max = 999999999999;
	public $Min = 0;
	public $Step = 1;
	public $Text = 0;
	public $M
	
	public function range($min,$max,$step=1)
	{
		$this->Min = $min;
		$this->Max = $max;
		$this->Step = $step;
		if(empty($this->Text)){
			$this->Text = $min;
		}
	}
	
	public function text($text=false)
	{
		if($text===false){
			return (int) $this->Text;
		}else{
			$this->Text = ($text < $this->Min)? $this->Min : $text;
			$this->Text = ($text > $this->Max)? $this->Max : $text;
		}
	}	
	
	public function control()
	{
		$val = $this->text();
		$atr = $this->attrib();
		$atr.= " min='" . $this->Min . "'";
		$atr.= " max='" . $this->Max . "'";
		$atr.= " step='" . $this->Step . "'";
		$str = "<input class='form-control'  type='number' value='$val' $atr>";			
		return $str;		
	}
}

*/


/*
 * 
 * Number.php
 * 
 * @author      Raul E. Ramos Guzman
 * @copyright   April 14 of 2023 
 * @version     Release: 1.1
 * 
*/

namespace Fred;

include_once "Control.php";

class Numbox extends Control
{
    public $Max = 999999999999;
    public $Min = 0;
    public $Step = 1;
    public $Text = 0;
    public $Currency = null; // <<< NUEVO atributo para definir tipo de moneda

    public function range($min, $max, $step=1)
    {
        $this->Min = $min;
        $this->Max = $max;
        $this->Step = $step;
        if (empty($this->Text)) {
            $this->Text = $min;
        }
    }

    public function text($text=false)
    {
        if ($text === false) {
            return (int) $this->Text;
        } else {
            $this->Text = ($text < $this->Min) ? $this->Min : $text;
            $this->Text = ($text > $this->Max) ? $this->Max : $text;
        }
    }

    public function control()
    {
        $val = $this->text();
        $atr = $this->attrib();
       

        // Si no hay moneda, usar input normal type=number
        if (empty($this->Currency)) {
			$atr.= " min='" . $this->Min . "'";
        	$atr.= " max='" . $this->Max . "'";
        	$atr.= " step='" . $this->Step . "'";
            $str = "<input class='form-control' type='number' value='$val' $atr>";
            return $str;
        }

        // Si hay moneda, usamos input text + hidden
        //$id = "numbox_" . uniqid(); 
        //$hiddenId = $id . "_hidden";
		$id = $this->Id;
		$viewId = "View$id";
        $atrv = ($this->Type==1)? " required ":"";
		$atrv.= ($this->Type==2)? " readonly ":"";
        $str  = "<input type='text' id='$viewId' class='form-control' placeholder='$ 0' $atrv>";
        $str .= "<input type='hidden' value='$val' $atr>";

        // Script JS inline para el formateo de moneda
        $str .= "
        <script>
        (function(){
            const input = document.getElementById('$viewId');
            const hidden = document.getElementById('$id');
            input.addEventListener('input', () => {
                let value = input.value.replace(/\\D/g, '');
                if (value) {
                    hidden.value = value;
                    input.value = parseInt(value, 10).toLocaleString('es-CO', {
                        style: 'currency',
                        currency: '{$this->Currency}',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                } else {
                    hidden.value = '';
                    input.value = '';
                }
            });
            // inicializar con valor actual
            if(hidden.value){
                input.value = parseInt(hidden.value, 10).toLocaleString('es-CO', {
                    style: 'currency',
                    currency: '{$this->Currency}',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
        })();
        </script>";

        return $str;
    }
}
