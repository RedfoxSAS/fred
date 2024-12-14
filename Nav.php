<?php
/* Class Nav
 * 
 * Carga la navegacion de la pagina
 * 
 * Autor: Raul Ramos
 * Fecha: 8/4/2019
 * 
 * */
 
namespace Fred;

class Nav
{
	private $host;
	private $menu = array();
	private $user;
	
	public function __construct($host, $user=false)
	{
		$this->menu = $host;
		$this->user = $user;
	}
	
	public function __toString()
	{
		$ul = array();
		$keys = array_keys($this->menu);
		
		foreach($keys as $key){
			$nm = ucfirst($key);
			$li = "<li>";
			$li.= "<a href=\"/$key\">$nm</a>";
			$li.= "<ul id=\"$nm\" >";
			$hab = true;
			$i=0;
			
			foreach($this->menu[$key] as $item){
				
				if($this->user!=false){
					$hab = $this->user->authorize($item);
				}
				if($hab==true){
					$c = App::$Crud->get($item);
					$a = ($c!=false)? "<a href=\"".$c[1]."\"><i class=\"" . $c[2] . "\"></i>".$c[0]."</a>":"";
					$li = ($a!="")? $li."<li>$a</li>" : $li;
					$i++;
				}
			}
			$li.= "</ul>";
			$li.= "</li>";
			if($i>0){
				$ul[] = $li;
			}
		}
		
		$ul = implode("",$ul);
		$str = "
		<button class=\"b-nav-open\" x-on:click=\"open = true\">Abrir</button>
		<nav class=\"nav\" x-bind:class=\"open ? 'nav-show' : ''\">
		<ul>
			<div class=\"nav-button\" x-on:click=\"open = false\">
				<button class=\"b-nav-close\">Cerrar</button>
			</div>
			$ul
		</ul>
		</nav>
		";
		return $str;
	}
	
}
