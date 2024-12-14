// FUNCIONES DE USO COMUN PARA OTRAS FUNCIONES   * * * * 
function _id(objetoId) {
   return document.getElementById(objetoId);
}

//muestra un elemento
function _show(nombre,inline)
{
	obj = _id(nombre);
	if(inline){
		obj.style.display = "inline-block";
	}else{
		obj.style.display = "block";
	}
}

//oculta un elemento
function _hide(nombre)
{
	obj = _id(nombre);
	obj.style.display = "none";
}

//codifica una cadena en forma utf8
function utf8_encode(str)
{
	try{
		return unescape(encodeURIComponent(str));
	}catch (e) {
		return str;
	}
}

//decodifca utf
function utf8_decode(utfstr)
{
	try {
		return decodeURIComponent(escape(utfstr));
	}catch (e) {
		return utfstr;
	}
}

function reload()
{
	location.reload(true);
}

// FUNCIONES PARA EL MANEJO DE IMAGENES   * * * * 

var image_maxw = [];
var image_maxh = [];	
var image_widt = [];
var image_high = [];
var image_posx = [];
var image_posy = [];
var image_file = [];

var URL = window.webkitURL || window.URL;

var xi ;
var yi ;
var isMousePressed = false;

// capturar la imagen desde un iput file
function image_capture(idx, event)
{
	image_file[idx] = event.target.files[0];
	var url = URL.createObjectURL(image_file[idx]);
	var img = new Image();
	img.src = url;
	img.onload = function() {
		image_posx[idx] = 0;
		image_posy[idx] = 0;	
		var ro = img.width / img.height;
		var rd = image_maxw[idx] / image_maxh[idx];
		
		if(ro > rd){
			image_widt[idx] = image_maxw[idx];
			image_high[idx] = image_maxw[idx] / ro;
			image_posy[idx] = Math.round((image_maxh[idx] - image_high[idx] ) / 2);	
		}else{
			image_high[idx] = image_maxh[idx];
			image_widt[idx] = image_maxh[idx] * ro;
			image_posx[idx] = Math.round((image_maxw[idx] - image_widt[idx]) / 2);
		}
		
		image_view(idx);
	}
}

//mostrar la imagen en un canvas
function image_view(idx)
{
	var name = "image_canvas" + idx;
	var canvas = document.getElementById(name);
	var ctx = canvas.getContext('2d');
	var url = URL.createObjectURL(image_file[idx]);
	var img = new Image();
	img.src = url;
	img.onload = function() {
		
		var scala = "image_scale" + [idx];
		var objscl = document.getElementById(scala);
		canvas.width  = image_maxw[idx];
		canvas.height = image_maxh[idx];
		var w = image_widt[idx] *  objscl.value / 100;
		var h = image_high[idx] *  objscl.value / 100;
		ctx.drawImage(img, image_posx[idx], image_posy[idx], w , h);
		
		var imgnam = "image_data" + idx;
		var imgtxt = document.getElementById(imgnam);
		imgtxt.value = canvas.toDataURL();
	}	
}

//inicia mover la imagen con el mouse sostenido
function image_mover_iniciar(canvas, event)
{
	isMousePressed = true;
	var ClientRect = canvas.getBoundingClientRect();
	xi = Math.round(event.clientX - ClientRect.left);
	yi = Math.round(event.clientY - ClientRect.top);
}

//mueve la imagen con el mouse sostenido
function image_mover(canvas, idx, event)
{
	if(!isMousePressed)
		return;
	var ClientRect = canvas.getBoundingClientRect();
	var xf = Math.round(event.clientX - ClientRect.left);
	var yf = Math.round(event.clientY - ClientRect.top);
	image_posx[idx] = image_posx[idx]+(xf-xi);
	image_posy[idx] = image_posy[idx]+(yf-yi);
	xi = xf;
	yi = yf;
	image_view(idx);
}

//detiene el movimiento de la imagen
function image_mover_parar()
{
	isMousePressed = false;
}

//muestra la imagen desde texto en base 64
function image_from_data(idx){
	try{
		var name = "image_canvas" + idx;
		var canvas = document.getElementById(name);
		var ctx = canvas.getContext('2d');
		var img = new Image();
		var imgnam = "image_data" + idx;
		var imgtxt = document.getElementById(imgnam);
		img.src = imgtxt.value;
		img.onload = function() {
			canvas.width  = image_maxw[idx];
			canvas.height = image_maxh[idx];
			ctx.drawImage(img, 0, 0, image_maxw[idx], image_maxh[idx]);
		}
	}catch(e){}
}

//borra la imagen en el canvas
function image_erase(idx)
{
	var name = "image_canvas" + idx;
	var canvas = document.getElementById(name);
	var ctx = canvas.getContext('2d');
	
	canvas.width = image_widt[idx];
	canvas.height = image_high[idx];
	
	ctx.fillStyle =  "#FFFFFF";
	ctx.rect(0, 0, image_widt[idx], image_high[idx]);
	ctx.fill();	
	
	var imgnam = "image_data" + idx;
	var imgtxt = document.getElementById(imgnam);
	imgtxt.value = "";
}

// hacer clic sobre el campo file
function image_file_clic(idx)
{
	var name = "image_file" + idx;
	document.getElementById(name).click();
}

//ajustar el alato del canvas
function image_ajustar_alto(idx, r)
{
	var name = "image_canvas" + idx;
	var canvas = document.getElementById(name);
	var ClientRect = canvas.getBoundingClientRect();
	canvas.style.height = (ClientRect.width / r) + "px";
}
// FUNCTIONER QUE SE SE EJECUTAN DESPUES DE CARGADA LA PAGINA

