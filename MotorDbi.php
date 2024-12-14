<?php

/* MotorDbi.php
 * 
 * Interface que debe cumplir los driver para el manejo de datos
 * 
 * @author: Raul Ramos
 * @date: 16/10/2015
 * 
 * Spaniel SAS 2017
 * Todo los derechos reservados
 * 
*/

namespace Fred;

interface MotorDbi
{
	# guarda los datos del objeto pasado como parameros
	public function save(Model $modelo);

	#Carga los datos almacenados de una clase y los agrega al objeto seleccionado
	public function open(Model $modelo);

	#retorna un arreglo con los registros que coinciden con los filtros
	public function query(Model $model);
	
	/* Corre una funcion mdl y retorna la cantidad de registros afectados */
	public function runMdl($mdl);
	
	/* Corre una funcion sql y retorna un arreglo con los datos encontrados o false en caso de error */
	public function runSql($sql);
	
	/* Retorna un areglo con los nombre de una tabla en la base de datos */
	public function getColumns($tablename);
	
	
	/* Elimina el registro del objeto en la base de datos */
	public function delete(Model $model);

	
	/* retorna un string con los datos encontrados segun la plantilla del objeto*/
	public function getDataString();
	
	/* Permite probar la conexion con la base de datos */
	public function test();
}

