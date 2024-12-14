///FUNCIONES BASICAS DE MAPA ---- REQUIERE UN MOTOR DE MAPA PARA FUNCIONAR

var map;
var markers = [];
var infowin = [];

var mapStatus = {
	Pause: true,
	Point: 0,
	Frecuency : 200,
	Player : false,
	Limits : false,
	Center: false,
	Zoom: false,
	Height: 0
};

function mapLoadShow()
{
	if(autoShow==true){
		mapShowAll();
	}else{
		mapShow(0);	
	}		
}

function infoString(data)
{
	var str = "<div class='infowindow-content'>";
	str+= "<b>" + data.Title + "</b><br>";
	str+= data.Street + "<br>";
	str+= data.Latitude + "," + data.Longitude + " (";
	str+= data.Altitude + " msnm ) (";
	str+= data.Head + " Grados )<br>";
	str+= "Velocidad: " + data.Velocity + " km/h<br>";
	str+= "Fecha y hora: " + data.Datetime ;
	str+= "</div>";
	return str;
}

function mapPlay()
{
	mapStatus.Player = true;
	var btnPlay = document.getElementById('btnPlay');
	if(mapStatus.Pause==true){
		mapStatus.Pause = false;
		mapShow(mapStatus.Point,true);
		btnPlay.innerHTML = '<i class=\"fa fa-pause\"></i>';
	}else{
		mapStatus.Pause = true;
		btnPlay.innerHTML = '<i class=\"fa fa-play\"></i>';	
	}
}

function mapStop()
{
	mapStatus.Point = 0;
	mapClean();
	mapShow(mapStatus.Point);
	var btnPlay = document.getElementById('btnPlay');
	btnPlay.innerHTML = '<i class=\"fa fa-play\"></i>';		
	mapStatus.Pause = true;
}

function mapVel(obj)
{
	mapStatus.Frecuency = 1000 / obj.value;
}

function mapComment(text)
{
	var mapcom = document.getElementById('map-comment');
	mapcom.innerHTML = text;	
}

function mapShowPoint(lat,lon,icon,title)
{
	var data = {
		Latitude:lat,
		Longitude: lon,
		Icon: icon,
		Info: false,
		Title: title
	}
	mapAddMark(data);
	mapSetCenter(lat,lon);
}

function mapShow(point,next=false)
{
	if(point < points.length){
		
		points[point].Info = infoString(points[point]);
		//cambia el icono del punto anterior en modo reproduccion
		if( (point > 0) && mapStatus.Player==true){
			var icon = points[point-1].Icon
			markSetIcon((point-1),icon);
		}
		//agregar un nuevo punto al mapa
		var mark = Object.assign({}, points[point]);
		if(icono!=undefined){
			mark.Icon = icono;
		}
		mapAddMark(mark);
		
		//centrar el mapa en el nuevo punto	
		mapSetCenter(points[point].Latitude,points[point].Longitude,false);

		//mostrar avance en el comentario
		avance = parseInt((point+1) / points.length * 100 );
		mapComment(avance + " % : " + points[point].Title);
		
		//programar el nuevo punto
		mapStatus.Point = point+1;
		if(mapStatus.Pause == false && next==true){
			setTimeout("mapShow("+mapStatus.Point+",true)",mapStatus.Frecuency);
		}
	}else{
		mapAutoZoom();
	}
}


function mapShowAll()
{
	var cant = points.length;
	mapClean();
	if(cant>0){
		for(i=0;i<cant;i++){
			points[i].Info = infoString(points[i]);
			mapAddMark(points[i]);
		}
	}
	setTimeout("mapAutoZoom()",1500);
}


function mapSearch(ctr)
{
	var val = ctr.value;
	
	if(val=="ALL"){
		mapAutoZoom();
	}else{
		var cant = points.length;
		if(cant>0){
			for(i=0;i<cant;i++){
				if(val==points[i].Id){
					mapSetCenter(points[i].Latitude,points[i].Longitude,15);
					return;
				}
			}
		}
	}
}


var isFullScreen = false;
function mapFullScreen() {
	var mapContainer = document.getElementById('map-container');
	var expandButton = document.getElementById('btnExpand');
	if (!isFullScreen) {
		if (mapContainer.requestFullscreen) {
			mapContainer.requestFullscreen();
		} else if (mapContainer.mozRequestFullScreen) { // Firefox
			mapContainer.mozRequestFullScreen();
		} else if (mapContainer.webkitRequestFullscreen) { // Chrome, Safari y Opera
			mapContainer.webkitRequestFullscreen();
		} else if (mapContainer.msRequestFullscreen) { // IE/Edge
			mapContainer.msRequestFullscreen();
		}
		isFullScreen = true;
		expandButton.innerHTML = '<i class=\"fa fa-compress\"></i>';
	} else {
		if (document.exitFullscreen) {
			document.exitFullscreen();
		} else if (document.mozCancelFullScreen) { // Firefox
			document.mozCancelFullScreen();
		} else if (document.webkitExitFullscreen) { // Chrome, Safari y Opera
			document.webkitExitFullscreen();
		} else if (document.msExitFullscreen) { // IE/Edge
			document.msExitFullscreen();
		}
		isFullScreen = false;
		expandButton.innerHTML = '<i class=\"fa fa-expand\"></i>';
		adjustDivHeight();
	}
}

function mapResize() {
	if(mapStatus.Height==0){
		var child = document.getElementById('map-container');
		var parent = child.parentElement;
		var parentHeight = parent.offsetHeight;
		var windowHeight = window.innerHeight;
		var childOffsetTop = child.offsetTop;
		if(parent.id != "appbody"){
			mapStatus.Height = parentHeight;
		}else{
			mapStatus.Height = windowHeight - childOffsetTop - 20;
		}
		child.style.height = mapStatus.Height + 'px';
	}
	child.style.height = mapStatus.Height + 'px';
}