$(document).ready( function() {
	
	$(".panel-controls").appendTo("#ButtonsPanelOptions");
	$(".panel-controls > a").addClass("btn btn-link");
	
});


// FUNCIONES PARA EL MANEJO DE VENTANAS EMERGENTES
function modal_msg()
{
	$("#FredModalDialog").removeClass();
	$("#FredModalDialog").addClass("modal-dialog modal-dialog-centered");
	$("#FredModal").modal("show");
}

function modal_pdf()
{
	$("#FredModalDialog").removeClass();
	$("#FredModalDialog").addClass("modal-dialog modal-dialog-centered");
	$("#FredModalDialog").addClass("modal-dialog-scrollable modal-xl");
	$("#FredModal").modal("show");
}

function modal_close()
{
	$("#FredModal").modal("hide");
}

function modal_confirm(title,msg,yes,no)
{
	var htm = msg;
	htm+= "<div style='text-align:center;margin-top:32px;'>" ;
	htm+= "<button class='btn btn-primary' onclick=\"" + yes + ";\">Aceptar</button> " ;
	htm+= "<button class='btn btn-secondary' onclick=\""+no+";modal_close();\">Cerrar</button>";
	htm+= "</div>";
	$("#FredModalTitle").html(title);
	$("#FredModalBody").html(htm);
	modal_msg();
}

function modal_print(uri)
{
	var btn = "<button type='button' id='btnImprimir' class='btn'>";
	btn+= "<i class='fa fa-print'></i> Imprimir</button>";
	var htm = "";
	htm+= "<iframe src='" + uri + "' class='data-sheet-print' id='FredFrame'></iframe>";

	$("#FredModalTitle").html(btn);
	$("#FredModalBody").html(htm);
	
	$('#btnImprimir').click(function(){
          //Hacemos foco en el iframe
		$('#FredFrame').get(0).contentWindow.focus(); 
		  //Ejecutamos la impresion sobre ese control
		$("#FredFrame").get(0).contentWindow.print(); 
    });		
	
	modal_pdf();
}

function back(uri=false)
{
	if(uri == false) { uri = uriBack;}
	window.location.href = uri;
}


//***** FUNCIONES PARA IMPRIMIR EL CUERPO DEL DOCUMENTO  *******/
function printApp() 
{
	// Obtener el contenido del div específico
	var divToPrint = document.getElementById("appbody").innerHTML;

	// Crear una nueva ventana de impresión
	var newWin = window.open('', 'Print-Window');

	// Escribir el contenido en la nueva ventana
	newWin.document.open();
	newWin.document.write('<html><head><title>Imprimir</title>');
	newWin.document.write('<link rel="stylesheet" type="text/css" href="style.css">'); // Opcional: Incluir estilos CSS
	newWin.document.write('</head><body>');
	newWin.document.write(divToPrint);
	newWin.document.write('</body></html>');
	newWin.document.close();

	// Esperar a que el contenido se cargue y ejecutar el comando de impresión
	setTimeout(function() {
		newWin.print();
		newWin.close();
	}, 1000);
}







// **** FIN FUNCIONES REVISADAS   ********************************




function rWindowShow()
{
	$("#rWindow").modal("show");
}


function rWindowLoad(url,callback)
{
	$("#rWindow").off();
	url+= "&rWindow=1";
	var htm = "<iframe class='modal-frame' src='" + url + "' ></iframe>";
	_id("rWindowBody").innerHTML = htm;
	$("#rWindow").on("hidden.bs.modal", function (e) {
		callback();
	});
}


var rFieldReturn = false;
var rValueReturn = false;
function rWindowReturn(id)
{
	if(rFieldReturn!=false){
		_id(rFieldReturn).value = id;
	}
	rValueReturn = id;
	rWindowClose();
}

function rServerLoad(url,callback)
{
	var ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() {
		if (ajax.readyState == 4 ) {
			var data;
			try{
				data = JSON.parse(ajax.responseText);
			}catch(e){
				data = ajax.responseText
			}
			callback(data);
		}
	}
	ajax.open( "GET", url, true );
	ajax.send();	
}

function message(data)
{
	alert(data.message);
	if(data.reload==true){
		reload();
	}
}

var menu_status;

function menu(menu)
{
	try{ clearTimeout(menu_status);}catch (e){}
	var menus = document.getElementsByClassName("menu-down");
	for(let obj of menus){
		obj.style.display = "none";
		if(obj.dataset.visible=="false" && obj.dataset.name==menu){
			obj.style.display = "block";
			obj.dataset.visible="true"
		}else{
			obj.dataset.visible="false"
		}
	}
	menu_status = setTimeout("menu()",10000);
}

function toggle(menu)
{
	var obj = _id(menu);
	if(obj.dataset.visible=="false"){
		obj.style.display = "block";
		obj.dataset.visible="true"
	}else{
		obj.style.display = "none";
		obj.dataset.visible="false"
	}
}

function getDateDiff(fecha)
{
	var date = new Date(fecha);
	var dateMsec = date.getTime();
	date = new Date();
	interval = date.getTime() - dateMsec;
	return interval;
}

function view_document(name)
{
	var url = "?view_document=" + name;
	rWindowLoad(url);
	rWindowShow();
}

function load_image(name="")
{
	var url = "?load_image=" + name;
	rWindowLoad(url,reload);
	rWindowShow();
}

function delete_image(name="")
{
	var seguro = confirm("Seguro que desea eliminar esta imagen?");
	if(seguro==true){
		var url = "?delete_image=" + name;
		rServerLoad(url,message);
	}
}

function select_ciudades(depa)
{
	var url = "clientes.php?select_ciudades=" + depa;
	rServerLoad(url,mostrar_select_ciudades);
}

function mostrar_select_ciudades(data)
{
	_id("Ciudad").innerHTML = data;
}
