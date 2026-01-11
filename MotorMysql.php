<?php

/* MotorMySql.php
 * @Autor		Raul Ramos Guzman
 * @Fecha		1/01/2023
 * @Copyright 	Redfox 2023
 * 
 * Motor para acceder a una base de datos MySql
 * Implementa la interfaz MotorDbi.php
 */

namespace Fred;

include_once "Model.php";
include_once "Modal.php";
include_once "MotorDbi.php";

class MotorMySql implements MotorDbi
{
    private ?\mysqli $conn = null;
    private string $database = "";
    private string $server = "";
    private string $user = "";
    private string $password = "";

    private bool $enableMessages = true;   // Controla si se muestran mensajes
    private bool $enableDataString = false; // Controla si se genera el string en query()
    
    private string $lastSql = "";
    private int $lastAffectedRows = 0;
    private int $lastInsertId = 0;
    private int $recordCount = 0;
    private string $dataString = "";
    private string $lastMsg = "";

    private static array $columnCache = [];

    public function __construct($base = false, $serv = false, $user = false, $pass = false)
    {
        if ($base instanceof MotorMySql) {
        $this->cloneFrom($base);
        if ($serv) {
            $this->database = $serv;
        }
		} elseif (is_string($base)) {
			$this->setCredentials($base, $serv ?: "", $user ?: "", $pass ?: "");
		}
    }

	public function setCredentials(string $database, string $server = "", string $user = "", string $password = ""): void
	{
		$this->database = $database;
		$this->server = $server;
		$this->user = $user;
		$this->password = $password;
	}

	public function setDatabase($base)
	{
		$this->database = $base;
	}

    // --- Métodos de configuración pública ---
    
    public function setMessagesEnabled(bool $enabled): void
    {
        $this->enableMessages = $enabled;
    }

    public function setDataStringEnabled(bool $enabled): void
    {
        $this->enableDataString = $enabled;
    }

    public function areMessagesEnabled(): bool
    {
        return $this->enableMessages;
    }

    public function isDataStringEnabled(): bool
    {
        return $this->enableDataString;
    }

    // --- Conexión ---

