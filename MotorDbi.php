<?php

/* MotorDbi.php
 * 
 * Interface que debe cumplir los drivers para el manejo de datos
 * 
 * @author: Raul Ramos
 * @date: 16/10/2015
 * @updated: 2025
 * 
 * Spaniel SAS 2017 - Redfox 2023
 * Todos los derechos reservados
 */

namespace Fred;

interface MotorDbi
{
    // --- OPERACIONES CRUD CON MODELOS ---
    
    /**
     * Guarda los datos del objeto en la base de datos
     * @return mixed ID insertado en caso de nuevo registro, true/false para actualización
     */
    public function save(Model $model);

    /**
     * Carga un único registro y lo asigna al objeto
     * @return bool true si se encontró y cargó el registro
     */
    public function open(Model $model): bool;

    /**
     * Consulta múltiples registros según los filtros del modelo
     * @return array|false Arreglo de objetos o false en caso de error
     */
    
	public function query(Model $model): array;

    /**
     * Elimina el registro representado por el modelo
     * @return bool true si se eliminó correctamente
     */
    public function delete(Model $model): bool;

    // --- EJECUCIÓN DIRECTA DE SQL ---
    
    /**
     * Ejecuta sentencias de manipulación (INSERT, UPDATE, DELETE)
     * @param string $sql Sentencia SQL a ejecutar
     * @return bool true en éxito, false en error
     */
    public function runMdl(string $sql): bool;

    /**
     * Ejecuta consultas SELECT y devuelve resultados como arreglo asociativo
     * @param string $sql Consulta SQL
     * @return array|false Arreglo de resultados o false en error
     */
    public function runSql(string $sql): array;

    // --- METADATOS ---
    
    /**
     * Obtiene las columnas de una tabla con sus valores por defecto
     * @param string $tablename Nombre de la tabla
     * @return array Arreglo asociativo [campo => valor_por_defecto]
     */
    public function getColumns(string $tablename): array;

    // --- UTILIDADES ---
    
    /**
     * Obtiene la representación en cadena generada durante la última operación query()
     * Solo contiene datos si se activó setDataStringEnabled(true)
     * @return string Cadena generada o cadena vacía
     */
    public function getDataString(): string;

    /**
     * Prueba la conexión con la base de datos
     * @return bool true si la conexión es exitosa
     */
    public function test(): bool;

    // --- CONFIGURACIÓN DINÁMICA ---
    
    /**
     * Activa o desactiva los mensajes de operación (Modal::msg)
     */
    public function setMessagesEnabled(bool $enabled): void;

    /**
     * Activa o desactiva la generación automática de la cadena de datos en query()
     */
    public function setDataStringEnabled(bool $enabled): void;
}