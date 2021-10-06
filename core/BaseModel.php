<?php
namespace Core;

use Core\Database;
use Exception;

abstract class BaseModel
{
    protected $table;
    public $fieldTables = ["*"];
    protected $fail;
    protected $errorMessage;
    public $join = "";
    public $leftJoin = "";
    public $sqlQuery = "";
    public $where = "";
    public $whereRaw = "";
    public $groupBy = "";
    public $limit = "";
    public $orderBy = "";
    public $offset = "";
    public $primaryKey = "id";

    public function __construct()
    {
    }

    public function all(array $data = [])
    {
        $data = self::prepareDataAll($data);
        $fields = implode(',', $this->fieldTables);
        $mysqli = Database::getInstance();
        
        $sql = "SELECT {$fields} FROM {$this->table} {$data[0]}";
        $result = $mysqli->query($sql);

        if (!$result) {
            $this->fail = true;
            return false;
        }

        $dataReturn = [];
        if ($result->num_rows == 0) {
            return $dataReturn;
        }

        while ($rsSql = $result->fetch_assoc()) {
            $dataReturn[] = $rsSql;
        }

        return $dataReturn;
    }

    public function find(int $id)
    {
        $fieldTables = (!is_countable($this->fieldTables) || count($this->fieldTables) == 0) ? "*" : implode(",", $this->fieldTables);

        $sql = "SELECT $fieldTables FROM {$this->table} WHERE id = {$id} LIMIT 1";
        $result = Database::getInstance()->query($sql);

        if (!$result) {
            $this->fail = true;
            $this->errorMessage = $result->error;
            return false;
        }

        if ($result->num_rows == 0) {
            return [];
        }

        return $result->fetch_assoc();
    }

    public function offset(int $offset)
    {
        $this->offset = "OFFSET {$offset}";
        return $this;
    }

    public function fetch(bool $all = false)
    {
        try {
            $result = Database::getInstance()->query($this->get());
            if (!$result) {
                return false;
            }

            if ($result->num_rows == 0) {
                return [];
            }

            if ($all) {
                $fetchAll = [];
                while ($row = $result->fetch_assoc()) {
                    $fetchAll[] = $row;
                }
                return $fetchAll;
            } else {
                return $result->fetch_assoc();
            }
        } catch (Exception $e) {
            $this->fail = true;
            $this->errorMessage = $e;
            return false;
        }
    }

    public function table(string $table = ""): BaseModel
    {
        if ($table != "") {
            $this->table = $table;
        }
        return $this;
    }

    public function join(string $tabelaJoin = "", string $field = "", string $operator = "", string $field1 = ""): BaseModel
    {
        $this->join .= " INNER JOIN {$tabelaJoin} ON {$field} {$operator} {$field1}";
        return $this;
    }

    public function leftJoin(string $tabelaJoin = "", string $campo = "", string $operador = "", string $campo1 = ""): BaseModel
    {
        $this->leftJoin .= " LEFT JOIN {$tabelaJoin} ON {$campo} {$operador} {$campo1}";
        return $this;
    }

    public function select(string $filterFields = ""): BaseModel
    {
        $filterFields = $filterFields == "" ? "*" : $filterFields;
        $this->sqlQuery = "SELECT $filterFields FROM " . $this->table;
        return $this;
    }

    public function where($fieldWhere = "", $operatorWhere = "", $valueWhere = "")
    {
        $this->whereRaw = "";
        $operador = $this->where != "" ? " AND" : "WHERE";
        $this->where .= "{$operador} {$fieldWhere} {$operatorWhere} \"{$valueWhere}\"";
        return $this;
    }
    
    public function whereRaw(string $whereRaw = ""): BaseModel
    {
        $this->where = "";
        $operador = "WHERE";
        $this->whereRaw = "{$operador} {$whereRaw}";
    
        return $this;
    }

    public function groupBy(string $fieldGroupBy = ""): BaseModel
    {
        $this->groupBy = " GROUP BY " . $fieldGroupBy;
        return $this;
    }

    public function limit($limitRows = ""): BaseModel
    {
        $this->limit = "LIMIT {$limitRows}";
        return $this;
    }

    public function orderBy(string $orderBy, string $order = "ASC"): BaseModel
    {
        $this->orderBy = "ORDER BY {$orderBy} {$order}";
        return $this;
    }

    public function get(): string
    {
        if (empty($this->sqlQuery)) {
            $this->select("*");
        }
        $sqlQuery = "{$this->sqlQuery} {$this->join} {$this->leftJoin} {$this->where}
            {$this->whereRaw} {$this->groupBy} {$this->orderBy} {$this->limit} {$this->offset};";

        $this->sqlQuery = "";
        $this->join = "";
        $this->leftJoin = "";
        $this->where = "";
        $this->whereRaw = "";
        $this->groupBy = "";
        $this->orderBy = "";
        $this->limit = "";
        $this->offset = "";

        return $sqlQuery;
    }

