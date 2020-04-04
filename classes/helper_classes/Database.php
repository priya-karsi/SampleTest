<?php

class Database {

    protected $di;
    protected $stmt;
    protected $pdo;

    protected $debug;
    protected $host;
    protected $db;
    protected $username;
    protected $password;
    protected $config;


    public function __construct(DependancyInjector $di)
    {
        $this->di = $di;
        $this->config = $this->di->get('config');
        $this->username = $this->config->get('username');
        $this->password = $this->config->get('password');
        $this->debug = $this->config->get('debug');
        $this->host = $this->config->get('host');
        $this->db = $this->config->get('db');
        $this->connectDB();

    }

    public function connectDB() {

        try{
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db}", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            if($this->debug) {
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        }
        catch(PDOException $e) {
            die( $this->debug ? $e->getMessage() : "Error While Connecting!");
        }
    }

    public function raw($sql, $mode=PDO::FETCH_OBJ) {
        return $this->query($sql)->fetchAll($mode);
    }

    public function query($sql) {
        return $this->pdo->query($sql); // exeqcutes the ddl statement but not dml use execute for dml
    }

    /**
     * Function inserts $data in $table and returns the insert id of the record.
     * the insert id is actually returned so that it can be used for the linking of tables
     */

    public function insert(string $table, $data) { //$data is mixed array
        $keys = array_keys($data);
        $fields = "`" . implode("`, `", $keys) . "`";
        $placeholder = ":" . implode(", :", $keys);
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholder})";
        // die($data['name']);
        $this->stmt = $this->pdo->prepare($sql);
        try
        {
            $this->stmt->execute($data);
            return $this->pdo->lastInsertId();
            // die($this->stmt->debugDumpParams());
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }

    public function update(string $table, $data, string $condition="1") { //$data is mixed array

        $columnKeyValue = "";

        $i = 0;
        foreach($data as $key=>$value) {
            $columnKeyValue .= "{$key} = :{$key}";
            $i++;
            if($i < count($data)) {
                $columnKeyValue .= ", ";
            }
        }
        $sql = "UPDATE {$table} SET {$columnKeyValue} WHERE {$condition}";
        $this->stmt = $this->pdo->prepare($sql);
        try
        {
            $this->stmt->execute($data);
            return  $this;
            // die($this->stmt->debugDumpParams());
        }catch(PDOException $e){
            die($e->getMessage());
        }
    }

    public function lastInsertId() {
        return$this->pdo->lastInsertId();
    }

    public function readData(string $table, $fields = [], $condition = '1', $readMode = PDO::FETCH_OBJ)
    {
        if(count($fields) == 0):
            $columnNameString = "*";
        else:
            $columnNameString = implode(", ", $fields);
        endif;

        $sql = "SELECT {$columnNameString} FROM {$table} WHERE {$condition}";
        // die($sql);
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute();
        return $this->stmt->fetchAll($readMode);
    }

    public function delete(string $table, string $condition) {
        $sql = "UPDATE {$table} SET deleted = 1 WHERE {$condition}";
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute();
    }

    public function exists($table, $data) { //$data is mixed array
        $field = array_keys($data)[0];
        $result =  $this->readData($table, [], "{$field} = '{$data[$field]}'", PDO::FETCH_ASSOC);
        if(count($result) > 0) {
            return true;
        }
        return false;
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollback();
    }

}



?>
