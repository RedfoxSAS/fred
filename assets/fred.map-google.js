
function mapLoad() {
	// Create a map object and specify the DOM element for display.
	map = new google.maps.Map(document.getElementById('map-body'), {
		center: { lat: 7.063198, lng: -73.851371 },
		scrollwheel: true,
		mapTypeId: "OSM",
		zoom: mapStatus.Zoom,
		disableDefaultUI: false,
		navigationControl: true,
		streetViewControl: true,
		keyboardShortcuts: true
	});

	mapStatus.Limits = new google.maps.LatLngBounds();

	map.mapTypes.set("OSM", new google.maps.ImageMapType({
		getTileUrl: function (coord, zoom) {
			var tilesPerGlobe = 1 << zoom;
			var x = coord.x % tilesPerGlobe;
			if (x < 0) {
				x = tilesPerGlobe + x;
			}
			return "http://tile.openstreetmap.org/" + zoom + "/" + x + "/" + coord.y + ".png";
		},
		tileSize: new google.maps.Size(256, 256),
		name: "OpenStreetMap",
		maxZoom: 25
	}));
	mapLoadShow();
}

function mapClean() {
	markers.forEach(marker => marker.setMap(null));
	markers.length = 0;
}

function mapAutoZoom() {
	map.fitBounds(mapStatus.Limits);
}

function mapSetCenter(lat, lon, zoom) {
	map.setCenter(new google.maps.LatLng(lat, lon));
	if (zoom != false) {
		map.setZoom(zoom);
	}
}

function mapAddMark(data) {
	var i = markers.length;
	markers[i] = new google.maps.Marker({
		position: new google.maps.LatLng(data.Latitude, data.Longitude),
		title: data.Title,
		map: map,
		icon: data.Icon,
		id: i
	});
	if (data.Info != false) {
		infowin[i] = new google.maps.InfoWindow({
			content: data.Info
		});
		markers[i].addListener('click', function () {
			infowin[this.id].open(map, markers[this.id]);
		});
	}
	mapStatus.Limits.extend(markers[i].position);
}

function markSetIcon(id, image) {
	markers[id].setIcon(image);
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
