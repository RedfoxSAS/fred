
function mapLoad() {

	map = L.map('map-body', {
		center: [7.063198, -73.851371], // Coordenadas del centro
		zoom: mapStatus.Zoom, // Nivel de zoom inicial
		zoomControl: true, // Mostrar control de zoom
		minZoom: 7, // Nivel de zoom mínimo
		maxZoom: 25, // Nivel de zoom máximo
		scrollWheelZoom: true, // Habilitar zoom con rueda del ratón
		doubleClickZoom: false, // Deshabilitar zoom con doble clic
		dragging: true, // Habilitar arrastre del mapa
		boxZoom: true, // Habilitar zoom con caja de selección
		tap: false, // Deshabilitar soporte para eventos tap en dispositivos táctiles
		preferCanvas: false // Usar SVG en lugar de Canvas
	});

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
	}).addTo(map);
	mapLoadShow();
}

function mapClean() {
	markers.forEach(marker => marker.remove());
	markers.length = 0;
}

function mapAutoZoom() {
	if (markers.length === 0) {
		return;
	}
	var bounds = L.latLngBounds(markers.map(marker => marker.getLatLng()));
	map.fitBounds(bounds);
}

function mapSetCenter(lat, lon, zoom) {
	if (zoom != false) {
		map.setView([lat, lon], zoom);
	} else {
		map.setView([lat, lon]);
	}
}

function mapAddMark(data) {
	var i = markers.length;

	var icon = L.icon({
		iconUrl: data.Icon, // Reemplaza con la ruta de tu imagen
		iconAnchor: [42, 38], // Punto del icono que se ubicará en la coordenada del marcador
		popupAnchor: [44, 0] // Punto desde el cual se abrirá el popup relativo al icono
		//iconSize: [38, 38], // Tamaño del icono
	});

	markers[i] = L.marker([data.Latitude, data.Longitude], { icon: icon }).addTo(map)
		.bindPopup(data.Info);
	//.openPopup();	
}

function markSetIcon(id, image) {
	var icon = L.icon({
		iconUrl: image,
		iconAnchor: [0, 0], // Punto del icono que se ubicará en la coordenada del marcador
		popupAnchor: [0, 0]
	});
	markers[id].setIcon(icon);
}

/*
function mapAddCircle(lat,lon,radio,color="orange",border=1,opacidad=0.5)
{
	new google.maps.Circle({
		center: new google.maps.LatLng(lat, lon),
		fillColor: color,
		fillOpacity: opacidad ,
		map: map,
		radius: radio,
		strokeWeight: border
	});
	mapStatus.Limits.extend(new google.maps.LatLng(lat,lon));
}
*/



//revisados hasta aqui que funciona
/*
function mapGetAdress(lat,lon,obj,idx,callback)
{

	var ajax_url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" + lat + "," + lon + "&sensor=true";
	//alert(ajax_url);
	var ajax_request = new XMLHttpRequest();
	element = document.getElementById("direccion_" + obj + idx);
	element.innerHTML = "Solicitando datos a google..";
	ajax_request.onreadystatechange = function() {
		if (ajax_request.readyState == 4 ) {
			//element.innerHTML = "Datos recibidos";
			//alert(ajax_request.responseText);
			var datos = JSON.parse(ajax_request.responseText);
			datos.lat = lat;
			datos.lon = lon;
			datos.obj = obj;
			datos.idx = idx;
			callback(datos);
		}
	}
	ajax_request.open("GET", ajax_url, true );
	ajax_request.send();	
}

*/
