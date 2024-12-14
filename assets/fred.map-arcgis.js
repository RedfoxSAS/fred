var base = "streets-navigation-vector";
var view;
var graphicsLayer;
var mylat = 7.063198;
var mylng = -73.851371;

function mapLoad() 
{
	require([
		"esri/Map",
		"esri/views/MapView",
		"esri/geometry/Point",
		"esri/symbols/PictureMarkerSymbol",
		"esri/layers/GraphicsLayer",
		"esri/Graphic",
		"esri/PopupTemplate"
		], function(Map, MapView, Point, PictureMarkerSymbol, GraphicsLayer, Graphic, PopupTemplate) {

			// Crea un mapa
			map = new Map({
				basemap: base // puedes cambiar el basemap según tus necesidades
				// streets  satellite  hybrid  topo  dark-gray   osm   streets-navigation-vector
			});

			// Crea la vista del mapa
			view = new MapView({
				container: "map-body", // div donde se mostrará el mapa
				map: map,
				center: [mylng, mylat], // coordenadas de San Francisco
				zoom: 15 // nivel de zoom inicial
			});

			// Crea una capa de gráficos
			graphicsLayer = new GraphicsLayer();
			map.add(graphicsLayer);
			mapLoadShow();
	});
	
}

function mapClean(){
	graphicsLayer.removeAll();
    markers = [];	
}

function mapAutoZoom()
{
	require([
      "esri/geometry/Extent",
      "esri/geometry/SpatialReference"
    ], function(Extent, SpatialReference) {
      if (markers.length > 0) {
        var xmin = Infinity, ymin = Infinity, xmax = -Infinity, ymax = -Infinity;
         markers.forEach(function(marker) {
          var point = marker.geometry;
          if (point.longitude < xmin) xmin = point.longitude;
          if (point.latitude < ymin) ymin = point.latitude;
          if (point.longitude > xmax) xmax = point.longitude;
          if (point.latitude > ymax) ymax = point.latitude;
        });

        var extent = new Extent({
          xmin: xmin,
          ymin: ymin,
          xmax: xmax,
          ymax: ymax,
          spatialReference: { wkid: 4326 }
        });
        view.goTo(extent.expand(1)); // Expande la extensión para añadir un margen
      }
    });
}

function mapSetCenter(lat,lon, zoom)
{
     if(zoom!=false){
		 view.goTo({
			center: [lon, lat],
			zoom: zoom
		 });
	 }else{
		  view.goTo({
			center: [lon, lat]
		 });
	 }
	 mylat = lat;
	 mylng = lon;
}

function mapAddMark(data)
{
	require([
        "esri/geometry/Point",
        "esri/symbols/PictureMarkerSymbol",
        "esri/Graphic",
        "esri/PopupTemplate"
      ], function(Point, PictureMarkerSymbol, Graphic, PopupTemplate) {

        // Cargar la imagen para obtener sus dimensiones originales
        var img = new Image();
        img.onload = function() {
          var markerSymbol = new PictureMarkerSymbol({
            url: data.Icon,
            width: img.width + "px",
            height: img.height + "px",
            xoffset: 0, // Ajustar el desplazamiento en el eje X
            yoffset: 20 + "px" // Ajustar el desplazamiento en el eje Y (anclaje en la parte inferior)
         });

          var point = new Point({
            longitude: data.Longitude,
            latitude: data.Latitude
          });

          var popupTemplate = new PopupTemplate({
            title: "Estado",
            content: data.Info
          });

          var markerGraphic = new Graphic({
            geometry: point,
            symbol: markerSymbol,
            popupTemplate: popupTemplate
          });

          graphicsLayer.add(markerGraphic);
          markers.push(markerGraphic);
        };

        img.src = data.Icon; // Establece la URL de la imagen para cargarla
      });
}

function markSetIcon(id,icon)
{
	require([
        "esri/symbols/PictureMarkerSymbol"
      ], function(PictureMarkerSymbol) {
        if (id >= 0 && id < markers.length) {
          var markerGraphic = markers[id];

          // Cargar la nueva imagen para obtener sus dimensiones originales
          var img = new Image();
          img.onload = function() {
            var newMarkerSymbol = new PictureMarkerSymbol({
              url: icon,
              width: img.width + "px",
              height: img.height + "px",
				xoffset: 0, // Ajustar el desplazamiento en el eje X
				yoffset: 0  // Ajustar el desplazamiento en el eje Y (anclaje en la parte inferior)
            });

            // Actualizar el símbolo del marcador
            markerGraphic.symbol = newMarkerSymbol;

            // Refrescar la capa de gráficos para aplicar el cambio
            graphicsLayer.refresh();
          };

          img.src = icon; // Establece la URL de la nueva imagen para cargarla
        }
      });
}

// streets  satellite  hybrid  topo  dark-gray   osm   streets-navigation-vector
function arcSat() { base = "satellite"; mapLoad(); }
function arcStr() { base = "streets"; mapLoad(); }
function arcHyb() { base = "hybrid"; mapLoad(); }
function arcTop() { base = "topo"; mapLoad(); }
function arcDrk() { base = "dark-gray"; mapLoad(); }
function arcNav() { base = "streets-navigation-vector"; mapLoad(); }
	

