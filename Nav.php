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
		<a
            class=\"navbar-toggler\"
            type=\"button\"
            data-toggle=\"collapse\"
            data-target=\"#navbarSupportedContent\"
            aria-controls=\"navbarSupportedContent\"
            aria-expanded=\"false\"
            aria-label=\"Toggle navigation\"
          >          
		  <span class=\"fa fa-bars\"></span>
        </a>
		<nav class=\"navbar navbar-expand-md collapse\" id=\"navbarSupportedContent\">
			<ul class=\"\">
				$ul
				<a href=\"/help\" class='nav-help'><i class=\"fa fa-circle-question\"></i> Ayuda</a>
			</ul>
		</nav>
		";
		return $str;
	}
	
}
