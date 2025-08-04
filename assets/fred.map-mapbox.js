
function mapLoad() {
	// Create a map object and specify the DOM element for display.
	mapboxgl.accessToken = 'pk.eyJ1IjoicmF1bHJhbW9zZ3UiLCJhIjoiY2pwN3VlYTdlMXQzejNxcnc2eHhsMmt4diJ9.B2xAvJGh5R40R8bw1fqrsw';
	map = new mapboxgl.Map({
		container: 'map-body',
		style: 'mapbox://styles/mapbox/streets-v10',
		center: [-73.851371, 7.063198],
		zoom: mapStatus.Zoom
	});
	map.addControl(new mapboxgl.NavigationControl());
	mapStatus.Limits = new mapboxgl.LngLatBounds();
	mapLoadShow();
}


function mapClean() {
	markers.forEach(marker => marker.remove());
	markers.length = 0;
}

function mapAutoZoom() {
	map.fitBounds(mapStatus.Limits);
}

function mapSetCenter(lat, lon, zoom) {
	map.flyTo({ center: [lon, lat] });
}

function mapAddMark(data) {
	var i = markers.length;
	var imagen = document.createElement("img");
	imagen.setAttribute("src", data.Icon);

	markers[i] = new mapboxgl.Marker({ element: imagen, offset: [0, -25] });
	markers[i].setLngLat(new mapboxgl.LngLat(data.Longitude, data.Latitude));
	markers[i].setDraggable(false);
	markers[i].addTo(map);

	var popup = new mapboxgl.Popup({ offset: 25 })
	popup.setHTML(data.Info);

	markers[i].setPopup(popup);
	mapStatus.Limits.extend(markers[i].getLngLat());

}

function markSetIcon(id, icon) {
	var imagen = markers[id].getElement();
	imagen.setAttribute("src", icon);
	markers[id].setOffset([0, 0]);
}


/*
const metersToPixelsAtMaxZoom = (meters, latitude) =>  meters/0.075/Math.cos(latitude * Math.PI/180) 
    
function mapAddCircle(lat,lon,radio,color="orange",border=1,opacidad=0.5)
{
	var i = circles;
	var id = "circle" + i;
	var col = "coleccion" + i;
	//alert(i);
	//map.on("load", function() {
	//setTimeout( function() {
	
		map.addSource(col, {
				"type": "geojson",
				"data": {
				 "type": "Point",
				 "coordinates": [lon,lat]
			}
		});
			
		map.addLayer({
			"id": id,
			"source": col,
			"type": "circle",
			"paint": {
				"circle-radius": { 
					stops: [ 
					[0, 0], 
					[20, metersToPixelsAtMaxZoom(radio, lat)] 
					], 
					base: 2 
				 } ,
				"circle-opacity": opacidad,
				"circle-color": color
			}
		});
	
	//},3000);
	//});

	limites.extend(new mapboxgl.LngLat(lon, lat));	
	circles++;
}
*/


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