    public function numRows()
    {
        try {
            $result = Database::getInstance()->query($this->get());
            if (!$result) {
                return false;
            }

            return $result->num_rows;
        } catch (Exception $e) {
            $this->fail = true;
            $this->errorMessage = $e;
            return false;
        }
    }

    // METHODS INSERTS #########################
    public function insert(array $dataInserts = [])
    {
        //echo "<pre>". print_r($dataInserts)."</pre>";
        $data = self::prepareDataInsert($dataInserts);
        $sql = "INSERT INTO {$this->table} (" . $data['fields'] . ") VALUES " . $data['values'] . ";";
        $this->sqlQuery = substr(trim($sql), 0, -1);
        $result = Database::getInstance()->query("{$this->sqlQuery}");
        
        return $result;
    }
    
    private static function prepareDataInsert(array $data)
    {
        $strKeysBinds = "";
        $strFieldsBinds = "";

        $multipleInserts = (isset($data[0]) && is_array($data[0])) ? true : false;
        $count = ($multipleInserts) ? count($data[0]) : count($data);

        if ($count > 0) {
            foreach ($data as $key => $value) {
                
                if ($multipleInserts) {
                    $insertValues = "";
                    $strFieldsBinds = "";
                    foreach ($data[$key] as $ky => $item) {
                        $item = (is_string($item)) ? "\"$item\"" : $item;
                        $insertValues .= ", $item";
                        $strFieldsBinds .= ", $ky";
                    }
                    $insertValues = ", (" . substr($insertValues, 2) . ")";
                    $strKeysBinds .= $insertValues;
                } else {
                    $value = (is_string($value)) ? "\"$value\"" : $value;
                    $strKeysBinds .= ", $value";
                    $strFieldsBinds .= ", $key";
                }
            }

            $strFieldsBinds = substr($strFieldsBinds, 2);
            $strKeysBinds = $multipleInserts ? substr($strKeysBinds, 2) : "(".substr($strKeysBinds, 2).")";
        }
        
        return ["values" => $strKeysBinds, "fields" => $strFieldsBinds];
    }

    public function update(array $data, $idOuWhereField)
    {
        $data = $this->prepareDataUpdate($data);
        $sqlWhere = "";

        if (is_array($idOuWhereField) && count($idOuWhereField) == 1) {
            
            //$fruit_name = current($idOuWhereField);
            $field = key($idOuWhereField);
            $value = (!is_numeric($idOuWhereField[$field]) && !is_bool($idOuWhereField[$field])) 
                ? "$idOuWhereField[$field]" : $idOuWhereField[$field];
                        
            $sqlQuery = $field . " = " . $value;
        } else {
            $sqlQuery = "{$this->primaryKey} = $idOuWhereField";
        }
        
        $sql = "UPDATE {$this->table} SET {$data[0]}  WHERE $sqlQuery";
        $result = Database::getInstance()->query($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    public function delete($idOuWhereField)
    {
        if (is_array($idOuWhereField) && count($idOuWhereField) == 1) {
            //$fruit_name = current($idOuWhereField);
            $field = key($idOuWhereField);
            $value = (!is_numeric($idOuWhereField[$field]) && !is_bool($idOuWhereField[$field])) 
                ? "$idOuWhereField[$field]" : $idOuWhereField[$field];
                        
            $sqlQuery = $field . " = " . $value;
        } else {
            $sqlQuery = "id = $idOuWhereField";
        }

        $sql = "DELETE FROM {$this->table} WHERE $sqlQuery";
        $result = Database::getInstance()->query($sql);
        if (!$result) {
            return false;
        }

        return true;
    }

    private function prepareDataUpdate(array $data)
    {
        $strKeysBinds = "";
        foreach ($data as $key => $value) {
            $value = (is_string($value)) ? "\"$value\"" : $value;
            $strKeysBinds .= ", {$key} = {$value}";
        }
        $strKeysBinds = substr($strKeysBinds, 1);

        return [$strKeysBinds];
    }
   
    private static function prepareDataAll(array $data)
    {
        $strKeysBinds = "";
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                $whereOrAnd = $strKeysBinds == "" ? " WHERE " : " AND ";
                $value = (is_string($value)) ? "\"$value\"" : $value;
                $strKeysBinds .= "$whereOrAnd {$key} = {$value}";
            }
        }

        return [$strKeysBinds];
    }

    public function begin()
    {
        $mysqli = \Core\Database::getInstance();
        $mysqli->autocommit(false);
    }

    public function commit()
    {
        $mysqli = \Core\Database::getInstance();
        $a = $mysqli->commit();
    }
    
    public function rollback()
    {
        $mysqli = \Core\Database::getInstance();
        $mysqli->rollback();
    }

    public function lastInsertId()
    {
        $con = \Core\Database::getInstance();
        return mysqli_insert_id($con);
    }

    public function __toString()
    {
        return "{$this->get()}";
    }
}
