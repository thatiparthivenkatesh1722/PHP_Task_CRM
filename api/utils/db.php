<?php

require_once(__DIR__ . "/pdo.php");

class DB {
    public $query = '';
    private $pdo = null;
    private $stmt = null;

    function __construct()
    {
        $this->pdo = getPDO();
    }

    public function query($query) {
        $this->query = $query;
    }

    public function prepare() {
        $this->stmt = $this->pdo->prepare($this->query);
    }

    public function execute($params) {
        return $this->stmt->execute($params);
    }

    public function first($params = []) {
        $this->prepare();
        $this->execute($params);
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get($params = []) {
        $this->prepare();
        $this->execute($params);
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($params = []) {
        $this->prepare();
        return $this->execute($params);
    }

    public function update($table, $fields = [], $where = []) {
        $setParts = [];
        $params   = [];
        foreach ($fields as $col => $val) {
            $setParts[] = "`$col` = :set_$col";
            $params["set_$col"] = $val;
        }
        $whereParts = [];
        foreach ($where as $col => $val) {
            $whereParts[] = "`$col` = :wh_$col";
            $params["wh_$col"] = $val;
        }
        $sql = "UPDATE `$table` SET " . implode(', ', $setParts)
             . " WHERE " . implode(' AND ', $whereParts);
        $this->stmt = $this->pdo->prepare($sql);
        return $this->stmt->execute($params);
    }

    public function delete($where = [], $table = '') {
        if ($table !== '') {
            $whereParts = [];
            $params     = [];
            foreach ($where as $col => $val) {
                $whereParts[] = "`$col` = :$col";
                $params[$col] = $val;
            }
            $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $whereParts);
            $this->stmt = $this->pdo->prepare($sql);
            return $this->stmt->execute($params);
        }
        $this->prepare();
        return $this->execute($where);
    }
}