    private function connect(): bool
    {
        if ($this->conn && $this->conn->ping()) {
            return true;
        }
		
        $this->conn = new \mysqli($this->server, $this->user, $this->password, $this->database);

        if ($this->conn->connect_error) {
            error_log("Error de conexión a MySQL: " . $this->conn->connect_error);
            return false;
        }

        if (!$this->conn->set_charset("utf8mb4")) {
            error_log("Error al establecer charset utf8mb4: " . $this->conn->error);
            return false;
        }

        return true;
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function cloneFrom(MotorMySql $db): void
    {
        $this->database = $db->database;
        $this->user = $db->user;
        $this->password = $db->password;
        $this->server = $db->server;
        // Copiar también la configuración
        $this->enableMessages = $db->enableMessages;
        $this->enableDataString = $db->enableDataString;
    }

    // --- Implementación de MotorDbi ---

    public function save(Model $model)
    {
        $ms = $model->className();

        if ($model->setting()->Exists) {
            $sql = $this->createMdlUpdate($model);

            $r = $this->runMdl($sql);
            $e = empty($model->Estado) ? "" : ", registro " . $model->Estado;
            if ($r !== false && $this->lastAffectedRows > 0) {
                $this->msg("($this->lastAffectedRows) $ms actualizado exitosamente$e.", 0);
                $model->saveFiles();
                return $r;
            } else if($this->lastAffectedRows==0){
                $this->msg("$ms sin cambios", 1);
                $model->saveFiles();
                return true;
            } else {
                $this->msg("$ms no se pudo modificar", 3);
                return false;
            }
        } else {
            $sql = $this->createMdlInsert($model);
           
            $r = $this->runMdl($sql);
            $e = empty($model->Estado) ? "" : ", registro " . $model->Estado;
            $key = $model->setting()->Key;
            $val = $model->value($key);

            if ($r !== false) {
                if ($this->lastInsertId > (int)$val) {
                    $model->$key = $this->lastInsertId;
                }
                $model->setting("Exists", true);
                $this->msg("Nuevo registro $ms creado exitosamente$e.", 0);
                $model->saveFiles();
                if($this->lastInsertId > 0){
                    return $this->lastInsertId;
                }
                return true;
            } else {
                $this->msg("Error al crear el registro $ms, Verifique los datos.", 3);
                return false;
            }
        }
    }

    public function open(Model $model): bool
    {
        if (!$this->connect()) {
            return false;
        }

        $sql = $this->createSqlOpen($model);
        $this->lastSql = $sql;

        $result = $this->conn->query($sql);
        if (!$result) {
            error_log("Error en open(): " . $this->conn->error . " | SQL: $sql");
            return false;
        }

        $row = $result->fetch_object();
        if ($row) {
            $model->Id = 1;
            $model->set($row);
            $model->openFiles();
            $method = $model->setting()->Method;
            if ($method && method_exists($model, $method)) {
                $model->$method();
            }
            return true;
        }

        return false;
    }

    public function query(Model $model): array
    {
        if (!$this->connect()) {
            return false;
        }

        $sql = $model->setting()->Sql;
        if (empty($sql)) {
            $sql = $this->createSqlQuery($model);
			
            if ($model->setting()->Group) {
                $sql .= " GROUP BY " . $model->setting()->Group;
            }
            if (!empty($model->setting()->Order)) {
                $sql .= " ORDER BY " . implode(",", $model->setting()->Order);
            }
            if ($model->setting()->Limit > 0) {
                $sql .= " LIMIT " . $model->setting()->Limit;
            }
        }
		
        $this->lastSql = $sql;
        $result = $this->conn->query($sql);
        if (!$result) {
            //error_log("Error en query(): " . $this->conn->error . " | SQL: $sql");
            return [];
        }

        $key = $model->setting()->Key;
        $class = get_class($model);
        $lista = [];
        $summa = $model->setting()->Summary ? explode(",", $model->setting()->Summary) : false;
        $metodo = $model->setting()->Method;

        $sumar = null;
        if ($summa) {
            $sumar = new $class();
            foreach ($summa as $s) {
                $sumar->$s = 0;
            }
        }

        $i = 0;
        $this->dataString = ""; // Reiniciar siempre
        while ($row = $result->fetch_object()) {
            $i++;
            $nuevo = new $class();
            $nuevo->Id = $i;
            $nuevo->set($row);
            if ($summa) {
                foreach ($summa as $s) {
                    $sumar->$s += (float)($nuevo->$s ?? 0);
                }
            }
            if ($metodo && method_exists($nuevo, $metodo)) {
                $nuevo->$metodo();
            }
            //$nuevo->seek();
            $lista[(string)($nuevo->$key ?? $i)] = $nuevo;
            
            // Solo generar string si está habilitado
            if ($this->enableDataString) {
                $this->dataString .= (string)$nuevo;
            }
        }

        if ($summa) {
            $sumar->setting()->Summary = true;
            if ($metodo && method_exists($sumar, $metodo)) {
                $sumar->$metodo();
            }
            $lista["summary"] = $sumar;
            if ($this->enableDataString) {
                $this->dataString .= (string)$sumar;
            }
        }
		$this->recordCount = $i;
		return $lista;

    }

    public function runMdl($mdl): bool
    {
        if (!is_string($mdl)) {
            error_log("runMdl espera un string SQL");
            return false;
        }

        if (!$this->connect()) {
            return false;
        }

        $this->lastSql = $mdl;
        $result = $this->conn->query($mdl);

        if ($result === false) {
            error_log("Error en runMdl: " . $this->conn->error . " | SQL: $mdl");
            return false;
        }
        $this->lastInsertId = $this->conn->insert_id;
        $this->lastAffectedRows = $this->conn->affected_rows;

        return $this->lastInsertId ?: $this->lastAffectedRows;
    }

    public function runSql($sql): array
    {
        if (!is_string($sql)) {
            return false;
        }

        if (!$this->connect()) {
            return false;
        }

        $this->lastSql = $sql;
        $result = $this->conn->query($sql);

        if (!$result) {
            error_log("Error en runSql: " . $this->conn->error . " | SQL: $sql");
            return false;
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    public function getColumns($tablename): array
    {
        if (!is_string($tablename)) {
            return [];
        }

        $cacheKey = $this->database . '.' . $tablename;
        if (isset(self::$columnCache[$cacheKey])) {
            return self::$columnCache[$cacheKey];
        }

        if (!$this->connect()) {
            return [];
        }

        $safeTable = "`" . str_replace("`", "``", $tablename) . "`";
        $result = $this->conn->query("SHOW COLUMNS FROM $safeTable");
        if (!$result) {
            return [];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $field = $row['Field'];
            $type = $row['Type'];
            $default = $row['Default'] ?? null;
            $extra = $row['Extra'];
            $value = $default ?? '';
            $isNull  = $row['Null'] === 'YES';

            if (preg_match('/int/i', $type)) {
                $value = '0';
            } elseif ($type === 'date') {
                $value = date('Y-m-d');
            } elseif (in_array($type, ['double', 'float', 'decimal'])) {
                $value = '0';
            } elseif ($type === 'time') {
                $value = '00:00:00';
            } elseif ($type === 'longtext' || $type === 'json') {
                $value = '{}';
            } else {
                $value = '';
            }

            if (($isNull && $default === null) || $extra === 'auto_increment') {
                $value = null;
            }

            $data[$field] = $value ;
        }
        self::$columnCache[$cacheKey] = $data;
        return $data;
    }

    public function delete(Model $model): bool
    {
        $sql = $this->createMdlDelete($model);
        $r = $this->runMdl($sql);
        if ($r !== false && $this->lastAffectedRows > 0) {
            if ($this->enableMessages) {
                $ms = $model->className();
                $this->msg("$ms eliminado correctamente", 0);
            }
            return true;
        }
        return false;
    }

    public function test(): bool
    {
        if (!$this->connect()) {
            return false;
        }
        $result = $this->conn->query("SHOW TABLES");
        $this->lastSql = "SHOW TABLES";
        return $result !== false;
    }

    // --- Métodos auxiliares ---

    private function escape($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }
        if (!$this->conn) {
            return "'" . addslashes((string)$value) . "'";
        }
        return "'" . $this->conn->real_escape_string((string)$value) . "'";
    }

    public function createSqlOpen(Model $model): string
    {
        $tab = $model->setting()->Table;
        $key = $model->setting()->Key;
        $rel = $this->createRelations($model);
        $cols = $this->getColumns($tab);

        $sql = "SELECT *";
        if (isset($cols["Estado"])) {
            $sql .= ", `$tab`.`Estado` AS `Estado`";
        }
        $sql .= " FROM `$tab`";
        $sql .= implode(" ", $rel);

        if (!empty($model->$key)) {
            $escapedKey = $this->escape($model->$key);
            $sql .= " WHERE `$key` = $escapedKey";
        } else {
            $sql .= $model->filters();
        }

        return $sql;
    }

    public function createMdlUpdate(Model $model): string
    {
        $table = $model->setting()->Table;
        $key = $model->setting()->Key;
        $data = $model->data(true);
        $fields = $this->getColumns($table);

        $sets = [];
        foreach ($fields as $field => $meta) {
            $value = (empty($data[$field]))? $meta : $data[$field];
            $escaped = $this->escape($value);
            $sets[] = "`$field` = $escaped";
        }

        $id = $this->escape($data[$key]);
        return "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE `$key` = $id";
    }

    public function createMdlInsert(Model $model): string
    {
        $table = $model->setting()->Table;
        $data = $model->data(true);
        $fields = $this->getColumns($table);

        $columns = [];
        $values = [];
        foreach ($fields as $field => $meta) {
            $columns[] = "`$field`";
            $value = (empty($data[$field]))? $meta : $data[$field];
            $values[] = $this->escape($value);
        }

        return "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
    }

    public function createMdlDelete(Model $model): string
    {
        $table = $model->setting()->Table;
        $key = $model->setting()->Key;
        $value = $this->escape($model->$key);
        return "DELETE FROM `$table` WHERE `$key` = $value";
    }

    public function createSqlQuery(Model $model): string
    {
        $tab = $model->setting()->Table;
        $rel = $this->createRelations($model);
        $cols = $this->getColumns($tab);

        $sql = "SELECT ";
        $sql .= ($model->setting()->Columns !== false) ? $model->setting()->Columns : "*";

        if (isset($cols["Estado"])) {
            $sql .= ", `$tab`.`Estado` AS `Estado`";
        }

        $sql .= " FROM `$tab`";
        $sql .= implode(" ", $rel);
        $sql .= $model->filters();

        return $sql;
    }

    public function createRelations(Model $model, int $depth = 0): array
    {
        if ($depth > 5) return [];

        $relation = [];
        $fields = $model->expose();

        foreach ($fields as $f) {
            if (isset($model->$f) && $model->$f instanceof Model) {
                $t1 = $model->setting()->Table;
                $t2 = $model->$f->setting()->Table;
                $k = $model->$f->setting()->Key;
                $rel = "LEFT JOIN `$t2` ON `$t1`.`$f` = `$t2`.`$k`";
                $relation[] = $rel;
                $relation = array_merge($relation, $this->createRelations($model->$f, $depth + 1));
            }
        }

        if ($model->setting()->Relations !== false) {
            $relation[] = $model->setting()->Relations;
        }

        return $relation;
    }

    // --- Métodos de utilidad (no en interfaz) ---

    public function count(Model $model): int
    {
        $sql = "SELECT COUNT(*) AS Total FROM `" . $model->setting()->Table . "`";
        $sql .= $model->filters();
        $r = $this->runSql($sql);
        return $r ? (int)($r[0]['Total'] ?? 0) : 0;
    }

    public function msg(string $mensaje, int $t = 0): void
    {
        $this->lastMsg = $mensaje;
        if ($this->enableMessages){
            if (class_exists('Fred\Modal')) {
                Modal::msg($mensaje, $t);
            } else {
                error_log("Modal::msg no disponible. Mensaje: $mensaje");
            }
        }
    }

    public function getConfig(): string
    {
        return implode(",", [$this->database, $this->server, $this->user, $this->password]);
    }

    // Getters
    public function getLastSql(): string { return $this->lastSql; }
    public function getLastMsg(): string { return $this->lastMsg; }
    public function getAffectedRows(): int { return $this->lastAffectedRows; }
    public function getInsertId(): int { return $this->lastInsertId; }
    public function getRecordCount(): int { return $this->recordCount; }
    public function isConnected(): bool { return $this->conn !== null && $this->conn->ping(); }
    public function getDatabase(): string { return $this->database;}
    public function getDataString(): string { return $this->dataString; }


    //FUNCIONES PARA TRANSACCIONES  //
    /**
     * Ejecuta una operación dentro de una transacción.
     * Si el callback lanza una excepción, se hace rollback automáticamente.
     * Si el callback devuelve false, también se hace rollback.
     * 
     * @param callable $callback Función que recibe la conexión y debe devolver true|false
     * @return bool true si la transacción se completó, false si se hizo rollback
     * @throws Exception si hay error de conexión
     */
    public function transactional(callable $callback): bool
    {
        if (!$this->connect()) {
            throw new \Exception("No se pudo conectar a la base de datos para iniciar la transacción.");
        }

        $this->conn->autocommit(false); // Asegura modo transaccional

        try {
            // Ejecutar el callback pasando la conexión
            $result = $callback($this->conn);

            if ($result === false) {
                $this->conn->rollback();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (\Throwable $e) {
            $this->conn->rollback();
            throw $e; // Re-lanza la excepción para que el llamador la maneje
        } finally {
            $this->conn->autocommit(true); // Restaura autocommit
        }
    }

}